<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Subject;

final class ChainSubjectLocator implements SubjectLocator
{
    /** @var SubjectLocator[] */
    private iterable $instances;

    /** @param SubjectLocator[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubject()
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof SubjectLocator);

            if ($subject = $instance->findSubject()) {
                return $subject;
            }
        }

        return null;
    }
}
