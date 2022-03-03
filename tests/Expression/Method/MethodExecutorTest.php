<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\Method\MethodExecutor;
use MakinaCorpus\AccessControl\Tests\Mock\MethodExecutorTestMock;
use PHPUnit\Framework\TestCase;

final class MethodExecutorTest extends TestCase
{
    public function testExecuteFunction(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithoutParameters';
        $executor = new MethodExecutor();

        $parameters = [];

        self::assertSame('OK', $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteClassInstanceMethod(): void
    {
        $executor = new MethodExecutor();

        $parameters = [
            'bar' => "Bar",
            'baz' => new \stdClass(),
            'foo' => 12,
        ];

        self::assertSame('INSTANCE OK', $executor->callResourceMethod(new MethodExecutorTestMock(), 'instanceMethod', $parameters));
    }

    public function testExecuteClassInstanceMethodAsArray(): void
    {
        $executor = new MethodExecutor();

        $parameters = [
            'bar' => "Bar",
            'baz' => new \stdClass(),
            'foo' => 12,
        ];

        self::assertSame('INSTANCE OK', $executor->callCallback([new MethodExecutorTestMock(), 'instanceMethod'], $parameters));
    }

    public function testExecuteCallback(): void
    {
        $executor = new MethodExecutor();

        $parameters = [
            'bar' => "Bar",
            'baz' => new \stdClass(),
            'foo' => 12,
        ];

        self::assertSame('OK', $executor->callCallback(
            function (int $foo, $bar, \stdClass $baz, ?\DateTimeInterface $fizz) {
                return 'OK';
            },
            $parameters
        ));
    }

    public function testExecuteClassStaticMethod(): void
    {
        $executor = new MethodExecutor();

        $parameters = [
            'bar' => "Bar",
            'baz' => new \stdClass(),
            'foo' => 12,
        ];

        self::assertSame('STATIC OK', $executor->callResourceMethod(new MethodExecutorTestMock(), 'staticMethod', $parameters));
    }

    public function testExecuteFunctionMethodWithNonExistingFunctionRaiseError(): void
    {
        self::markTestIncomplete();
    }

    public function testExecuteObjectMethodWithNonExistingClassRaiseError(): void
    {
        self::markTestIncomplete();
    }

    public function testExecuteObjectMethodWithNonExistingMethodRaiseError(): void
    {
        self::markTestIncomplete();
    }

    public function testExecuteObjectMethodWithAbstractMethodRaiseError(): void
    {
        self::markTestIncomplete();
    }

    public function testExecuteObjectMethodWithNonPublicMethodRaiseError(): void
    {
        self::markTestIncomplete();
    }

    public function testExecuteWithAdditionalParameters(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithoutParameters';
        $executor = new MethodExecutor();

        $parameters = ['foo' => 12, 'bar' => 'foo'];

        self::assertSame('OK', $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithMissingOptionalParameter(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithOptionalParameter';
        $executor = new MethodExecutor();

        $parameters = [];

        self::assertSame('OK', $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithDefaultValuedOptionalParameter(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = ['foo' => 11];

        self::assertSame(23, $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithSpecializedInstance(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithSpecializedInstance';
        $executor = new MethodExecutor();

        $parameters = ['iterator' => new \EmptyIterator()];

        self::assertSame('OK', $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithManyParameters(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = ['foo' => 37, 'bar' => 66];

        self::assertSame(103, $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithMissingParameterRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = [];

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches('/missing parameter/');
        $executor->callCallback($functionName, $parameters);
    }

    public function testExecuteWithTypeMismatchRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = ['foo' => 37, 'bar' => 'Mouh'];

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches("/expected one of 'int', 'string' given/");
        $executor->callCallback($functionName, $parameters);
    }

    public function testExecuteWithUnionType(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithUnionType';

        if (!\function_exists($functionName)) {
            self::markTestSkipped("This test is meant for PHP >= 8");
        }

        $executor = new MethodExecutor();

        $parameters = ['foo' => 12];

        self::assertSame('OK', $executor->callCallback($functionName, $parameters));
    }

    public function testExecuteWithTypeMismatchInUnionTypeRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithUnionType';

        if (!\function_exists($functionName)) {
            self::markTestSkipped("This test is meant for PHP >= 8");
        }

        $executor = new MethodExecutor();

        $parameters = ['foo' => 'Mouh'];

        self::expectException(AccessRuntimeError::class);
        self::expectExceptionMessageMatches('/expected \'int|bool|float\'.*\'string\' given/');
        $executor->callCallback($functionName, $parameters);
    }
}

if (0 < \version_compare(PHP_VERSION, '8.0.0')) {
    function testFunctionWithUnionType(int|bool|float $foo)
    {
        return 'OK';
    }
}

function testFunctionWithSpecializedInstance(\Iterator $iterator)
{
    return 'OK';
}

function testFunctionWithoutParameters()
{
    return 'OK';
}

function testFunctionWithOptionalParameter(?\stdClass $foo)
{
    return 'OK';
}

function testFunctionWithDefaultValue(int $foo, int $bar = 12)
{
    return $bar + $foo;
}
