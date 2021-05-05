<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\Expression\Expression;

final class MethodExpression implements Expression
{
    private string $methodName;
    /** @var string[] */
    private array $arguments = [];
    private ?string $serviceName = null;

    public function __construct(string $methodName, array $arguments, ?string $serviceName = null)
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments;
        $this->serviceName = $serviceName;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $arguments): bool
    {
        // Passed arguments are a key-value hashmap whose keys are arguments
        // name, we need to re-order those to match the method call argument
        // names list.
        // @todo create and use a MethodExecutor here directly.
        $executor = new MethodExecutor();

        if ($this->serviceName) {
            // @todo et comment on récupère le service?
            // $executor->callServiceMethod($this->serviceName, $this->methodName, $arguments);
        }

        // @todo et sur quoi on appelle la méthode?
        // return $executor->callcaMethod($this->methodName, $arguments);

        throw new \Exception("Implemente moi.");
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        if ($this->serviceName) {
            return \sprintf("%s.%s(%s)", $this->serviceName, $this->methodName, \implode(', ', $this->parameters));
        }
        return \sprintf("%s(%s)", $this->methodName, \implode(', ', $this->parameters));
    }
}
