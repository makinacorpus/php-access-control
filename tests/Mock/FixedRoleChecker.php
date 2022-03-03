<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;

/**
 * @codeCoverageIgnore
 */
final class FixedRoleChecker implements RoleChecker
{
    private $return;

    public function __construct(bool $return)
    {
        $this->return = $return;
    }

    /**
     * {@inheritdoc}
     */
    public function subjectHasRole($subject, string $role): bool
    {
        return $this->return;
    }
}
