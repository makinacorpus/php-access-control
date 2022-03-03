<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\RoleChecker;

interface RoleChecker
{
    /**
     * Has the given subject the given role.
     *
     * Attention, $subject can be null.
     *
     * @param mixed $subject
     *   Can be anything provided by the SubjectLocator instance, if can't
     *   work with the given subject, just return false.
     */
    public function subjectHasRole($subject, string $role): bool;
}
