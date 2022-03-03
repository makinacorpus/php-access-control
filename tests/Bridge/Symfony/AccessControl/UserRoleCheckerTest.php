<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserRoleChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;

final class UserRoleCheckerTest extends TestCase
{
    public function testIt(): void
    {
        $instance = new UserRoleChecker();

        if (\class_exists(InMemoryUser::class)) {
            $subject = new InMemoryUser('foo', null, ['ROLE_TESTING']);
        } else {
            $subject = new User('foo', null, ['ROLE_TESTING']);
        }

        self::assertTrue($instance->subjectHasRole($subject, 'ROLE_TESTING'));
        self::assertFalse($instance->subjectHasRole($subject, 'ROLE_NON_EXISTING'));
        // Ensure it doesn't crash with unexpected input.
        self::assertFalse($instance->subjectHasRole(new \stdClass(), 'ROLE_TESTING'));
    }
}
