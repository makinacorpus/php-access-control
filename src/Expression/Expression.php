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
     * @return ExpressionArgument[]
     */
    public function getArguments(): array;

    /**
     * From the given arbitrary key-value pairs (context values) convert
     * to an array whose keys are those from the arguments of this instance.
     *
     * Values from context which are not defined in arguments will be kept
     * in returned array, so that potential optional extra method parameters
     * will be kept and send anyway to the later executed method.
     */
    public function mapArgumentsFromContext(array $context): array;

    /**
     * Display expression as string.
     */
    public function toString(): string;
}
