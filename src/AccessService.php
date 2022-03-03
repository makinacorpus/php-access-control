<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Execute a domain-bound function or service method.
 *
 * Usage:
 *   #[AccessService("ServiceName.methodName(resource, subject,...)")]
 *
 * @Annotation
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessService implements AccessPolicy
{
    private string $expression;

    public function __construct($expression)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($expression)) {
            if (\is_array($expression['value'])) {
                $this->expression = $expression['value'][0];
            } else {
                $this->expression = $expression['value'];
            }
        } else {
            $this->expression = $expression;
        }
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
