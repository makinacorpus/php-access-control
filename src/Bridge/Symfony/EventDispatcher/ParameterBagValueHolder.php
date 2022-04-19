<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher;

use MakinaCorpus\AccessControl\Expression\ValueHolder;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Listen on kernel event, when controller is found, in order to be able to
 * read its attributes and allow or disallow access to it.
 *
 * We listen on KernelEvents::CONTROLLER_ARGUMENTS event, once all arguments
 * have been computed so that they may serve in the future for attribute based
 * calculations.
 *
 * Please note that this class might need some love, it has been written very
 * quickly in order to put in place a proof of concept.
 */
final class ParameterBagValueHolder implements ValueHolder
{
    private ParameterBag $decorated;

    public function __construct(ParameterBag $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(string $propertyName): mixed
    {
        return $this->decorated->get($propertyName);
    }
}
