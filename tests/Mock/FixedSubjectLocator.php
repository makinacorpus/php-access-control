<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;

/**
 * @codeCoverageIgnore
 */
final class FixedSubjectLocator implements SubjectLocator
{
    private $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function findSubject(): iterable
    {
        yield $this->subject;
    }
}
