<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\SubjectLocator;

use MakinaCorpus\AccessControl\SubjectLocator\ChainSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use PHPUnit\Framework\TestCase;

final class ChainSubjectLocatorTest extends TestCase
{
    public function testAllAreCalled(): void
    {
        $chain = new ChainSubjectLocator([
            new class() implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    if (false) {
                        yield "Booh !";
                    }
                }
            },
            new class() implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    yield 'Hey !';
                    yield 'Foo !';
                }
            },
            new class() implements SubjectLocator
            {
                public function findSubject(): iterable
                {
                    yield 'Bar !';
                }
            },
        ]);

        self::assertSame(
            [
                'Hey !',
                'Foo !',
                'Bar !',
            ],
            self::toArray($chain->findSubject())
        );
    }

    private static function toArray(iterable $value): array
    {
        return \is_array($value) ? $value : \iterator_to_array($value);
    }
}
