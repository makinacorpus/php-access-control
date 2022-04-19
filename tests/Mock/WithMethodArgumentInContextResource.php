<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 */
#[\MakinaCorpus\AccessControl\AccessMethod("foo.instanceMethod(bar)")]
final class WithMethodArgumentInContextResource
{
    private int $resourceId = 12;
}
