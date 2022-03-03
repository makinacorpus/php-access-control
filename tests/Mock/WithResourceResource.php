<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessResource("test_resource_class", "resourceId")
 * @MakinaCorpus\AccessControl\AccessRole("always_works")
 */
#[\MakinaCorpus\AccessControl\AccessResource("test_resource_class", "resourceId")]
#[\MakinaCorpus\AccessControl\AccessRole("always_works")]
final class WithResourceResource
{
    private int $resourceId = 12;
}
