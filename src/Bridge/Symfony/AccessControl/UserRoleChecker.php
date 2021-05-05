<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\Role\RoleChecker;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserRoleChecker implements RoleChecker
{
    public function subjectHasRole($subject, string $role): bool
    {
        return $subject instanceof UserInterface && \in_array($role, $subject->getRoles());
    }
}
