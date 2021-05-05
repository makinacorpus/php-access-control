<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Subject;

interface SubjectLocator
{
    /**
     * Find subject in context.
     *
     * @return mixed
     *   Subject can be literally anything, it depends upon your framework,
     *   execution context, and so on. We will just do a little bit of
     *   introspection magic to determine whether or not it's applicable to
     *   access methods.
     */
    public function findSubject();
}
