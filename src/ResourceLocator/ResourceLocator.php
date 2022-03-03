<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ResourceLocator;

interface ResourceLocator
{
    /**
     * Find a resource.
     */
    public function loadResource(string $resourceType, /* mixed */ $resourceId) /* null|mixed */;
}
