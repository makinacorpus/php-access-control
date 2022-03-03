<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use Symfony\Component\Security\Core\Security;

/**
 * Uses symfony/security's UserInterface tied to the incoming request as subject.
 */
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
    public function findSubject(): iterable
    {
        if ($user = $this->security->getUser()) {
            yield $user;
        }
    }
}
