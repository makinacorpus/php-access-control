<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * All resource set policies must allow.
 *
 * Usage:
 *   #[AccessAllOrNothing]
 */
#[\Attribute]
final class AccessAllOrNothing implements AccessPolicy
{
    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessAllOrNothing()';
    }
}
