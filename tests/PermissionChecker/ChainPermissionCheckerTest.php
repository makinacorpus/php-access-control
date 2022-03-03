<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PermissionChecker;

use MakinaCorpus\AccessControl\PermissionChecker\ChainPermissionChecker;
use MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker;
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
