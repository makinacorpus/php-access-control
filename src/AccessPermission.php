<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Ensure subject has the given permission.
 *
 * Usage:
 *   #[AccessPermission("do that")]
 *
 * @Annotation
 */
#[Attribute]
final class AccessPermission implements AccessPolicy
{
    private string $permission;

    public function __construct($permission)
    {
        // Doctrine BC compat (is_array() call).
        $this->permission = (string) \is_array($permission) ? $permission['value'] : $permission;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }
}
