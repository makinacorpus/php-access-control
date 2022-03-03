<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Always deny.
 *
 * This is not really useful other than for testing or disabling something.
 *
 * Usage:
 *   #[AccessDeny]
 */
#[\Attribute]
final class AccessDeny implements AccessPolicy
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
        return \sprintf('AccessDeny("%s")', $this->reason ?? "No reason");
    }
}
