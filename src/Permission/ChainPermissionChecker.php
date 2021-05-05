<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Permission;

final class ChainPermissionChecker implements PermissionChecker
{
    /** @var PermissionChecker[] */
    private iterable $instances;

    /** @param PermissionChecker[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function subjectHasPermission($subject, string $permission): bool
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof PermissionChecker);

            if ($instance->subjectHasPermission($subject, $permission)) {
                return true;
            }
        }

        return false;
    }
}
