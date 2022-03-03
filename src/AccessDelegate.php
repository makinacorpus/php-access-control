<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Delegates access check to another class attributes.
 *
 * This has limitations, the target class cannot use the AccessMethod
 * attribute because we would need to instanciate it, and we can't.
 *
 * Usage:
 *   #[AccessDelegate(SomeOtherClass::class)]
 */
#[\Attribute]
final class AccessDelegate implements AccessPolicy
{
    private string $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessDelegate(' . $this->className . ')';
    }
}
