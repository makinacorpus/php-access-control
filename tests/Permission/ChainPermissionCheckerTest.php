<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Permission;

use MakinaCorpus\AccessControl\Permission\ChainPermissionChecker;
use MakinaCorpus\AccessControl\Permission\PermissionChecker;
use PHPUnit\Framework\TestCase;

final class ChainPermissionCheckerTest extends TestCase
{
    public function testAllAreCalled(): void
    {
        $chain = new ChainPermissionChecker([
            new class() implements PermissionChecker
            {
                public function subjectHasPermission($subject, string $permission): bool
                {
                    return false;
                }
            },
            new class() implements PermissionChecker
            {
                public function subjectHasPermission($subject, string $permission): bool
                {
                    return 'bar' === $permission;
                }
            },
        ]);

        self::assertTrue($chain->subjectHasPermission('foo', 'bar'));
        self::assertFalse($chain->subjectHasPermission('fizz', 'buzz'));
    }
}
