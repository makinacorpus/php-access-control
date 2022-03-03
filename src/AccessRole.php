<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Ensure subject has the given role.
 *
 * Usage:
 *   #[AccessRole("ROLE_ADMIN")]
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessRole implements AccessPolicy
{
    private string $role;

    public function __construct(string $role)
    {
        $this->role = $role;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessRole(' . $this->role . ')';
    }
}
