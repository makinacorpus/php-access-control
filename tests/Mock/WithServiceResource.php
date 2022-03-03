<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 * @MakinaCorpus\AccessControl\AccessService("This.That(resource, subject)")
 */
#[\MakinaCorpus\AccessControl\AccessService("This.That(resource, subject)")]
final class WithServiceResource
{
}
