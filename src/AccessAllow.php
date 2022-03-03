<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Always allow.
 *
 * Use this to mark a resource always visible.
 *
 * Usage:
 *   #[AccessAllow]
 */
#[\Attribute]
final class AccessAllow implements AccessPolicy
{
    private ?string $reason = null;

    public function __construct(?string $reason = null)
    {
        $this->reason = $reason;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return \sprintf('AccessAllow("%s")', $this->reason ?? "No reason");
    }
}
