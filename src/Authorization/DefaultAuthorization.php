<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Authorization;

use MakinaCorpus\AccessControl\AccessAllOrNothing;
use MakinaCorpus\AccessControl\AccessConfigurationError;
use MakinaCorpus\AccessControl\AccessMethod;
use MakinaCorpus\AccessControl\AccessPermission;
use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\AccessRole;
use MakinaCorpus\AccessControl\AccessService;
use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\Expression\ParameterAggregator;
use MakinaCorpus\AccessControl\Expression\Method\MethodExecutor;
use MakinaCorpus\AccessControl\Permission\PermissionChecker;
use MakinaCorpus\AccessControl\Policy\PolicyLoader;
use MakinaCorpus\AccessControl\Role\RoleChecker;
use MakinaCorpus\AccessControl\Subject\SubjectLocator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use MakinaCorpus\AccessControl\Expression\MethodExpressionParser;

final class DefaultAuthorization implements Authorization, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private PolicyLoader $policyLoader;
    private SubjectLocator $subjectLocator;
    private ?PermissionChecker $permissionChecker;
    private ?RoleChecker $roleChecker;
    private bool $denyIfNoPolicies = false;
    private bool $debug = true;

    public function __construct(
        PolicyLoader $policyLoader,
        SubjectLocator $subjectLocator,
        ?PermissionChecker $permissionChecker = null,
        ?RoleChecker $roleChecker = null,
        bool $denyIfNoPolicies = false,
        bool $debug = true
    ) {
        $this->debug = $debug;
        $this->denyIfNoPolicies = $denyIfNoPolicies;
        $this->permissionChecker = $permissionChecker;
        $this->policyLoader = $policyLoader;
        $this->roleChecker = $roleChecker;
        $this->subjectLocator = $subjectLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($resource, ...$parameters): bool
    {
        if (!\is_object($resource)) {
            throw new \Exception("Only class are implemented for now.");
        }

        $policies = $this->policyLoader->loadFromClass(\get_class($resource));

        return $this->decideOnPolicies($policies, $resource, ...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function isMethodGranted($resource, string $methodName, ...$parameters): bool
    {
        $className = null;
        if (\is_object($resource)) {
            $className = \get_class($resource);
        } else if (\is_string($resource) && \class_exists($className)) {
            $className = $resource;
        } else {
            throw new AccessConfigurationError("Given object is neither an instance or a class name.");
        }

        $policies = $this->policyLoader->loadFromClassMethod($className, $methodName);

        return $this->decideOnPolicies($policies, $resource, ...$parameters);
    }

    private function decideOnPolicies(iterable $policies, $resource, ...$parameters): bool
    {
        $subject = $this->subjectLocator->findSubject();

        $total = 0; $denied = 0; $allowed = 0;
        $allOrNothing = false;

        foreach ($policies as $policy) {
            if (!$subject) {
                return false;
            }

            ++$total;

            if ($policy instanceof AccessAllOrNothing) {
                $allOrNothing = true;
            } else {
                if ($this->handlePolicy($policy, $subject, $resource, ...$parameters)) {
                    ++$allowed;
                } else {
                    ++$denied;
                }
            }
        }

        if ($total === 0) {
            return !$this->denyIfNoPolicies;
        }

        return $allOrNothing ? 0 === $denied : 0 < $allowed;
    }

    private function handlePolicy(AccessPolicy $policy, $subject, $resource, ...$parameters): bool
    {
        if ($policy instanceof AccessRole) {
            return $this->handlePolicyAccessRole($policy, $subject, $resource, ...$parameters);
        }
        if ($policy instanceof AccessPermission) {
            return $this->handlePolicyAccessPermission($policy, $subject, $resource, ...$parameters);
        }
        if ($policy instanceof AccessMethod) {
            return $this->handlePolicyAccessMethod($policy, $subject, $resource, ...$parameters);
        }
        if ($policy instanceof AccessService) {
            return $this->handlePolicyAccessService($policy, $subject, $resource, ...$parameters);
        }
        if ($this->debug) {
            throw new AccessConfigurationError(\sprintf("Unhandled policy: %s", \get_class($policy)));
        }
        return false;
    }

    private function handlePolicyAccessRole(AccessRole $policy, $subject, $resource, ...$parameters): bool
    {
        if (!$this->roleChecker) {
            if ($this->debug) {
                throw new \Exception("No %s is registered, cannot process %s", RoleChecker::class, AccessRole::class);
            }
            return false;
        }
        return $this->roleChecker->subjectHasRole($subject, $policy->getRole());
    }

    private function handlePolicyAccessPermission(AccessPermission $policy, $subject, $resource, ...$parameters): bool
    {
        if (!$this->permissionChecker) {
            if ($this->debug) {
                throw new \Exception("No %s is registered, cannot process %s", PermissionChecker::class, AccessRole::class);
            }
            return false;
        }
        return $this->roleChecker->subjectHasRole($subject, $policy->getRole());
    }

    private function handlePolicyAccessMethod(AccessMethod $policy, $subject, $resource, ...$parameters): bool
    {
        if (!\is_object($resource)) {
            throw new AccessConfigurationError(\sprintf("Cannot apply an %s policy on a non-object", AccessMethod::class));
        }

        // Parse method string and extract name and parameter names.
        // @todo This will not work.
        $method = (new MethodExpressionParser())->parse($policy->getMethod());

        if ($method->serviceName) {
            throw new AccessConfigurationError(\sprintf("Cannot apply a service method call when using an %s policy.", AccessMethod::class));
        }

        // Compute parameter values that will be passed to method call.
        $computedArgs = (new ParameterAggregator())->aggregateParameters($method->parameters, $subject, $resource, $parameters);

        return (new MethodExecutor())->callResourceMethod($resource, $method->methodName, $computedArgs);
    }

    private function handlePolicyAccessService(AccessService $policy, $subject, $resource, ...$parameters): bool
    {
        throw new \Exception("Implement me.");
    }
}
