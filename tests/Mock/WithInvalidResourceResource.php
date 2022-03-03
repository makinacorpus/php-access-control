<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessResource("this_will_not_work", "resourceId")
 * @MakinaCorpus\AccessControl\AccessRole("always_works")
 */
#[\MakinaCorpus\AccessControl\AccessResource("this_will_not_work", "resourceId")]
#[\MakinaCorpus\AccessControl\AccessRole("always_works")]
final class WithInvalidResourceResource
{
    private int $resourceId = 12;
}
