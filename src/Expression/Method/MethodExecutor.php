<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\ArgumentResolver\ArgumentResolver;
use MakinaCorpus\ArgumentResolver\DefaultArgumentResolver;
use MakinaCorpus\ArgumentResolver\Context\ArrayResolverContext;
use MakinaCorpus\ArgumentResolver\Error\MissingArgumentError;

final class MethodExecutor
{
    private ArgumentResolver $argumentResolver;

    public function __construct(?ArgumentResolver $argumentResolver = null)
    {
        $this->argumentResolver = $argumentResolver ?? new DefaultArgumentResolver();
    }

    /**
     * Find and call function.
     *
     * It can be either of:
     *   - an arbitrary registered name associated with a callable, case in
     *     which the callable must be introspectable,
     *   - a root namespace function name,
     *   - a namespaced function name fully qualified name.
     *
     * @param array $parameters
     *   Key-value pairs of arguments to pass to function.
     *
     * @return mixed
     *   Whatever the method returned.
     */
    public function callCallback(callable $callback, array $parameters)
    {
        try {
            return ($callback)(
                ...$this->argumentResolver->getArguments(
                    $callback,
                    new ArrayResolverContext($parameters)
                )
            );
        } catch (MissingArgumentError $e) {
            throw new AccessRuntimeError($e->getMessage(), 0, $e);
        }
    }

    /**
     * Find and call resource instance method.
     *
     * Method must be a method name on this object.
     *
     * @param array $parameters
     *   Key-value pairs of arguments to pass to function.
     *
     * @return mixed
     *   Whatever the method returned.
     */
    public function callResourceMethod(object $object, string $methodName, array $parameters)
    {
        $callback = \Closure::fromCallable([$object, $methodName]);

        try {
            return ($callback)(
                ...$this->argumentResolver->getArguments(
                    $callback,
                    new ArrayResolverContext($parameters)
                )
            );
        } catch (MissingArgumentError $e) {
            throw new AccessRuntimeError($e->getMessage(), 0, $e);
        }
    }
}
