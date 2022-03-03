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
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
final class AccessPermission implements AccessPolicy
{
    private string $permission;

    public function __construct($permission)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($permission)) {
            if (\is_array($permission['value'])) {
                $this->permission = $permission['value'][0];
            } else {
                $this->permission = $permission['value'];
            }
        } else {
            $this->permission = $permission;
        }
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessPermission(' . $this->permission . ')';
    }
}
