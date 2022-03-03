<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Execute a method on the given resource. Resource must be an object instance.
 *
 * Usage:
 *   #[AccessMethod("methodName(subject,...)")]
 *
 * @Annotation
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessMethod implements AccessPolicy
{
    private string $method;

    public function __construct($method)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($method)) {
            if (\is_array($method['value'])) {
                $this->method = $method['value'][0];
            } else {
                $this->method = $method['value'];
            }
        } else {
            $this->method = $method;
        }
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessMethod(' . $this->method . ')';
    }
}
