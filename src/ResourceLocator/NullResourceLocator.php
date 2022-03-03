<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ResourceLocator;

/**
 * @codeCoverageIgnore
 */
class NullResourceLocator implements ResourceLocator
{
    /**
     * {@inheritdoc}
     */
    public function loadResource(string $resourceType, $resourceId)
    {
        return null;
    }
}
