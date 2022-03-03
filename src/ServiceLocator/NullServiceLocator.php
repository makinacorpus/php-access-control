<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ServiceLocator;

/**
 * @codeCoverageIgnore
 */
final class NullServiceLocator implements ServiceLocator
{
    /**
     * {@inheritdoc}
     */
    public function findServiceMethod(string $methodName, ?string $serviceName): ?callable
    {
        return null;
    }
}
