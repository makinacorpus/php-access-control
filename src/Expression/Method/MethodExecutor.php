<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\AccessConfigurationError;

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
     * @return mixed
     *   Whatever the method returned.
     */
    public function callFunction(string $functionName, array $parameters)
    {
        try {
            $reflectionFunction = new \ReflectionFunction($functionName);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError(\sprintf("'%s' function does not exist ", $functionName), 0, $e);
        }

        return $this->call(
            $functionName,
            $functionName,
            $reflectionFunction,
            $parameters
        );
    }

    /**
     * Find and call resource instance method.
     *
     * Method must be a method name on this object.
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
     * Find and call service method.
     *
     * Service can be either of:
     *   - an arbitrary registered name associated with an object instance,
     *   - a fully qualified class name associated with an object instance.
     *
     * Method must be a method name on this object.
     *
     * @return mixed
     *   Whatever the method returned.
     */
    public function callServiceMethod(string $service, string $methodName, array $parameters)
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * Validate incoming parameters and execute.
     */
    private function call(
        string $humanName,
        callable $function,
        \ReflectionFunctionAbstract $reflectionFunction,
        array $parameters
    ) {
        // Validate parameter types.
        $parameters = \array_values($parameters);
        $methodParameters = \array_values($reflectionFunction->getParameters());

        foreach ($methodParameters as $index => $reflectionParameter) {
            \assert($reflectionParameter instanceof \ReflectionParameter);

            if (isset($parameters[$index])) {
                // Check type compatibility.
                if ($reflectionParameter->hasType()) {
                    $reflectionType = $reflectionParameter->getType();

                    $typeMatch = false;
                    $inputType = $this->getType($parameters[$index]);
                    $expected = (string) $reflectionType;

                    if ($reflectionType instanceof \ReflectionUnionType) {
                        foreach ($reflectionType->getTypes() as $candidate) {
                            \assert($candidate instanceof \ReflectionNamedType);
                            if ($candidate == $inputType) {
                                $typeMatch = true;
                            }
                        }
                    } else if ($inputType === (string) $reflectionType) {
                        $typeMatch = true;
                    }
                    if (!$typeMatch) {
                        throw new AccessConfigurationError(\sprintf(
                            "Cannot call %s, type mismatch for parameter #%d (\$%s), expected '%s', '%s' given",
                            $humanName, $index + 1, $reflectionParameter->getName(), $expected, $inputType
                        ));
                    }
                }
            } elseif ($reflectionParameter->isDefaultValueAvailable()) {
                $parameters[$index] = $reflectionParameter->getDefaultValue();
            } else if ($reflectionParameter->allowsNull()) {
                $parameters[$index] = null;
            } else {
                throw new AccessConfigurationError(\sprintf(
                    "Cannot call %s, missing parameter #%d (\$%s)",
                    $humanName, $index + 1, $reflectionParameter->getName()
                ));
            }
        }

        return ($function)(...$parameters);
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
