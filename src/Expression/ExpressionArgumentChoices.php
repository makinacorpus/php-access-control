<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;

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
final class ExpressionArgumentChoices
{
    private array $choices;

    public function __construct(array $choices)
    {
        $this->choices = \array_values($choices);
    }

    /**
     * Find any value in the choices that match one of the given types.
     *
     * @param string|string[] $type
     */
    public function find($types)
    {
        if (!$this->choices) {
            return null;
        }

        if (!$types) {
            return $this->choices[0];
        }

        $types = (array)$types;

        foreach ($types as $type) {
            if ('resource' === $type || 'callable' === $type) {
                return null; // Do not support resources or callables.
            }
            if ('mixed' === $type) {
                return $this->choices[0];
            }

            foreach ($this->choices as $choice) {
                if (\class_exists($type) || \interface_exists($type)) {
                    if (\is_object($choice) && $choice instanceof $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_bool($choice)) {
                    if ('bool' === $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_int($choice)) {
                    if ('int' === $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_float($choice)) {
                    if ('float' === $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_string($choice)) {
                    if ('string' === $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_array($choice)) {
                    if ('array' === $type) {
                        return $choice;
                    }
                    continue;
                }

                if (\is_iterable($choice)) {
                    if ('iterable' === $type || 'array' === $type) {
                        return $choice;
                    }
                    continue;
                }
            }
        }

        throw new AccessRuntimeError(\sprintf("Could not find value for given types '%s'", \implode(', ', $types)));
    }
}
