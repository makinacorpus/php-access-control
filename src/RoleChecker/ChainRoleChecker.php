<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\RoleChecker;

final class ChainRoleChecker implements RoleChecker
{
    /** @var RoleChecker[] */
    private iterable $instances;

    /** @param RoleChecker[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function subjectHasRole($subject, string $role): bool
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof RoleChecker);

            if ($instance->subjectHasRole($subject, $role)) {
                return true;
            }
        }

        return false;
    }
}
