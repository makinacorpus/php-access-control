<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

use MakinaCorpus\AccessControl\AccessConfigurationError;

/**
 * Implementation of ExpressionParser that only handles object method call.
 *
 * This is a very primitive one, you may use if you wish to avoid the
 * symfony/expression-language dependency and if it is enough for your usage.
 */
final class MethodExpressionParser implements ExpressionParser
{
    public function parse(string $expression): Expression
    {
        // Service method can be "[Service.]method([param, ...])"
        $funcMatch = [];
        if (!\preg_match('/^ ([^()\s]+) \s* \((.*)\) $/msx', \trim($expression), $funcMatch)) {
            throw new AccessConfigurationError($this->validateMethodMessage());
        }

        $methodName = null;
        $parameters = [];
        $serviceName = null;

        $pieces = \explode('.', $funcMatch[1]);
        if (1 === \count($pieces)) {
            $methodName = \trim($pieces[0]);
        } else if (2 === \count($pieces)) {
            $methodName = \trim($pieces[1]);
            $serviceName = \trim($pieces[0]);
        } else {
            throw new AccessConfigurationError($this->validateMethodMessage());
        }

        // Match parameters.
        $paramString = \trim($funcMatch[2]);
        if (!empty($paramString)) {
            $parameters = \array_map(fn ($value) => $this->validateMethodParameter($value), \explode(',', $paramString));
        }

        return new MethodExpression($methodName, $parameters, $serviceName);
    }

    private function validateMethodParameter(string $value): string
    {
        $ret = \trim($value);
        if (empty($ret)) {
            throw new AccessConfigurationError("Invalid service method expression: empty parameter name");
        }
        if (false !== \strpos($ret, ' ')) {
            throw new AccessConfigurationError(\sprintf("Invalid service method expression: missing coma between: %s", $ret));
        }
        if (!\preg_match('/^[a-zA-Z0-9_]+$/', $ret)) {
            throw new AccessConfigurationError(\sprintf("Invalid service method expression: invalid parameter name: %s", $ret));
        }
        return $ret;
    }

    private function validateMethodMessage(): string
    {
        return "Invalid service method expression: it must match: '[ServiceName.]methodName([param [, ...]])'";
    }
}
