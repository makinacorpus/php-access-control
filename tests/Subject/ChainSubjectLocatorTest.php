<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Subject;

use MakinaCorpus\AccessControl\Subject\ChainSubjectLocator;
use MakinaCorpus\AccessControl\Subject\SubjectLocator;
use PHPUnit\Framework\TestCase;

final class ChainSubjectLocatorTest extends TestCase
{
    public function testAllAreCalled(): void
    {
        $chain = new ChainSubjectLocator([
            new class() implements SubjectLocator
            {
                public function findSubject()
                {
                    return null;
                }
            },
            new class() implements SubjectLocator
            {
                public function findSubject()
                {
                    return 'Hey !';
                }
            },
            new class() implements SubjectLocator
            {
                /** @codeCoverageIgnore */
                public function findSubject()
                {
                    return 'Bar !';
                }
            },
        ]);

        self::assertSame('Hey !', $chain->findSubject());
    }
}
