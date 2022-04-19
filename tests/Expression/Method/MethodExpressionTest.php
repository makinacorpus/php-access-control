<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\ExpressionArgument;
use MakinaCorpus\AccessControl\Expression\Method\MethodExpression;
use PHPUnit\Framework\TestCase;

final class MethodExpressionTest extends TestCase
{
    /**
     * e.g. "doThat(foo)" and we pass an additional unused "bar".
     */
    public function testMapFromContextKeepsExtraValues(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null),
        ]);

        self::assertSame(
            [
                'foo' => 12,
                'bar' => 13,
            ],
            $method->mapArgumentsFromContext(
                [
                    'foo' => 12,
                    'bar' => 13,
                ]
            )
        );
    }

    /**
     * e.g. "doThat(foo: foo.buzz)".
     */
    public function testMapFromContextFetchNestedProperties(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null, 'buzz'),
        ]);

        self::assertSame(
            [
                'foo' => 12,
                'bar' => 13,
            ],
            $method->mapArgumentsFromContext(
                [
                    'foo' => (object) ['buzz' => 12],
                    'bar' => 13,
                ]
            )
        );
    }

    /**
     * e.g. "doThat(foo: foo.baah)" where property "baah" does not exist.
     */
    public function testMapFromContextGiveNullIfNestedPropertyDoesNotExist(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null, 'baah'),
        ]);

        self::assertSame(
            [
                'foo' => null,
                'bar' => 13,
            ],
            $method->mapArgumentsFromContext(
                [
                    'foo' => (object) ['buzz' => 12],
                    'bar' => 13,
                ]
            )
        );
    }

    /**
     * e.g: "doThat(id: foo.id)" where "foo" does not exist in context.
     */
    public function testMapFromContextRaiseErrorIfNamedContextDoesNotExist(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('id', 'foo', 'id'),
        ]);

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/Argument \\\$id maps the context argument 'foo' which does not exist'/");
        $method->mapArgumentsFromContext(['foo' => 12, 'bar' => 13]);
    }

    /**
     * e.g: "doThat(foo: foo.buzz)" where "foo" is not an object.
     */
    public function testMapFromContextRaiseErrorIfNestedPropertyIsNotAnObject(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null, 'buzz'),
        ]);

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/foo is not an object, cannot fetch property 'buzz'/");
        $method->mapArgumentsFromContext(['foo' => 12, 'bar' => 13]);
    }

    /**
     * e.g. "doThat(foo: bar)" where "bar" already exists in arguments.
     */
    public function testMapFromContextRaiseErrorIfOverwrite(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', 'bar'),
        ]);

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/foo cannot be overriden from context argument 'bar'/");
        $method->mapArgumentsFromContext(['foo' => 12, 'bar' => 13]);
    }

    /**
     * e.g. "doThat(foo, bar: buzz)" will correctly map "buzz" as "bar".
     */
    public function testMapFromContextDoRenameValues(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null),
            new ExpressionArgument('bar', 'buzz'),
        ]);

        self::assertSame(
            [
                'foo' => 12,
                'buzz' => 13,
                'bar' => 13,
            ],
            $method->mapArgumentsFromContext(
                [
                    'foo' => 12,
                    'buzz' => 13,
                ]
            )
        );
    }
}
