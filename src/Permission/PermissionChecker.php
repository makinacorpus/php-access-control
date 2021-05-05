<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Permission;

interface PermissionChecker
{
    /**
     * Has the given subject the given permission.
     *
     * Attention, $subject can be null.
     *
     * @param mixed $subject
     *   Can be anything provided by the SubjectLocator instance, if can't
     *   work with the given subject, just return false.
     */
    public function subjectHasPermission($subject, string $permission): bool;
}
