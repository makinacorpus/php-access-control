<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Ensure subject has the given role.
 *
 * Usage:
 *   #[AccessRole("ROLE_ADMIN")]
 *
 * @Annotation
 */
#[Attribute]
final class AccessRole implements AccessPolicy
{
    private string $role;

    public function __construct($role)
    {
        // Doctrine BC compat (is_array() call).
        $this->role = (string) \is_array($role) ? $role['value'] : $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
