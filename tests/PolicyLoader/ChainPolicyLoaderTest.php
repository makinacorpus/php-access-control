<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\PolicyLoader;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use MakinaCorpus\AccessControl\PolicyLoader\AnnotationPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\ChainPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;

final class ChainPolicyLoaderTest extends AbstractPolicyLoaderTest
{
    protected function createPolicyLoader(): PolicyLoader
    {
        AnnotationRegistry::registerLoader('class_exists');

        return new ChainPolicyLoader([
            new AnnotationPolicyLoader(
                new AnnotationReader()
            )
        ]);
    }
}
