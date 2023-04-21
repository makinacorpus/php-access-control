<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

use MakinaCorpus\ArgumentResolver\Resolver\ValueChoices;

/**
 * List of values for a single argument that can match a given parameter.
 *
 * For example, a context might yield more than one subject types (framework
 * provided one, domain provided one, user with multiple identities), but the
 * service method may yield a single argument.
 *
 * This class serves the purposes of exposing that there are more than one
 * possibilities that can be used, and the implementation should pick one.
 */
class ExpressionArgumentChoices extends ValueChoices
{
}
