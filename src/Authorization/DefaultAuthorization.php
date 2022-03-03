<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Authorization;

use MakinaCorpus\AccessControl\AccessAllOrNothing;
use MakinaCorpus\AccessControl\AccessAllow;
use MakinaCorpus\AccessControl\AccessDelegate;
use MakinaCorpus\AccessControl\AccessDeny;
use MakinaCorpus\AccessControl\AccessMethod;
use MakinaCorpus\AccessControl\AccessPermission;
use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\AccessResource;
use MakinaCorpus\AccessControl\AccessRole;
use MakinaCorpus\AccessControl\AccessService;
use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\AuthorizationContext;
use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\Error\AccessError;
use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\ExpressionArgumentChoices;
use MakinaCorpus\AccessControl\Expression\Method\MethodExecutor;
use MakinaCorpus\AccessControl\Expression\Method\MethodExpression;
use MakinaCorpus\AccessControl\Expression\Method\MethodExpressionParser;
use MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;
use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;
use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;
use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use MakinaCorpus\Profiling\Profiler;
use MakinaCorpus\Profiling\Implementation\ProfilerContextAware;
use MakinaCorpus\Profiling\Implementation\ProfilerContextAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

final class DefaultAuthorization implements Authorization, AuthorizationContext, LoggerAwareInterface, ProfilerContextAware
{
    use LoggerAwareTrait;
    use ProfilerContextAwareTrait;

    private PolicyLoader $policyLoader;
    private SubjectLocator $subjectLocator;
    private ?ResourceLocator $resourceLocator = null;
    private ?ServiceLocator $serviceLocator = null;
    private ?PermissionChecker $permissionChecker = null;
    private ?RoleChecker $roleChecker = null;
    private bool $denyIfNoPolicies = false;
    private bool $debug = true;
    private ?string $runId = null;

