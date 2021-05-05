<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Expression;

use MakinaCorpus\AccessControl\AccessConfigurationError;
use MakinaCorpus\AccessControl\Expression\FallbackExpressionParser;
use PHPUnit\Framework\TestCase;

final class FallbackExpressionParserTest extends TestCase
{
    public function testMethodWithoutParams(): void
    {
        $parser = new FallbackExpressionParser();
        $method = $parser->parseMethod(" s0m3_fo-nc3t1on ( ) ");

        self::assertSame('s0m3_fo-nc3t1on', $method->methodName);
        self::assertSame([], $method->parameters);
        self::assertSame(null, $method->serviceName);
    }

    public function testMethodWithParams(): void
    {
        $parser = new FallbackExpressionParser();
        $method = $parser->parseMethod("MyMethodCall(resource, subject , bla, foo) ");

        self::assertSame('MyMethodCall', $method->methodName);
        self::assertSame(['resource', 'subject', 'bla', 'foo'], $method->parameters);
        self::assertSame(null, $method->serviceName);
    }

    public function testMethodWithServiceWithoutParams(): void
    {
        $parser = new FallbackExpressionParser();
        $method = $parser->parseMethod("se_Rv1ce.MyMethodCall() ");

        self::assertSame('MyMethodCall', $method->methodName);
        self::assertSame([], $method->parameters);
        self::assertSame('se_Rv1ce', $method->serviceName);
    }

    public function testMethodWithServiceWithParams(): void
    {
        $parser = new FallbackExpressionParser();
        $method = $parser->parseMethod("foo.bar(fizz,buzz) ");

        self::assertSame('bar', $method->methodName);
        self::assertSame(['fizz', 'buzz'], $method->parameters);
        self::assertSame('foo', $method->serviceName);
    }

    public function testMethodParamsInvalidParameterName(): void
    {
        $parser = new FallbackExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/invalid parameter name/');
        $parser->parseMethod("foo(ba-)");
    }

    public function testMethodParamMissingComaRaiseError(): void
    {
        $parser = new FallbackExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/missing coma/');
        $parser->parseMethod("foo(bar baz)");
    }

    public function testMethodParamDoubleComaInRaiseError(): void
    {
        $parser = new FallbackExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/empty parameter name/');
        $parser->parseMethod("foo(bar ,, baz)");
    }

    public function testMethodSpaceInFuncRaiseError(): void
    {
        $parser = new FallbackExpressionParser();

        self::expectException(AccessConfigurationError::class);
        $parser->parseMethod("fun.ct.ion()");
    }

    public function testMethodTwoDotsInFuncRaiseError(): void
    {
        $parser = new FallbackExpressionParser();

        self::expectException(AccessConfigurationError::class);
        $parser->parseMethod("fun ction()");
    }
}
