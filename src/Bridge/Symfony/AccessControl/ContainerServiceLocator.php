<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use Symfony\Component\DependencyInjection\ServiceLocator as SymfonyServiceLocator;

/**
 * Works with tag-registered services for locating their access methods.
 */
final class ContainerServiceLocator implements ServiceLocator
{
    private SymfonyServiceLocator $containerServiceLocator;

    public function __construct(SymfonyServiceLocator $containerServiceLocator)
    {
        $this->containerServiceLocator = $containerServiceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
    {
        if (!$serviceName) {
            return null;
        }
        if (!$this->containerServiceLocator->has($serviceName)) {
            return null;
        }

        $serviceInstance = $this->containerServiceLocator->get($serviceName);

        if (!\method_exists($serviceInstance, $methodName)) {
            return null;
        }

        return [$serviceInstance, $methodName];
    }
}