    public function __construct(
        PolicyLoader $policyLoader,
        SubjectLocator $subjectLocator,
        ?ResourceLocator $resourceLocator = null,
        ?ServiceLocator $serviceLocator = null,
        ?PermissionChecker $permissionChecker = null,
        ?RoleChecker $roleChecker = null,
        bool $denyIfNoPolicies = false,
        bool $debug = true
    ) {
        $this->debug = $debug;
        $this->denyIfNoPolicies = $denyIfNoPolicies;
        $this->logger = new NullLogger();
        $this->permissionChecker = $permissionChecker;
        $this->policyLoader = $policyLoader;
        $this->resourceLocator = $resourceLocator;
        $this->roleChecker = $roleChecker;
        $this->serviceLocator = $serviceLocator;
        $this->subjectLocator = $subjectLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentSubject(string $className)
    {
        foreach ($this->subjectLocator->findSubject() as $subject) {
            if (\is_object($subject) && $subject instanceof $className) {
                return $subject;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($resource, array $context = []): bool
    {
        if (!\is_object($resource)) {
            throw new AccessRuntimeError("Only class are implemented for now.");
        }

        $profiler = $this->startProfiler();
        try {
            $profiler->start('isGranted');
            $this->runId = $profiler->getId();

            $this->info("Received isGranted({resource})", ['resource' => \get_class($resource)]);

            $policies = $this->policyLoader->loadFromClass(\get_class($resource));

            return $this->decideOnPolicies($policies, $resource, $context, $profiler);
        } finally {
            $profiler->stop();
            $this->debug("isGranted() TIME {time} msec", ['time' => $profiler->getElapsedTime()]);
            $this->runId = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isMethodGranted($resource, string $methodName, array $context = []): bool
    {
        $className = null;
        if (\is_object($resource)) {
            $className = \get_class($resource);
        } else if (\is_string($resource) && \class_exists($className)) {
            $className = $resource;
        } else {
            throw new AccessRuntimeError("Given object is neither an instance or a class name.");
        }

        $profiler = $this->startProfiler();
        try {
            $profiler->start('isMethodGranted');
            $this->runId = $profiler->getId();

            $this->info("Received isMethodGranted({resource}::{method})", ['resource' => \get_class($resource), 'method' => $methodName]);

            $policies = $this->policyLoader->loadFromClassMethod($className, $methodName);

            return $this->decideOnPolicies($policies, $resource, $context, $profiler);
        } finally {
            $profiler->stop();
            $this->debug("isMethodGranted() TIME {time} msec", ['time' => $profiler->getElapsedTime()]);
            $this->runId = null;
        }
    }

    private function decideOnPolicies(iterable $policies, $resource, array $context, Profiler $profiler): bool
    {
        // Need this to make it rewindable since we need to locate the resource
        // first, before applying all other policices.
        $policies = \is_array($policies) ? $policies : \iterator_to_array($policies);

        if (!$policies) {
            return $this->returnIfNoPolicyFound();
        }

        $subjects = $this->subjectLocator->findSubject();
        $subjects = \is_array($subjects) ? $subjects : \iterator_to_array($subjects);

        $total = 0; $denied = 0; $allowed = 0;
        $allOrNothing = false; $resourceFound = false;

        // Resource location.
        foreach ($policies as $policy) {
            if (!$resourceFound && $policy instanceof AccessResource) {
                $this->debug("{resource}.", ['resource' => $policy->toString()]);

                if ($resource = $this->locateResource($policy, $subjects, $resource, $context, $profiler)) {
                    $resourceFound = true;
                }
            } else if ($policy instanceof AccessAllOrNothing) {
                $this->debug("Policy is ALL OR NOTHING.");

                $allOrNothing = true;
            }
        }

        if (null === $resource) {
            $this->warning("No resource was found by resource loaders, DENY.");

            // No resource were found by locators or invalid input was given.
            return false;
        }

        // Now, we apply policies.
        foreach ($policies as $policy) {
            ++$total;

            if (!$policy instanceof AccessResource && !$policy instanceof AccessAllOrNothing) {
                if ($this->handlePolicy($policy, $subjects, $resource, $context, $profiler)) {
                    // Short-circuit at first match when not all or nothing.
                    if (!$allOrNothing) {
                        $this->debug("ALLOW by {policy}, short-circuiting (reason: allowed)", ['policy' => $policy->toString()]);

                        return true;
                    } else {
                        $this->debug("ALLOW by {policy}", ['policy' => $policy->toString()]);

                        ++$allowed;
                    }
                } else if ($allOrNothing) {
                    $this->debug("DENY by {policy}, short-circuiting (reason: all or nothing)", ['policy' => $policy->toString()]);

                    return false;
                } else {
                    $this->debug("DENY by {policy}", ['policy' => $policy->toString()]);

                    ++$denied;
                }
            }
        }

        if ($total === 0) {
            return $this->returnIfNoPolicyFound();
        }

        return $allOrNothing ? 0 === $denied : 0 < $allowed;
    }

    private function returnIfNoPolicyFound(): bool
    {
        if ($this->denyIfNoPolicies) {
            $this->warning("No policy found, DENY by configuration.");

            return false;
        } else {
            $this->warning("No policy found, ALLOW by configuration.");

            return true;
        }
    }

    private function locateResource(AccessResource $policy, array $subjects, $resource, array $context, Profiler $profiler)
    {
        try {
            $profiler->start('locateResource');

            if (!$this->resourceLocator) {
                if ($this->debug) {
                    throw new AccessConfigurationError(\sprintf("No %s is registered, cannot process %s", ResourceLocator::class, AccessResource::class));
                }
                return null;
            }

            $resourceId = $policy->findResourceId($resource);

            if (null === $resourceId) {
                throw new AccessConfigurationError(\sprintf(
                    "Could not find the resource identifier on %s::%s",
                    \get_class($resource), $policy->getResourceType()
                ));
            }

            $found = $resource = $this->resourceLocator->loadResource(
                $policy->getResourceType(),
                $resourceId
            );

            if ($this->debug && (null === $found || false === $found)) {
                throw new AccessRuntimeError(\sprintf(
                    "No resource locator was able to find the %s typed object with identifier %s",
                    $policy->getResourceType(),
                    $resourceId
                ));
            }

            return $found;
        } finally {
            $this->debug("locateResource() TIME {time} msec", ['time' => $profiler->stop('locateResource')]);
        }
    }

    private function handlePolicy(AccessPolicy $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        try {
            $profiler->start('handlePolicy');

            if ($policy instanceof AccessDeny) {
                return $this->handlePolicyAccessDeny($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessAllow) {
                return $this->handlePolicyAccessAllow($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessRole) {
                return $this->handlePolicyAccessRole($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessPermission) {
                return $this->handlePolicyAccessPermission($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessMethod) {
                return $this->handlePolicyAccessMethod($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessService) {
                return $this->handlePolicyAccessService($policy, $subjects, $resource, $context, $profiler);
            }
            if ($policy instanceof AccessDelegate) {
                return $this->handlePolicyAccessDelegate($policy, $subjects, $resource, $context, $profiler);
            }

            throw new AccessConfigurationError(\sprintf("Unhandled policy: %s", \get_class($policy)));

        } catch (AccessError $e) {
            $this->error("handlePolicy() ERROR while executing policy {policy}", ['policy' => $policy->toString(), 'exception' => $e]);

            if ($this->debug) {
                throw $e;
            }

            return false;

        } finally {
            $this->debug("handlePolicy() TIME {time} msec", ['time' => $profiler->stop('handlePolicy')]);
        }
    }

    private function handlePolicyAccessDelegate(AccessDelegate $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        $className = $policy->getClassName();

        if (!\class_exists($className) && !\interface_exists($className)) {
            if ($this->debug) {
                throw new AccessConfigurationError(\sprintf("Class or interface %s does not exist, cannot process %s", $className, AccessRole::class));
            }
            return false;
        }

        // @todo circular dependency break.
        $policies = $this->policyLoader->loadFromClass($className);

        return $this->decideOnPolicies($policies, $resource, $context, $profiler);
    }

    private function handlePolicyAccessDeny(AccessDeny $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        return false;
    }

    private function handlePolicyAccessAllow(AccessAllow $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        return true;
    }

    private function handlePolicyAccessRole(AccessRole $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        if (!$this->roleChecker) {
            if ($this->debug) {
                throw new AccessConfigurationError(\sprintf("No %s is registered, cannot process %s", RoleChecker::class, AccessRole::class));
            }
            return false;
        }

        foreach ($subjects as $subject) {
            if ($this->roleChecker->subjectHasRole($subject, $policy->getRole())) {
                return true;
            }
        }

        return false;
    }

    private function handlePolicyAccessPermission(AccessPermission $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        if (!$this->permissionChecker) {
            if ($this->debug) {
                throw new AccessConfigurationError("No %s is registered, cannot process %s", PermissionChecker::class, AccessRole::class);
            }
            return false;
        }

        foreach ($subjects as $subject) {
            if ($this->roleChecker->subjectHasRole($subject, $policy->getRole())) {
                return true;
            }
        }

        return false;
    }

    private function handlePolicyAccessMethod(AccessMethod $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        if (!\is_object($resource)) {
            throw new AccessRuntimeError(\sprintf("Cannot apply an %s policy on a non-object", AccessMethod::class));
        }

        $method = (new MethodExpressionParser())->parse($policy->getMethod());
        \assert($method instanceof MethodExpression);

        if ($method->getServiceName()) {
            throw new AccessConfigurationError(\sprintf("Cannot apply a service method call when using an %s policy.", AccessMethod::class));
        }

        return (new MethodExecutor())->callResourceMethod(
            $resource,
            $method->getServiceMethodName(),
            $method->mapArgumentsFromContext(['subject' => new ExpressionArgumentChoices($subjects), 'resource' => $resource] + $context)
        );
    }

    private function handlePolicyAccessService(AccessService $policy, array $subjects, $resource, array $context, Profiler $profiler): bool
    {
        if (!$this->serviceLocator) {
            if ($this->debug) {
                throw new AccessConfigurationError(\sprintf("No %s is registered, cannot process %s", ServiceLocator::class, AccessService::class));
            }
            return false;
        }

        $method = (new MethodExpressionParser())->parse($policy->getExpression());
        \assert($method instanceof MethodExpression);

        $serviceCallback = $this->serviceLocator->findServiceMethod($method->getServiceMethodName(), $method->getServiceName());

        if (!$serviceCallback) {
            $this->error("handlePolicyAccessService() UNABLE TO FIND SERVICE for policy {policy}", ['policy' => $policy->toString()]);

            if ($this->debug) {
                throw new AccessConfigurationError(\sprintf(
                    "No service locator was able to find the service %s",
                    $method->toString()
                ));
            }
            return false;
        }

        return (new MethodExecutor())->callCallback(
            $serviceCallback,
            $method->mapArgumentsFromContext(['subject' => new ExpressionArgumentChoices($subjects), 'resource' => $resource] + $context)
        );
    }

    private function debug(string $message, array $context = []): void
    {
        $this->logger->debug("[access-control] [{id}] " . $message, ['id' => $this->runId] + $context);
    }

    private function info(string $message, array $context = []): void
    {
        $this->logger->info("[access-control] [{id}] " . $message, ['id' => $this->runId] + $context);
    }

    private function warning(string $message, array $context = []): void
    {
        $this->logger->warning("[access-control] [{id}] " . $message, ['id' => $this->runId] + $context);
    }

    private function error(string $message, array $context = []): void
    {
        $this->logger->error("[access-control] [{id}] " . $message, ['id' => $this->runId] + $context);
    }
}
