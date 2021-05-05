<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Policy;

interface PolicyLoader
{
    /**
     * Load policies from class.
     *
     * @return \MakinaCorpus\AccessControl\AccessPolicy[]
     */
    public function loadFromClass(string $className): iterable;

    /**
     * Load policies from class method.
     *
     * @return \MakinaCorpus\AccessControl\AccessPolicy[]
     */
    public function loadFromClassMethod(string $className, string $methodName): iterable;

    /**
     * Load policies from function.
     *
     * @return \MakinaCorpus\AccessControl\AccessPolicy[]
     */
    public function loadFromFunction(string $functionName): iterable;
}
