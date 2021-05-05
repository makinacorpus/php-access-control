<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * All resource set policies must allow.
 *
 * Usage:
 *   #[AccessAllOrNothing]
 *
 * @Annotation
 */
#[Attribute]
final class AccessAllOrNothing implements AccessPolicy
{
}
