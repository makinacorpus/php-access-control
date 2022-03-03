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
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessRole implements AccessPolicy
{
    private string $role;

    public function __construct($role)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($role)) {
            if (\is_array($role['value'])) {
                $this->role = $role['value'][0];
            } else {
                $this->role = $role['value'];
            }
        } else {
            $this->role = $role;
        }
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
