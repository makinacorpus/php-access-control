<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Uses symfony/security's UserInterface roles.
 */
final class UserRoleChecker implements RoleChecker
{
    private ?RoleHierarchy $roleHierarchy = null;

    public function setRoleHierarchy(?RoleHierarchy $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    public function subjectHasRole($subject, string $role): bool
    {
        if (!$subject instanceof UserInterface) {
            return false;
        }

        $userRoles = $this->roleHierarchy ? $this->roleHierarchy->getReachableRoleNames($subject->getRoles()) : $subject->getRoles();

        return \in_array($role, $userRoles);
    }
}
