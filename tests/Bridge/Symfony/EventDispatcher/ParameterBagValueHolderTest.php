<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\EventDispatcher;

use MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher\ParameterBagValueHolder;
use MakinaCorpus\AccessControl\Expression\ValueAccessor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;

final class ParameterBagValueHolderTest extends TestCase
{
    public function testGet(): void
    {
        $instance = new ParameterBagValueHolder(new InputBag(['foo' => 'bar']));

        self::assertSame('bar', ValueAccessor::getValueFrom($instance, 'foo'));
    }

    public function testGetWithNonExistingValue(): void
    {
        $instance = new ParameterBagValueHolder(new InputBag());

        self::assertNull(ValueAccessor::getValueFrom($instance, 'foo'));
    }
}
