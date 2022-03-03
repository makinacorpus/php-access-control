<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\ServiceLocator;

use MakinaCorpus\AccessControl\ResourceLocator\ChainResourceLocator;
use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;
use PHPUnit\Framework\TestCase;

final class ChainResourceLocatorTest extends TestCase
{
    public function testIt(): void
    {
        $instance = new ChainResourceLocator([
            // First one will return null.
            new class () implements ResourceLocator
            {
                public function loadResource(string $resourceType, $resourceId)
                {
                    return null;
                }
            },
            // Second one will return some value.
            new class () implements ResourceLocator
            {
                public function loadResource(string $resourceType, $resourceId)
                {
                    return 12;
                }
            },
            // Third one will return some value we will never reach.
            new class () implements ResourceLocator
            {
                public function loadResource(string $resourceType, $resourceId)
                {
                    throw new \Exception("I shall not be called.");
                }
            },
        ]);

        self::assertSame(12, $instance->loadResource('foo', 'bar'));
    }
}
