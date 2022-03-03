<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\SubjectLocator;

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
    public function findSubject(): iterable
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof SubjectLocator);

            foreach ($instance->findSubject() as $value) {
                if ($value !== null) {
                    yield $value;
                }
            }
        }
    }
}
