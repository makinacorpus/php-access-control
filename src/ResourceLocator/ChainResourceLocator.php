<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ResourceLocator;

final class ChainResourceLocator implements ResourceLocator
{
    /** @var ResourceLocator[] */
    private iterable $instances;

    /** @param ResourceLocator[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function loadResource(string $resourceType, $resourceId)
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof ResourceLocator);

            $candidate = $instance->loadResource($resourceType, $resourceId);
            if (null !== $candidate && false !== $candidate) {
                return $candidate;
            }
        }
    }
}
