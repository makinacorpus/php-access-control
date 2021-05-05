<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Policy;

use Doctrine\Common\Annotations\Reader;
use MakinaCorpus\AccessControl\AccessConfigurationError;
use MakinaCorpus\AccessControl\AccessPolicy;

final class AnnotationPolicyLoader implements PolicyLoader
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
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

        foreach ($this->reader->getClassAnnotations($reflectionClass) as $annotation) {
            if ($annotation instanceof AccessPolicy) {
                yield $annotation;
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

        foreach ($this->reader->getMethodAnnotations($reflectionMethod) as $annotation) {
            if ($annotation instanceof AccessPolicy) {
                yield $annotation;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFunction(string $functionName): iterable
    {
        throw new \Exception("Doctrine annotations do not support functions as annotation source.");
    }
}
