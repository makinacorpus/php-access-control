<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

interface Authorization
{
    /**
     * Is this resource granted.
     *
     * @param mixed $resource
     *   If string, then consider it's a function name.
     *   If object, then attempt find policies names over it.
     * @param array $context
     *   Key-value pairs of contextual values. For a controller, for example,
     *   it will be its resolved arguments values with names.
     */
    public function isGranted($resource, array $context = []): bool;

    /**
     * Alias of isGranted() working on a class name and method name
     * instead of a single resource object.
     *
     * @param string|object $className
     * @param string $methodName
     *   Method name on the given class or object.
     * @param array $context
     *   Key-value pairs of contextual values. For a controller, for example,
     *   it will be its resolved arguments values with names.
     *
     * @deprecated
     *   Use isGranted() directly, you can pass an array [object, methodName].
     */
    public function isMethodGranted($object, string $methodName, array $context = []): bool;
}
