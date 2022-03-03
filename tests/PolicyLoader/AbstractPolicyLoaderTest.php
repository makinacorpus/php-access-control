<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PolicyLoader;

use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;
use MakinaCorpus\AccessControl\Tests\Mock\PolicyLoaderTestClass;
use PHPUnit\Framework\TestCase;

abstract class AbstractPolicyLoaderTest extends TestCase
{
    public function testLoadFromClass(): void
    {
        $loader = $this->createPolicyLoader();

        $count = 0;
        foreach ($loader->loadFromClass(PolicyLoaderTestClass::class) as $policy) {
            self::assertInstanceOf(AccessPolicy::class, $policy);
            $count++;
        }

        self::assertSame(5, $count);
    }

    public function testLoadFromClassErrorWhenClassDoesNotExist(): void
    {
        $loader = $this->createPolicyLoader();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Class does not exist/');

        foreach ($loader->loadFromClass('NonExistingClass') as $class) {
            self::fail();
        }
    }

    public function testLoadFromClassMethod(): void
    {
        $loader = $this->createPolicyLoader();

        $count = 0;
        foreach ($loader->loadFromClassMethod(PolicyLoaderTestClass::class, 'normalMethod') as $policy) {
            self::assertInstanceOf(AccessPolicy::class, $policy);
            $count++;
        }

        self::assertSame(5, $count);
    }

    public function testLoadFromClassMethodErrorWhenClassDoesNotExist(): void
    {
        $loader = $this->createPolicyLoader();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Class does not exist/');

        foreach ($loader->loadFromClassMethod('NonExistingClass', 'nonExistingMethod') as $class) {
            self::fail();
        }
    }

    public function testLoadFromClassMethodErrorWhenMethodDoesNotExist(): void
    {
        $loader = $this->createPolicyLoader();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Class method does not exist/');

        foreach ($loader->loadFromClassMethod(PolicyLoaderTestClass::class, 'nonExistingMethod') as $class) {
            self::fail();
        }
    }

    public function testLoadFromFunction(): void
    {
        self::markTestIncomplete();
    }

    abstract protected function createPolicyLoader(): PolicyLoader;
}
