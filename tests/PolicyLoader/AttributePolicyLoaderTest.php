<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PolicyLoader;

use MakinaCorpus\AccessControl\PolicyLoader\AttributePolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;

final class AttributePolicyLoaderTest extends AbstractPolicyLoaderTest
{
    protected function createPolicyLoader(): PolicyLoader
    {
        if (PHP_VERSION_ID < 80000) {
            self::markTestSkipped("Attribute policy loader can only work with PHP >= 8.0");
        }

        return new AttributePolicyLoader();
    }
}
