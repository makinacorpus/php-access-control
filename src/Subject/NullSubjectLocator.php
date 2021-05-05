<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Subject;

/**
 * @codeCoverageIgnore
 */
final class NullSubjectLocator implements SubjectLocator
{
    /**
     * {@inheritdoc}
     */
    public function findSubject()
    {
        return null;
    }
}
