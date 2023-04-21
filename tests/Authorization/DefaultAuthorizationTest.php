<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Authorization;

use MakinaCorpus\AccessControl\AccessAllow;
use MakinaCorpus\AccessControl\AccessDeny;
use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\PolicyLoader\AttributePolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\NullPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;
use MakinaCorpus\AccessControl\ResourceLocator\ChainResourceLocator;
use MakinaCorpus\AccessControl\ServiceLocator\NullServiceLocator;
use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use MakinaCorpus\AccessControl\SubjectLocator\NullSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use MakinaCorpus\AccessControl\Tests\Mock\FixedRoleChecker;
use MakinaCorpus\AccessControl\Tests\Mock\FixedSubjectLocator;
use MakinaCorpus\AccessControl\Tests\Mock\MethodArgumentInContext;
use MakinaCorpus\AccessControl\Tests\Mock\WithDelegateResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithInvalidDelegateResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithInvalidResourceResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithMethodArgumentInContextResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithMethodResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithResourceResource;
use MakinaCorpus\AccessControl\Tests\Mock\WithResourceResourceLocator;
use MakinaCorpus\AccessControl\Tests\Mock\WithServiceResource;
use PHPUnit\Framework\TestCase;

final class DefaultAuthorizationTest extends TestCase
{
    public function testAccessAllowAlwaysAllow(): void
    {
        $auth = new DefaultAuthorization(
            new class () extends NullPolicyLoader
            {
                public function loadFromClass(string $className): iterable
                {
                    return [
                        new AccessAllow(),
                    ];
                }
            },
            new NullSubjectLocator(),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true
        );

        self::assertTrue($auth->isGranted(new \stdClass()));
    }

    public function testAccessDenyAlwaysDeny(): void
    {
        $auth = new DefaultAuthorization(
            new class () extends NullPolicyLoader
            {
                public function loadFromClass(string $className): iterable
                {
                    return [
                        new AccessDeny(),
                    ];
                }
            },
            new NullSubjectLocator(),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true
        );

        self::assertFalse($auth->isGranted(new \stdClass()));
    }

    public function testReturnTrueWhenNoPolicysAndNoSubject(): void
    {
        $auth = new DefaultAuthorization(
            new NullPolicyLoader(),
            new NullSubjectLocator(),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true
        );

        self::assertTrue($auth->isGranted(new \stdClass()));
    }

