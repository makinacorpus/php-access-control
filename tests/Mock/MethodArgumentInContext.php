<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

class MethodArgumentInContext
{
    public function instanceMethod($bar)
    {
        return 13 === $bar;
    }
}
