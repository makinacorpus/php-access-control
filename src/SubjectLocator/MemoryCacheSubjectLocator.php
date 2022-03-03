<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\SubjectLocator;

/**
 * Cache located subjects in memory.
 *
 * This works until PHP executions remain isolated, this wont work anymore
 * if we plug this onto a different architecture. If we come to that, we
 * should cache it into the RequestStack's current request instead.
 */
final class MemoryCacheSubjectLocator implements SubjectLocator
{
    private SubjectLocator $decorated;
    private ?array $current = null;

    public function __construct(SubjectLocator $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubject(): iterable
    {
        if (null === $this->current) {
            $found = $this->decorated->findSubject();

            $this->current = \is_array($found) ? $found : \iterator_to_array($found);
        }

        return $this->current;
    }
}
