<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

/**
 * Read-only interface for current security context.
 */
interface AuthorizationContext
{
    /**
     * Get current subject.
     *
     * Internally uses the subject locators instances.
     *
     * @param string $className
     *   Return only the matching subject.
     *
     * @return null|mixed
     *   Anything that was found.
     */
    public function getCurrentSubject(string $className);
}
