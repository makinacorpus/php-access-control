<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

interface Expression
{
    /**
     * Execute expression.
     *
     * @return bool
     *   All expressions must return a boolean value, it's up to the
     *   implementation to decide how to convert it.
     */
    public function execute(array $arguments): bool;

    /**
     * Get required arguments.
     *
     * @return string
     *   Argument names found in expression that are supposed to be passed
     *   as arguments for executing.
     */
    public function getArguments(): array;

    /**
     * Display expression as string.
     */
    public function toString(): string;
}
