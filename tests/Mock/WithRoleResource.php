<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessRole("always_works")
 */
#[\MakinaCorpus\AccessControl\AccessRole("always_works")]
final class WithRoleResource
{
}
