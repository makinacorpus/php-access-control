<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PolicyLoader;

use MakinaCorpus\AccessControl\ServiceLocator\ChainServiceLocator;
use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use PHPUnit\Framework\TestCase;

final class ChainServiceLocatorTest extends TestCase
{
    public function testIt(): void
    {
        $instance = new ChainServiceLocator([
            // First one will return null.
            new class () implements ServiceLocator
            {
                public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
                {
                    return null;
                }
            },
            // Second one will return some value.
            new class () implements ServiceLocator
            {
                public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
                {
                    return fn () => 12;
                }
            },
            // Third one will return some value we will never reach.
            new class () implements ServiceLocator
            {
                public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
                {
                    throw new \Exception("I shall not be called.");
                }
            },
        ]);

        self::assertSame(12, ($instance->findServiceMethod('foo', 'bar'))());
    }
}
