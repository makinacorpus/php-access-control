<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\PolicyLoader;

final class ChainPolicyLoader implements PolicyLoader
{
    /** @var PolicyLoader[] */
    private iterable $instances;

    /** @param PolicyLoader[] $instances */
    public function __construct(iterable $instances)
    {
        $this->instances = $instances;
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClass(string $className): iterable
    {
        return (function () use ($className) {
            foreach ($this->instances as $instance) {
                \assert($instance instanceof PolicyLoader);
                yield from $instance->loadFromClass($className);
            }
        })();
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClassMethod(string $className, string $methodName): iterable
    {
        return (function () use ($className, $methodName) {
            foreach ($this->instances as $instance) {
                \assert($instance instanceof PolicyLoader);
                yield from $instance->loadFromClassMethod($className, $methodName);
            }
        })();
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFunction(callable $functionName): iterable
    {
        return (function () use ($functionName) {
            foreach ($this->instances as $instance) {
                \assert($instance instanceof PolicyLoader);
                yield from $instance->loadFromFunction($functionName);
            }
        })();
    }
}
