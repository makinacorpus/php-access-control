<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Role;

use MakinaCorpus\AccessControl\Role\ChainRoleChecker;
use MakinaCorpus\AccessControl\Role\RoleChecker;
use PHPUnit\Framework\TestCase;

final class ChainRoleCheckerTest extends TestCase
{
    public function testAllAreCalled(): void
    {
        $chain = new ChainRoleChecker([
            new class() implements RoleChecker
            {
                public function subjectHasRole($subject, string $role): bool
                {
                    return false;
                }
            },
            new class() implements RoleChecker
            {
                public function subjectHasRole($subject, string $role): bool
                {
                    return 'bar' === $role;
                }
            },
        ]);

        self::assertTrue($chain->subjectHasRole('foo', 'bar'));
        self::assertFalse($chain->subjectHasRole('fizz', 'buzz'));
    }
}
