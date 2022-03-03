<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

/**
 * Arguments in expressions can be named/aliased.
 *
 * For example, consider the following user method signature:
 *
 *    doAccessCheck(UserInterface $user, SomeEntity $entity): bool
 *
 * Then, you would have to inject "subject" and "resource" from the context,
 * for this, the policy allows you to write the expression as such:
 *
 *    #[AccessMethod(\doAccessCheck(user: subject, entity: resource))]
 *    class SomeEntity {}
 *
 * Which allows you to explicit parameter names, without which the method
 * executor will not be able to map correctly arguments to method argument
 * names.
 *
 * @internal
 */
final class ExpressionArgument
{
    /** Name of argument in user method ("user" for "resource" for example). */
    public string $name;
    /** Name of argument in context (eg. "resource", "subject", ...)  */
    public ?string $context;
    /** Name of property to access within the given value (if object). */
    public ?string $property;

    public function __construct(string $name, ?string $context = null, ?string $property = null)
    {
        $this->name = $name;
        $this->context = $context;
        $this->property = $property;
    }

    public function toString(): string
    {
        if ($this->property) {
            if ($this->context) {
                return \sprintf("%s: %s.%s", $this->name, $this->context, $this->property);
            }
            return \sprintf("%s.%s", $this->name, $this->property);
        } else if ($this->context) {
            return \sprintf("%s: %s", $this->name, $this->context);
        }
        return \sprintf("%s", $this->name);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
