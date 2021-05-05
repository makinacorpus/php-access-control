<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Policy;

final class AttributePolicyLoader implements PolicyLoader
{
    /**
     * {@inheritdoc}
     */
    public function loadFromClass(string $className): iterable
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromClassMethod(string $className, string $methodName): iterable
    {
        throw new \Exception("Not implemented yet.");
    }

    /**
     * {@inheritdoc}
     */
    public function loadFromFunction(string $functionName): iterable
    {
        throw new \Exception("Not implemented yet.");
    }
}
