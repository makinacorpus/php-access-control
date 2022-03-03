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
 *
 * @Annotation
 */
#[\Attribute]
final class AccessDeny implements AccessPolicy
{
    private ?string $reason = null;

    public function __construct($reason = null)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($reason)) {
            if (\is_array($reason['value'])) {
                $this->reason = $reason['value'][0];
            } else {
                $this->reason = $reason['value'];
            }
        } else {
            $this->reason = $reason;
        }
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
