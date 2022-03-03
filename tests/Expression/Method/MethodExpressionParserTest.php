<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\Expression\ExpressionArgument;
use MakinaCorpus\AccessControl\Expression\Method\MethodExpressionParser;
use PHPUnit\Framework\TestCase;

final class MethodExpressionParserTest extends TestCase
{
    public function testMethodWithoutParams(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse(" s0m3_fo-nc3t1on ( ) ");

        self::assertSame('s0m3_fo-nc3t1on', $method->getServiceMethodName());
        self::assertSame([], $method->getArguments());
        self::assertSame(null, $method->getServiceName());
    }

    public function testMethodWithProperty(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("MyMethodCall(foo.bar) ");

        self::assertSame('MyMethodCall', $method->getServiceMethodName());
        self::assertSame(['foo.bar'], $this->flattenArgumentList($method->getArguments()));
        self::assertSame(null, $method->getServiceName());
    }

    public function testMethodWithPropertyAndNamedParameters(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("MyMethodCall(foo: bar.bla) ");

        self::assertSame('MyMethodCall', $method->getServiceMethodName());
        self::assertSame(['foo: bar.bla'], $this->flattenArgumentList($method->getArguments()));
        self::assertSame(null, $method->getServiceName());
    }

    public function testMethodWithParams(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("MyMethodCall(resource, subject , bla, foo) ");

        self::assertSame('MyMethodCall', $method->getServiceMethodName());
        self::assertSame(['resource', 'subject', 'bla', 'foo'], $this->flattenArgumentList($method->getArguments()));
        self::assertSame(null, $method->getServiceName());
    }

    public function testMethodWithNamedParameters(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("MyMethodCall(some_entity: resource, user:subject , bla, the_foo:foo) ");

        self::assertSame('MyMethodCall', $method->getServiceMethodName());
        self::assertSame(['some_entity: resource', 'user: subject', 'bla', 'the_foo: foo'], $this->flattenArgumentList($method->getArguments()));
        self::assertSame(null, $method->getServiceName());
    }

    public function testMethodWithServiceWithoutParams(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("se_Rv1ce.MyMethodCall() ");

        self::assertSame('MyMethodCall', $method->getServiceMethodName());
        self::assertSame([], $this->flattenArgumentList($method->getArguments()));
        self::assertSame('se_Rv1ce', $method->getServiceName());
    }

    public function testMethodWithServiceWithParams(): void
    {
        $parser = new MethodExpressionParser();
        $method = $parser->parse("foo.bar(fizz,buzz) ");

        self::assertSame('bar', $method->getServiceMethodName());
        self::assertSame(['fizz', 'buzz'], $this->flattenArgumentList($method->getArguments()));
        self::assertSame('foo', $method->getServiceName());
    }

    public function testMethodParamsInvalidParameterName(): void
    {
        $parser = new MethodExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Invalid parameter name expression/');
        $parser->parse("foo(ba-)");
    }

    public function testMethodParamMissingComaRaiseError(): void
    {
        $parser = new MethodExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Invalid parameter name expression/');
        $parser->parse("foo(bar baz)");
    }

    public function testMethodParamDoubleComaInRaiseError(): void
    {
        $parser = new MethodExpressionParser();

        self::expectException(AccessConfigurationError::class);
        self::expectExceptionMessageMatches('/Empty parameter/');
        $parser->parse("foo(bar ,, baz)");
    }

    public function testMethodSpaceInFuncRaiseError(): void
    {
        $parser = new MethodExpressionParser();

        self::expectException(AccessConfigurationError::class);
        $parser->parse("fun.ct.ion()");
    }

    public function testMethodTwoDotsInFuncRaiseError(): void
    {
        $parser = new MethodExpressionParser();

        self::expectException(AccessConfigurationError::class);
        $parser->parse("fun ction()");
    }

    private function flattenArgumentList(array $arguments): array
    {
        return \array_map(fn (ExpressionArgument $arg) => $arg->toString(), $arguments);
    }
}
