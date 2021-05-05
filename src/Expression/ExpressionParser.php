<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

interface ExpressionParser
{
    /**
     * Parse, validate and compile an expression.
     */
    public function parse(string $expression): Expression;
}
