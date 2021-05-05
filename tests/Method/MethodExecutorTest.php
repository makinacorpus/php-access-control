<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Authorization;

use MakinaCorpus\AccessControl\AccessConfigurationError;
use MakinaCorpus\AccessControl\Expression\Method\MethodExecutor;
use PHPUnit\Framework\TestCase;

final class MethodExecutorTest extends TestCase
{
    public function testExecuteFunction(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithoutParameters';
        $executor = new MethodExecutor();

        $parameters = [];

        self::assertSame('OK', $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteObjectMethod(): void
    {
        self::markTestIncomplete();
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

        $parameters = [12, 'foo'];

        self::assertSame('OK', $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteWithMissingOptionalParameter(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithOptionalParameter';
        $executor = new MethodExecutor();

        $parameters = [];

        self::assertSame('OK', $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteWithDefaultValuedOptionalParameter(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = [11];

        self::assertSame(23, $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteWithManyParameters(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = [37, 66];

        self::assertSame(103, $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteWithMissingParameterRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = [];

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/missing parameter/');
        $executor->callFunction($functionName, $parameters);
    }

    public function testExecuteWithTypeMismatchRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithDefaultValue';
        $executor = new MethodExecutor();

        $parameters = [37, 'Mouh'];

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/expected \'int\'.*\'string\' given/');
        $executor->callFunction($functionName, $parameters);
    }

    public function testExecuteWithUnionType(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithUnionType';

        if (!\function_exists($functionName)) {
            self::markTestSkipped("This test is meant for PHP >= 8");
        }

        $executor = new MethodExecutor();

        $parameters = [12];

        self::assertSame('OK', $executor->callFunction($functionName, $parameters));
    }

    public function testExecuteWithTypeMismatchInUnionTypeRaiseError(): void
    {
        $functionName = __NAMESPACE__ . '\\testFunctionWithUnionType';

        if (!\function_exists($functionName)) {
            self::markTestSkipped("This test is meant for PHP >= 8");
        }

        $executor = new MethodExecutor();

        $parameters = ['Mouh'];

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/expected \'int|bool|float\'.*\'string\' given/');
        $executor->callFunction($functionName, $parameters);
    }
}

if (0 < \version_compare(PHP_VERSION, '8.0.0')) {
    function testFunctionWithUnionType(int|bool|float $foo)
    {
        return 'OK';
    }
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
