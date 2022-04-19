<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

/**
 * Helper for fetching arbitrary data on objects.
 */
final class ValueAccessor
{
    public static function getValueFrom(object $object, string $propertyName)
    {
        if ($object instanceof ValueHolder) {
            return $object->getValue($propertyName);
        }
        if ($value = self::getValueFromProperty($object, $propertyName)) {
            return $value;
        }
        return self::getValueFromMethod($object, $propertyName);
    }

    private static function getValueFromProperty(object $object, string $propertyName)
    {
        try {
            $ref = new \ReflectionProperty($object, $propertyName);

            if ($ref->isPublic()) {
                return $object->{$propertyName};
            }

            return (\Closure::bind(
                fn ($victim) => $victim->{$propertyName},
                $object,
                \get_class($object)
            ))($object);

        } catch (\ReflectionException $e) {
            return null; // Property does not exist, fallback.
        }
    }

    private static function getValueFromMethod(object $object, string $methodName)
    {
        try {
            $ref = new \ReflectionMethod($object, $methodName);

            // We can call the method nly if all parameters are optional.
            foreach ($ref->getParameters() as $parameter) {
                if (!$parameter->isOptional()) {
                    return null;
                }
            }

            if ($ref->isPublic()) {
                return $object->{$methodName}();
            }

            return (\Closure::bind(
                fn ($victim) => $victim->{$methodName}(),
                $object,
                \get_class($object)
            ))($object);

        } catch (\ReflectionException $e) {
            return null; // Method does not exist, fallback.
        }
    }
}
