<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

class MethodExecutorTestMock
{
    public static function staticMethod(int $foo, $bar, \stdClass $baz, ?\DateTimeInterface $fizz)
    {
        return 'STATIC OK';
    }

    public function instanceMethod(int $foo, $bar, \stdClass $baz, ?\DateTimeInterface $fizz)
    {
        return 'INSTANCE OK';
    }
}