    public function testReturnTrueIfNoPolicys(): void
    {
        $auth = new DefaultAuthorization(
            new NullPolicyLoader(),
            new class () implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    yield 'something';
                }
            },
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false, // This is what we test here.
            true
        );

        self::assertTrue($auth->isGranted(new \stdClass()));
    }

    public function testRaiseErrorForUnhandledPolicys(): void
    {
        $auth = new DefaultAuthorization(
            new class () extends NullPolicyLoader
            {
                public function loadFromClass(string $className): iterable
                {
                    return [
                        new class () implements AccessPolicy
                        {
                            public function toString(): string { return 'unhandled'; }
                        },
                    ];
                }
            },
            new class () implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    yield 'something';
                }
            },
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true
        );

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Unhandled policy/');
        $auth->isGranted(new \stdClass());
    }

    public function testReturnFalseIfNoPolicysAndDefaultIsDeny(): void
    {
        $auth = new DefaultAuthorization(
            new NullPolicyLoader(),
            new class () implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    yield 'something';
                }
            },
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            true, // This is what we test here.
            true
        );

        self::assertFalse($auth->isGranted(new \stdClass()));
    }

    public function testServiceLocator(): void
    {
        $subject = new \stdClass();
        $resource = new WithServiceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            new class () implements ServiceLocator
            {
                public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
                {
                    if ("This" === $serviceName && "That" === $methodName) {
                        return function (\stdClass $subject, WithServiceResource $resource) {
                            if (!$resource instanceof WithServiceResource) {
                                return false;
                            }
                            return true;
                        };
                    }
                    return null;
                }
            }, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::assertTrue($auth->isGranted($resource));
    }

    public function testServiceLocatorRaiseErrorIfNotFoundWhenDebug(): void
    {
        $subject = new \stdClass();
        $resource = new WithServiceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            new NullServiceLocator(), // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/able to find the service This.That\(resource, subject\)/');
        $auth->isGranted($resource);
    }

    public function testServiceLocatorReturnFalseIfNotFound(): void
    {
        $subject = new \stdClass();
        $resource = new WithServiceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            new NullServiceLocator(), // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // WITHOUT DEBUG
        );

        self::assertFalse($auth->isGranted($resource));
    }

    public function testServiceLocatorRaiseErrorIfNotRegisteredWhenDebug(): void
    {
        $subject = new \stdClass();
        $resource = new WithServiceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/ServiceLocator is registered, cannot/');
        $auth->isGranted($resource);
    }

    public function testServiceLocatorReturnFalseIfNotRegistered(): void
    {
        $subject = new \stdClass();
        $resource = new WithServiceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // WITHOUT DEBUG
        );

        self::assertFalse($auth->isGranted($resource));
    }

    public function testMethodCanBeCalledOnContextArgument(): void
    {
        $subject = new \stdClass();
        $resource = new WithMethodArgumentInContextResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // WITHOUT DEBUG
        );

        // Valid value in context.
        self::assertTrue($auth->isGranted($resource, [
            'bar' => 13,
            'foo' => new MethodArgumentInContext(),
        ]));

        // Invalid value in context.
        self::assertFalse($auth->isGranted($resource, [
            'bar' => 12,
            'foo' => new MethodArgumentInContext(),
        ]));

        // Invalid argument in context.
        self::assertFalse($auth->isGranted($resource, [
            'bar' => 13,
            'foo' => "Non object, method is not callable",
        ]));

        // Missing argument in context.
        self::assertFalse($auth->isGranted($resource, [
            'bar' => 13,
        ]));
    }

    public function testMethod(): void
    {
        $subject = new \stdClass();
        $resource = new WithMethodResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // WITHOUT DEBUG
        );

        // Valid value in context.
        self::assertTrue($auth->isGranted($resource, [
            'bar' => 13,
        ]));

        // Invalid value in context.
        self::assertFalse($auth->isGranted($resource, [
            'bar' => 12,
        ]));
    }

    public function testAccessDelegate(): void
    {
        $subject = new \stdClass();
        $resource = new WithDelegateResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true),
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::assertTrue($auth->isGranted($resource));
    }

    public function testAccessDelegateRaiseErrorIfNotExistsWhenDebug(): void
    {
        $subject = new \stdClass();
        $resource = new WithInvalidDelegateResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true),
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Class or interface this_class_does_not_exists does not exist/');
        $auth->isGranted($resource);
    }

    public function testAccessDelegateReturnFalseIfNotExists(): void
    {
        $subject = new \stdClass();
        $resource = new WithInvalidDelegateResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true),
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // WITHOUT DEBUG
        );

        self::assertFalse($auth->isGranted($resource));
    }

    public function testAccessMethod(): void
    {
        self::markTestIncomplete();
    }

    public function testAccessMethodRaiseErrorIfMethodNotFoundWhenDebug(): void
    {
        self::markTestIncomplete();
    }

    public function testAccessMethodReturnFalseIfMethodNotFound(): void
    {
        self::markTestIncomplete();
    }

    public function testResourceLocator(): void
    {
        $subject = new \stdClass();
        $resource = new WithResourceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            new ChainResourceLocator([new WithResourceResourceLocator()]), // This is what we test here.
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true), // Always allow input resource.
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::assertTrue($auth->isGranted($resource));
    }

    public function testResourceLocatorRaiseErrorIfUnfoundWhenDebug(): void
    {
        $subject = new \stdClass();
        $resource = new WithInvalidResourceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            new WithResourceResourceLocator(), // This is what we test here.
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true), // Always allow input resource.
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches('/No resource locator was able to find/');
        $auth->isGranted($resource);
    }

    public function testResourceLocatorReturnFalseIfUnfound(): void
    {
        $subject = new \stdClass();
        $resource = new WithInvalidResourceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            new WithResourceResourceLocator(), // This is what we test here.
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true), // Always allow input resource.
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // NO DEBUG
        );

        self::assertFalse($auth->isGranted($resource));
    }

    public function testResourceLocatorRaiseErrorIfNoLocatorRegisteredWhenDebug(): void
    {
        $subject = new \stdClass();
        $resource = new WithResourceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null, // This is what we test here.
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true), // Always allow input resource.
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            true // WITH DEBUG
        );

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/ResourceLocator is registered, cannot/');
        $auth->isGranted($resource);
    }

    public function testResourceLocatorReturnFalseIfNoLocatorRegistered(): void
    {
        $subject = new \stdClass();
        $resource = new WithResourceResource();

        $auth = new DefaultAuthorization(
            $this->createPolicyLoader(), // Required.
            new FixedSubjectLocator($subject),
            /* ?ResourceLocator $resourceLocator */ null, // This is what we test here.
            /* ?ServiceLocator $serviceLocator */ null,
            /* ?PermissionChecker $permissionChecker */ null,
            new FixedRoleChecker(true), // Always allow input resource.
            /* ?MethodExecutor $methodExecutor */ null,
            false,
            false // NO DEBUG
        );

        self::assertFalse($auth->isGranted($resource));
    }

    public function testResourceLocatorRaiseErrorIfIdIsNullWhenDebug(): void
    {
        self::markTestIncomplete();
    }

    public function testResourceLocatorReturnFalseIfIdIsNull(): void
    {
        self::markTestIncomplete();
    }

    public function testHandleAccessRole(): void
    {
        self::markTestIncomplete();
    }

    public function testHandleAccessPermission(): void
    {
        self::markTestIncomplete();
    }

    protected function createPolicyLoader(): PolicyLoader
    {
        return new AttributePolicyLoader();
    }
}
