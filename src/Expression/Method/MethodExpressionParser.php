<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\Expression\Expression;
use MakinaCorpus\AccessControl\Expression\ExpressionArgument;
use MakinaCorpus\AccessControl\Expression\ExpressionParser;

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
            $parameters = \array_map(fn ($value) => $this->validateMethodParameterExpression($value), \explode(',', $paramString));
        }

        return new MethodExpression($methodName, $serviceName, $parameters);
    }

    private function validateMethodParameterExpression(string $value): ExpressionArgument
    {
        $name = $context = $property = null;

        if (false !== ($pos = \strpos($value, ':'))) {
            $name = $this->validateMethodParameterName(
                \substr($value, 0, $pos)
            );
            list ($context, $property) = $this->validateMethodParameterValue(
                \substr($value, $pos + 1)
            );
        } else {
            list ($name, $property) = $this->validateMethodParameterValue(
                $value
            );
        }

        return new ExpressionArgument($name, $context, $property);
    }

    /**
     * Validate expression such as VALUE_NAME
     */
    private function validateMethodParameterName(string $value): string
    {
        $value = \trim($value);

        if (empty($value)) {
            throw new AccessConfigurationError("Empty parameter name");
        }
        if (!\preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new AccessConfigurationError(\sprintf("Invalid parameter name expression: %s", $value));
        }

        return $value;
    }

    /**
     * Validate expression such as VALUE_NAME[.PROPERTY_NAME]
     */
    private function validateMethodParameterValue(string $value): array
    {
        $value = \trim($value);

        if (false !== ($pos = \strpos($value, '.'))) {
            return [
                $this->validateMethodParameterName(
                    \substr($value, 0, $pos)
                ),
                $this->validateMethodParameterName(
                    \substr($value, $pos + 1)
                ),
            ];
        } else{
            return [
                $this->validateMethodParameterName(
                    $value
                ),
                null,
            ];
        }
    }

    private function validateMethodMessage(): string
    {
        return "Invalid service method expression: it must match: '[ServiceName.]methodName([param [, ...]])'";
    }
}
