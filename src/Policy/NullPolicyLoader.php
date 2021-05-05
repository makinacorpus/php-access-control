<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Policy;

/**
 * @codeCoverageIgnore
 */
class NullPolicyLoader implements PolicyLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadFromClass(string $className): iterable
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClassMethod(string $className, string $methodName): iterable
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFunction(string $functionName): iterable
    {
        return [];
    }
}
