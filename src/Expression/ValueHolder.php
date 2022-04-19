<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

/**
 * Interface for proxifying value access to other objects.
 */
interface ValueHolder
{
    /**
     * Get value with name.
     */
    public function getValue(string $propertyName): mixed;
}
