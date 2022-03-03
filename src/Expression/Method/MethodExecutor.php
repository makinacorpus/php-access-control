<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessConfigurationError;
use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\ExpressionArgumentChoices;

final class MethodExecutor
{
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
        $callback = \Closure::fromCallable($callback);

        try {
            $reflectionFunction = new \ReflectionFunction($callback);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError("Could not introspect provided <callback>", 0, $e);
        }

        return $this->call(
            $reflectionFunction->getName() . '()',
            $callback,
            $reflectionFunction,
            $parameters
        );
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
        $className = \get_class($object);
        $humanName = $className . '::' . $methodName;

        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError(\sprintf("'%s' class does not exist", $className), 0, $e);
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError(\sprintf("'%s' method does not exist", $humanName), 0, $e);
        }

        if (!$reflectionMethod->isPublic()) {
            throw new AccessConfigurationError(\sprintf("'%s' must be public", $humanName));
        }
        if ($reflectionMethod->isAbstract()) {
            throw new AccessConfigurationError(\sprintf("'%s' must not be abstract", $humanName));
        }
        if ($reflectionMethod->isConstructor()) {
            throw new AccessConfigurationError(\sprintf("'%s' cannot be the constructor", $humanName));
        }

        return $this->call(
            $humanName,
            fn (...$parameters) => $object->{$methodName}(...$parameters),
            $reflectionMethod,
            $parameters
        );
    }

    /**
     * Validate incoming parameters and execute.
     *
     * @param array $parameters
     *   Key-value pairs of arguments to pass to function.
     */
    private function call(
        string $humanName,
        callable $function,
        \ReflectionFunctionAbstract $reflectionFunction,
        array $parameters
    ) {
        $args = [];
        foreach ($reflectionFunction->getParameters() as $reflectionParameter) {
            \assert($reflectionParameter instanceof \ReflectionParameter);

            $parameterName = $reflectionParameter->getName();
            $allowedTypes = $this->getAllowedTypes($reflectionParameter);

            if (\array_key_exists($parameterName, $parameters)) {
                $value = $parameters[$parameterName];
                if (!$allowedTypes) {
                    if ($value instanceof ExpressionArgumentChoices) {
                        $args[$parameterName] = $value->find(null);
                    } else {
                        $args[$parameterName] = $value;
                    }
                } else {
                    if ($value instanceof ExpressionArgumentChoices) {
                        try {
                            $args[$parameterName] = $value->find($allowedTypes);
                        } catch (AccessRuntimeError $e) {
                            throw new AccessRuntimeError(\sprintf(
                                "Cannot call %s, type mismatch for parameter \$%s, expected one of '%s', could not find any value in context.",
                                $humanName, $parameterName, \implode("', '", $allowedTypes)
                            ));
                        }
                    } else {
                        $found = false;
                        $valueType = $this->getType($value);
                        foreach ($allowedTypes as $allowedType) {
                            if ($valueType === $allowedType || \is_subclass_of($valueType, $allowedType)) {
                                $args[$parameterName] = $value;
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            throw new AccessRuntimeError(\sprintf(
                                "Cannot call %s, type mismatch for parameter \$%s, expected one of '%s', '%s' given.",
                                $humanName, $parameterName, \implode("', '", $allowedTypes), $valueType
                            ));
                        }
                    }
                }
            } else if ($reflectionParameter->isDefaultValueAvailable()) {
                $args[$parameterName] = $reflectionParameter->getDefaultValue();
            } else if ($reflectionParameter->allowsNull()) {
                $args[$parameterName] = null;
            } else {
                throw new AccessRuntimeError(\sprintf(
                    "Cannot call %s, missing parameter \$%s.",
                    $humanName, $parameterName
                ));
            }
        }

        return ($function)(...$args);
    }

    private function getAllowedTypes(\ReflectionParameter $reflectionParameter): array
    {
        if ($reflectionParameter->hasType()) {
            $reflectionType = $reflectionParameter->getType();

            if ($reflectionType instanceof \ReflectionUnionType) {
                $ret = [];
                foreach ($reflectionType->getTypes() as $candidate) {
                    \assert($candidate instanceof \ReflectionNamedType);
                    if ('mixed' === $candidate) {
                        return [];
                    }
                    $ret[] = $candidate->getName();
                }

                return $ret;

            } else {
                \assert($reflectionType instanceof \ReflectionNamedType);

                return [$reflectionType->getName()];
            }
        }

        return [];
    }

    /**
     * @codeCoverageIgnore
     */
    private function getType($value): string
    {
        switch ($type = \gettype($value)) {
            case 'double':
                return 'float';
            case 'integer':
                return 'int';
            case 'object':
                return \get_class($value);
            default:
                return $type;
        }
    }
}
