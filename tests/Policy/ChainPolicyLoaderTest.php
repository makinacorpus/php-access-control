<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Policy;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use MakinaCorpus\AccessControl\Policy\AnnotationPolicyLoader;
use MakinaCorpus\AccessControl\Policy\ChainPolicyLoader;
use MakinaCorpus\AccessControl\Policy\PolicyLoader;

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
