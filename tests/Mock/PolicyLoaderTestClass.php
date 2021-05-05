<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

/**
 * @UnrelatedAnnotation()
 * @MakinaCorpus\AccessControl\AccessAllOrNothing()
 * @MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")
 * @MakinaCorpus\AccessControl\AccessPermission("some permission")
 * @MakinaCorpus\AccessControl\AccessRole("some role")
 * @codeCoverageIgnore
 */
#[MakinaCorpus\AccessControl\AccessAllOrNothing()]
#[MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
#[MakinaCorpus\AccessControl\AccessPermission("some permission")]
#[MakinaCorpus\AccessControl\AccessRole("some role")]
abstract class PolicyLoaderTestClass
{
    /**
     * @UnrelatedAnnotation()
     * @MakinaCorpus\AccessControl\AccessAllOrNothing()
     * @MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")
     * @MakinaCorpus\AccessControl\AccessPermission("some permission")
     * @MakinaCorpus\AccessControl\AccessRole("some role")
     */
    #[MakinaCorpus\AccessControl\AccessAllOrNothing()]
    #[MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
    #[MakinaCorpus\AccessControl\AccessPermission("some permission")]
    #[MakinaCorpus\AccessControl\AccessRole("some role")]
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
#[MakinaCorpus\AccessControl\AccessAllOrNothing()]
#[MakinaCorpus\AccessControl\AccessService("SomeService.someMethod")]
#[MakinaCorpus\AccessControl\AccessPermission("some permission")]
#[MakinaCorpus\AccessControl\AccessRole("some role")]
function readPoliciesFromMe(): void
{
}

/**
 * @Annotation
 * @codeCoverageIgnore
 */
final class UnrelatedAnnotation
{
}
