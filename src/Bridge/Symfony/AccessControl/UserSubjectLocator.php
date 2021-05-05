<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\Subject\SubjectLocator;
use Symfony\Component\Security\Core\Security;

final class UserSubjectLocator implements SubjectLocator
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubject()
    {
        return $this->security->getUser();
    }
}
