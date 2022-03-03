<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessDelegate(WithRoleResource::class)
 */
#[\MakinaCorpus\AccessControl\AccessDelegate(WithRoleResource::class)]
final class WithDelegateResource
{
}
