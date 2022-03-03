<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression\Method;

use MakinaCorpus\AccessControl\Error\AccessRuntimeError;
use MakinaCorpus\AccessControl\Expression\Expression;
use MakinaCorpus\AccessControl\Expression\ExpressionArgument;
use MakinaCorpus\AccessControl\Expression\ValueAccessor;

final class MethodExpression implements Expression
{
    private string $methodName;
    private ?string $serviceName = null;
    /** @var ExpressionArgument */
    private array $arguments = [];

    public function __construct(string $methodName, ?string $serviceName = null, array $arguments = [])
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
        throw new \Exception("Implemente moi.");
    }

    /**
     * {@inheritdoc}
     */
    public function mapArgumentsFromContext(array $context): array
    {
        foreach ($this->arguments as $argument) {
            \assert($argument instanceof ExpressionArgument);

            if ($argument->context && \array_key_exists($argument->context, $context)) {
                if (\array_key_exists($argument->name, $context)) {
                    throw new AccessRuntimeError(\sprintf(
                        "Argument $%s cannot be overriden from context argument '%s'",
                        $argument->name,
                        $argument->context
                    ));
                }
                $context[$argument->name] = $context[$argument->context];
            }
        }

        if ($argument->property) {
            $object = $context[$argument->name];
            if (!\is_object($object)) {
                throw new AccessRuntimeError(\sprintf(
                    "Argument from context $%s is not an object, cannot fetch property '%s'",
                    $argument->name,
                    $argument->property
                ));
            }

            $context[$argument->name] = ValueAccessor::getValueFrom($object, $argument->property);
        }

        return $context;
    }

    /**
     * Get service name.
     */
    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    /**
     * Get service method name.
     */
    public function getServiceMethodName(): string
    {
        return $this->methodName;
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
            return \sprintf("%s.%s(%s)", $this->serviceName, $this->methodName, \implode(', ', $this->arguments));
        }
        return \sprintf("%s(%s)", $this->methodName, \implode(', ', $this->arguments));
    }
}
