<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Authorization;

use MakinaCorpus\AccessControl\AccessConfigurationError;
use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\AccessRole;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Policy\NullPolicyLoader;
use MakinaCorpus\AccessControl\Subject\NullSubjectLocator;
use MakinaCorpus\AccessControl\Subject\SubjectLocator;
use PHPUnit\Framework\TestCase;

final class DefaultAuthorizationTest extends TestCase
{
    public function testReturnFalseWhenPolicysButNoSubject(): void
    {
        $auth = new DefaultAuthorization(
            new class () extends NullPolicyLoader
            {
                public function loadFromClass(string $className): iterable
                {
                    return [
                        new AccessRole("admin"),
                    ];
                }
            },
            new NullSubjectLocator(),
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
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
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
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
                public function findSubject()
                {
                    return 'something';
                }
            },
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
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
                        },
                    ];
                }
            },
            new class () implements SubjectLocator
            {
                public function findSubject()
                {
                    return 'something';
                }
            },
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
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
                public function findSubject()
                {
                    return 'something';
                }
            },
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            true, // This is what we test here.
            true
        );

        self::assertFalse($auth->isGranted(new \stdClass()));
    }

    public function testHandleAccessRole(): void
    {
        self::markTestIncomplete();
    }

    public function testHandleAccessPermission(): void
    {
        self::markTestIncomplete();
    }

    public function testHandleAccessService(): void
    {
        self::markTestSkipped();
    }

    public function testHandleAccessMethod(): void
    {
        self::markTestSkipped();
    }
}
