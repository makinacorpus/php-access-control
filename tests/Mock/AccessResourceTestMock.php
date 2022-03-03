<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

class AccessResourceTestMock
{
    public $publicProp = 12;

    private $privateProp = 13;

    public function publicMethod(): int
    {
        return 14;
    }

    private function privateMethod(): int
    {
        return 15;
    }

    public function publicMethodWithOptionalParam(?string $foo = null): int
    {
        return 16;
    }

    /**
     * @codeCoverageIgnore
     */
    public function publicMethodWithNonOptionalParam(string $foo): int
    {
        throw new \Exception("I shall not be called.");
    }
}
