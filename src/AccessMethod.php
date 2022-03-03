<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Execute a method on the given resource. Resource must be an object instance.
 *
 * Usage:
 *   #[AccessMethod("methodName(subject,...)")]
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessMethod implements AccessPolicy
{
    private string $method;

    public function __construct(string $method)
    {
        $this->method = $method;
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
