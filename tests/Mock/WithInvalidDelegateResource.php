<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessDelegate("this_class_does_not_exists")
 */
#[\MakinaCorpus\AccessControl\AccessDelegate("this_class_does_not_exists")]
final class WithInvalidDelegateResource
{
}
