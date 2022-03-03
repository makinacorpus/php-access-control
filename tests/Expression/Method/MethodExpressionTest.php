<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\ExpressionArgument;
use MakinaCorpus\AccessControl\Expression\Method\MethodExpression;
use PHPUnit\Framework\TestCase;

final class MethodExpressionTest extends TestCase
{
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

    public function testMapFromContextRaiseErrorIfNestedPropertyIsNotAnObject(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', null, 'buzz'),
        ]);

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/foo is not an object, cannot fetch property 'buzz'/");
        $method->mapArgumentsFromContext(['foo' => 12, 'bar' => 13]);
    }

    public function testMapFromContextRaiseErrorIfOverwrite(): void
    {
        $method = new MethodExpression('doThat', null, [
            new ExpressionArgument('foo', 'bar'),
        ]);

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/foo cannot be overriden from context argument 'bar'/");
        $method->mapArgumentsFromContext(['foo' => 12, 'bar' => 13]);
    }

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
