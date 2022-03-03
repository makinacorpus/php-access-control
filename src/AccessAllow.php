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
 *
 * @Annotation
 */
#[\Attribute]
final class AccessAllow implements AccessPolicy
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
        return \sprintf('AccessAllow("%s")', $this->reason ?? "No reason");
    }
}
