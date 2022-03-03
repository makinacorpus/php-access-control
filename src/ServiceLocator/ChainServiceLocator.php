<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ServiceLocator;

final class ChainServiceLocator implements ServiceLocator
{
    /** @var ServiceLocator[] */
    private iterable $instances;

    /** @param ServiceLocator[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
    {
        foreach ($this->instances as $instance) {
            \assert($instance instanceof ServiceLocator);

            if ($subject = $instance->findServiceMethod($methodName, $serviceName)) {
                return $subject;
            }
        }

        return null;
    }
}
