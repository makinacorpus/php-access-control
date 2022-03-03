<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests;

use MakinaCorpus\AccessControl\AccessResource;
use MakinaCorpus\AccessControl\Tests\Mock\AccessResourceTestMock;
use PHPUnit\Framework\TestCase;

class AccessResourceTest extends TestCase
{
    public function testCanReadPublicProperty(): void
    {
        $aggregate = new AccessResource('untested_type', 'publicProp');

        self::assertSame(12, $aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testCanReadPrivateProperty(): void
    {
        $aggregate = new AccessResource('untested_type', 'privateProp');

        self::assertSame(13, $aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testCanReadStdClassProperty(): void
    {
        $aggregate = new AccessResource('untested_type', 'arbitraryProp');

        $instance = new \stdClass();
        $instance->arbitraryProp = 11;

        self::assertSame(11, $aggregate->findResourceId($instance));
    }

    public function testCanReadPublicMethod(): void
    {
        $aggregate = new AccessResource('untested_type', 'publicMethod');

        self::assertSame(14, $aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testCanReadPrivateMethod(): void
    {
        $aggregate = new AccessResource('untested_type', 'privateMethod');

        self::assertSame(15, $aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testCanReadPublicMethodWithOptionalParameters(): void
    {
        $aggregate = new AccessResource('untested_type', 'publicMethodWithOptionalParam');

        self::assertSame(16, $aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testCanNotReadMethodWithNonOptionalParameters(): void
    {
        $aggregate = new AccessResource('untested_type', 'publicMethodWithNonOptionalParam');

        self::assertNull($aggregate->findResourceId(new AccessResourceTestMock()));
    }

    public function testGetters(): void
    {
        $instance1 = new AccessResource('test1', 'test2');
        self::assertSame('test1', $instance1->getResourceType());
        self::assertSame('test2', $instance1->getResourceIdPropertyName());
    }
}
