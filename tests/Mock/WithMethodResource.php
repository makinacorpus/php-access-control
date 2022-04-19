<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 */
#[\MakinaCorpus\AccessControl\AccessMethod("instanceMethod(bar)")]
final class WithMethodResource
{
    public function instanceMethod($bar)
    {
        return 13 === $bar;
    }
}
