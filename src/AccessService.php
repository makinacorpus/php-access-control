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
#[Attribute]
final class AccessService implements AccessPolicy
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
