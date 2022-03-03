<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\SubjectLocator;

/**
 * @codeCoverageIgnore
 */
final class NullSubjectLocator implements SubjectLocator
{
    /**
     * {@inheritdoc}
     */
    public function findSubject(): iterable
    {
        return [];
    }
}
