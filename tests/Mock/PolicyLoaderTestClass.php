<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @codeCoverageIgnore
 */
#[\MakinaCorpus\AccessControl\AccessAllOrNothing()]
#[\MakinaCorpus\AccessControl\AccessPermission("some permission")]
#[\MakinaCorpus\AccessControl\AccessResource("foo", "bar")]
#[\MakinaCorpus\AccessControl\AccessRole("some role")]
#[\MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
abstract class PolicyLoaderTestClass
{
    #[\MakinaCorpus\AccessControl\AccessAllOrNothing()]
    #[\MakinaCorpus\AccessControl\AccessPermission("some permission")]
    #[\MakinaCorpus\AccessControl\AccessResource("foo", "bar")]
    #[\MakinaCorpus\AccessControl\AccessRole("some role")]
    #[\MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
    public function normalMethod(): void
    {
    }

    protected function protectedMethod(): void
    {
    }

    abstract public function abstractMethod(): void;
}

/**
 * @codeCoverageIgnore
 */
#[\MakinaCorpus\AccessControl\AccessAllOrNothing()]
#[\MakinaCorpus\AccessControl\AccessPermission("some permission")]
#[\MakinaCorpus\AccessControl\AccessResource("foo", "bar")]
#[\MakinaCorpus\AccessControl\AccessRole("some role")]
#[\MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
function readPoliciesFromMe(): void
{
}
