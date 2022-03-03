<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\PolicyLoader;

use MakinaCorpus\AccessControl\AccessPolicy;
use MakinaCorpus\AccessControl\Error\AccessConfigurationError;

final class AttributePolicyLoader implements PolicyLoader
{
    public function __construct()
    {
        if (PHP_VERSION_ID < 80000) {
            throw new AccessConfigurationError("Attribute policy loader can only work with PHP >= 8.0");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClass(string $className): iterable
    {
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError("Class does not exists: " . $className, 0, $e);
        }

        foreach ($reflectionClass->getAttributes() as $attribute) {
            if (\in_array(AccessPolicy::class, \class_implements($attribute->getName()))) {
                yield $attribute->newInstance();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClassMethod(string $className, string $methodName): iterable
    {
        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError(\sprintf("Class does not exist: %s", $className), 0, $e);
        }

        try {
            $reflectionMethod = $reflectionClass->getMethod($methodName);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError(\sprintf("Class method does not exist: %s::%s", $className, $methodName), 0, $e);
        }

        foreach ($reflectionMethod->getAttributes() as $attribute) {
            if (\in_array(AccessPolicy::class, \class_implements($attribute->getName()))) {
                yield $attribute->newInstance();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFunction(string $functionName): iterable
    {
        try {
            $reflectionFunction = new \ReflectionFunction($functionName);
        } catch (\ReflectionException $e) {
            throw new AccessConfigurationError("Class does not exists: " . $functionName, 0, $e);
        }

        foreach ($reflectionFunction->getAttributes() as $attribute) {
            if (\in_array(AccessPolicy::class, \class_implements($attribute->getName()))) {
                yield $attribute->newInstance();
            }
        }
    }
}
