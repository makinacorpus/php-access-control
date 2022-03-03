<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PolicyLoader;

use MakinaCorpus\AccessControl\PolicyLoader\AttributePolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\ChainPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;

final class ChainPolicyLoaderTest extends AbstractPolicyLoaderTest
{
    protected function createPolicyLoader(): PolicyLoader
    {
        return new ChainPolicyLoader([
            new AttributePolicyLoader()
        ]);
    }
}
