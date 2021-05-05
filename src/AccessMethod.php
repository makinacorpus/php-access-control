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
#[Attribute]
final class AccessMethod implements AccessPolicy
{
    private string $method;

    public function __construct($method)
    {
        // Doctrine BC compat (is_array() call).
        $this->method = (string) \is_array($method) ? $method['value'] : $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
