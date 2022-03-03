<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Mock;

use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;

/**
 * @codeCoverageIgnore
 */
final class WithResourceResourceLocator implements ResourceLocator
{
    /**
     * {@inheritdoc}
     */
    public function loadResource(string $resourceType, $resourceId)
    {
        if ('test_resource_class' === $resourceType) {
            $ret = new \stdClass();
            $ret->id = $resourceId;
            $ret->type = $resourceType;

            return $ret;
        }

        return null;
    }
}
