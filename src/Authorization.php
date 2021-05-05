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
     * @param mixed ...$parameters
     *   Additional parameters, behavior at the discretion of policies.
     *   Those parameters are not named, and will be append in order to any
     *   method call involved with policies.
     */
    public function isGranted($resource, ...$parameters): bool;

    /**
     * Alias of isGranted() working on a class name and method name
     * instead of a single resource object.
     *
     * @param string|object $className
     * @param string $methodName
     *   Method name on the given class or object.
     * @param mixed ...$parameters
     *   Additional parameters, behavior at the discretion of policies.
     *   Those parameters are not named, and will be append in order to any
     *   method call involved with policies.
     */
    public function isMethodGranted($object, string $methodName, ...$parameters): bool;
}
