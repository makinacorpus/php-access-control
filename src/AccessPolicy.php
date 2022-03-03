<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Used for targetting our own policies when searching for them.
 */
interface AccessPolicy
{
    /**
     * Return a string representation for logs.
     */
    public function toString(): string;
}
