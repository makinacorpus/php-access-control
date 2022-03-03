<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\ServiceLocator;

interface ServiceLocator
{
    /**
     * Find service.
     *
     * @param string $methodName
     *   A method name, if $service is unspecified, it can be a fully qualified
     *   function name, such as `\strlen` for exemple (this is an exemple, do
     *   not use this function).
     * @param ?string $serviceName
     *   A service name, of course meaning of this value depends upon your
     *   framework and configuration, using Symfony it may be a container
     *   registered class name for example.
     *
     * @return null|callable
     *   Return a callable that can be called directly.
     *   Beware that this callable must be introspectable, otherwise we will
     *   no be able to proceed with argument mapping, you can just return a
     *   callable with variadic arguments for example.
     */
    public function findServiceMethod(string $methodName, ?string $serviceName): ?callable;
}
