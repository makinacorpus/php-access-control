<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl;

use MakinaCorpus\AccessControl\Expression\ValueAccessor;

/**
 * Tell the system how to load the corresponding resource.
 *
 * You need to give two pieces of information:
 *
 *   - The resource type as a string, whose form depend on the registed
 *     resource locators, there is no other constraints than your domain.
 *
 *   - The resource identifier, which is a property or method name that
 *     exists on the class on which you applied this attribute. The property
 *     or method may yield/return a value of any type (mixed type).
 *
 * Beware that the property name refers to a property from the object passed
 * to the Authorization::{isGranted,isMethodGranted)() $resource parameter
 * and not the $resourceType given to the AccessResource attribute constructor.
 *
 * Usage:
 *   #[AccessResource(SomeClass::class, "somePropertyName")]
 *
 * @Annotation
 */
#[\Attribute]
final class AccessResource implements AccessPolicy
{
    private string $resourceType;
    private string $resourceIdPropertyName;

    public function __construct($resourceType, $resourceIdPropertyName = null)
    {
        // Doctrine BC compat (is_array() call).
        if (\is_array($resourceType)) {
            if (\is_array($resourceType['value'])) {
                $this->resourceType = $resourceType['value'][0];
                $this->resourceIdPropertyName = $resourceType['value'][1] ?? null;
            } else {
                $this->resourceType = $resourceType['value'];
            }
        } else {
            $this->resourceType = $resourceType;
            $this->resourceIdPropertyName = $resourceIdPropertyName;
        }
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceIdPropertyName(): string
    {
        return $this->resourceIdPropertyName;
    }

    public function findResourceId(object $object)
    {
        return ValueAccessor::getValueFrom($object, $this->resourceIdPropertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return 'AccessResource(' . $this->resourceType . ', ' . ($this->resourceIdPropertyName ? $this->resourceIdPropertyName : 'null') . ')';
    }
}
