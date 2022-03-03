<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Execute a domain-bound function or service method.
 *
 * Usage:
 *   #[AccessService("ServiceName.methodName(resource, subject,...)")]
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessService implements AccessPolicy
{
    private string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessService(' . $this->expression . ')';
    }
}
