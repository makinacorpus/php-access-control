# Access-control micro-framework

This access-control micro-framework is based upon PHP attributes:
you may set access control attributes on any class or method you wish to apply
access control to.

Then it's your application responsability to call when necessary the
`AuthorizationChecker::isGranted()` method over the object which carries those
attributes. This means that once you choose to use this API, you will have to
implement when and where calling access checks, but not how.

This API provides a powerful yet simple to use attribute loader and parser,
and an authorization API you may call at any place, anywhere. What it does not
provide is an already configured or implemented decision points, this is your
project to decide where and when those access control checks must be done.

# Role Based Access Control (RBAC)

Role based access control is giving access to a resource when the subject has
a given role. This API is agnostic from the subject implementation, so role is
a discrete abstraction. In the API, a role is a simple text string.

```php
namespace MyVendor\MyApp\SomeBoundingContext\Entity;

use MakinaCorpus\AccessControl\AccessRole;

#[AccessRole("ROLE_USER")]
class FooEntity
{
    public function canDoThat(UserInterface $subject)
    {
        if ($this->owner !== $subject->getUsername()) {
            return false;
        }
        return true;
    }
}
```

You need to implement the `MakinaCorpus\AccessControl\RoleChecker\RoleChecker`
interface and register it for this work, for example:

```php
namespace MyVendor\MyApp\AccessControl;

use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;
use MyVendor\MyApp\Entity\SomeUserImplementation;

class MyRoleChecker implement RoleChecker
{
    public function subjectHasRole($subject, string $role): bool
    {
        return $subject instanceof SomeUserImplementation && $subject->hasRole($role);
    }
}
```

When using the Symfony bundle, a default `RoleChecker` implementation uses
Symfony's current user roles transparently.

# Permission Based Access Control (PBAC)

Role based access control is giving access to a resource when the subject has
a given permission. This API is agnostic from the subject implementation, so
permission is a discrete abstraction. In the API, a role is a simple text
string.

```php
namespace MyVendor\MyApp\SomeBoundingContext\Entity;

use MakinaCorpus\AccessControl\AccessPermission;

#[AccessPermission("do_that_with_foo")]
class FooEntity
{
    public function canDoThat(UserInterface $subject)
    {
        if ($this->owner !== $subject->getUsername()) {
            return false;
        }
        return true;
    }
}
```

You need to implement the `MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker`
interface and register it for this work, for example.

```php
namespace MyVendor\MyApp\AccessControl;

use MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker;
use MyVendor\MyApp\Entity\SomeUserImplementation;

class MyPermissionChecker implement PermissionChecker
{
    public function subjectHasPermission($subject, string $$permission): bool
    {
        return $subject instanceof SomeUserImplementation && $subject->hasPermission($permission);
    }
}
```

This is no default `PermissionChecker` implementation.

# Domain bound/driven access control

## Description

This access method is one of the most interesting one, this is where you will
be able to delegate the access check to an arbitrary object method, service
method, resource method, or global function.

The idea is that you implement an access check method in your domain in a
fashion it remains decoupled from the access check API, allowing you to
implement business related, context bound access control handlers.

## Using resource method

Let's dive into an exemple, assume you have a bus command:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Entity;

use MakinaCorpus\AccessControl\AccessMethod;

#[AccessMethod("canDoThat(subject)")]
class FooEntity
{
    public function canDoThat(UserInterface $subject)
    {
        if ($this->owner !== $subject->getUsername()) {
            return false;
        }
        return true;
    }
}
```

And that's pretty much it, then you need to call `Authorization::isGranted($yourEntity)`
whenever it fits with your input/output.

## Using context argument method

Method can be any context argument method, for example, consider the following
entity:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Entity;

class Product
{
    private int $quantityInStock;

    public function hasEnoughQuantity(int $needed): bool
    {
        return $this->quantityInStock > $needed;
    }
}
```

Then the following controller function (framework agnostic):

```php
namespace MyVendor\MyApp\SomeBoundingContext\Entity;

use MakinaCorpus\AccessControl\AccessMethod;

#[AccessMethod("product.hasEnoughQuantity(quantityRequired)")]
public function addToCart(Product $product, int $quantityRequired)
{
    // Do something.
}
```

In this example, both `product` and `quantityRequired` are controller
parameters, we are not working with a resource.

## Using service method

### Use-case

Let's dive into an exemple, assume you have a bus command:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

use MakinaCorpus\AccessControl\AccessService;

#[AccessService("ThatService.canDoThat(subject, resource)")]
class DoThisOrThatCommand
{
}
```

And implemented that service:

```php
namespace MyVendor\MyApp\SomeBoundingContext\AccessControl;

class ThatService
{
    public function canDoThat(UserInterface $subject, $resource)
    {
        if ($resource->issuer !== $subject->getUsername()) {
            return false;
        }
        return true;
    }
}
```

Then registered it into the access control component configuration (considering
in this sample that you are using the Symfony container):

```yaml
services:
    MyVendor\MyApp\SomeBoundingContext\AccessControl\ThatService:
        tags: ['access_control.method']
```

We consider in this exemple that you wrote such decorator for your bus, allowing
your code to effectively plugged over the access control API transparently (the
following code belongs to your infrastructure layer and is not domain bound):

```php
namespace MyVendor\MyApp\Bus;

use MakinaCorpus\AccessControl\Authorization;

class MyBusAccessDecorator implements MyBus
{
    private Authorization $authorization;
    private MyBus $decorated;

    public function dispatch(object $command): void
    {
        if (!$this->authorization->isGranted($command)) {
            throw new \Exception("YOU SHALL NOT PASS");
        }
        $this->decorated->dispatch($command);
    }
}
```

### Parameter explicit naming

If your method arguments are not the same as the context values, you can write
explicitly named arguments, as such:

```php
namespace MyVendor\MyApp\SomeBoundingContext\AccessControl;

class ThatService
{
    public function canDoThat(UserInterface $myBusinessUser, $myDomainEntity)
    {
        if ($myDomainEntity->issuer !== $myBusinessUser->getUsername()) {
            return false;
        }
        return true;
    }
}
```

Then:

```php
use MakinaCorpus\AccessControl\AccessService;

#[AccessService("ThatService.canDoThat(myBusinessUser: subject, myDomainEntity: resource)")]
class DoThisOrThatCommand
{
}
```

### Resource property as parameter

Now consider that you wanted to fetch a command property instead:

```php
namespace MyVendor\MyApp\SomeBoundingContext\AccessControl;

class ThatService
{
    public function canDoThat(UserInterface $myBusinessUser, $resource)
    {
        if ($someId !== $resource) {
            return false;
        }
        return true;
    }
}
```

Then:

```php
use MakinaCorpus\AccessControl\AccessService;

#[AccessService("ThatService.canDoThat(myBusinessUser: subject, myDomainEntity: resource.entityId)")]
class DoThisOrThatCommand
{
    public $entityId;
}
```

Note that may also fetch properties on any other object than the resource,
consider the following access method signature:

```php
namespace MyVendor\MyApp\SomeBoundingContext\AccessControl;

class ThatService
{
    public function canDoThat(string $userId, $resource)
    {
        if ($userId !== $resource->userId) {
            return false;
        }
        return true;
    }
}
```

Then you could combine with explicit parameter naming and write:

```php
use MakinaCorpus\AccessControl\AccessService;

#[AccessService("ThatService.canDoThat(userId: subject.id, resource)")]
class DoThisOrThatCommand
{
}
```

Property name (following the dot) can be either one of:

 - a public, protected or private property name,
 - a public, protected, private method name,
 - if a method, it must have no parameters, or only optional parameters.

If the property or method does not exist, `null` will be returned silently.

If the method cannot be called, an exception will be raised.

## In all cases

`Authorization` will:

 - Find the `AccessService` or `AccessMethod` attribute, and parse it.
 - With `AccessService`, it will search for the registered `ThatService`
   service, that should be an instance registered using the dependency injection
   container (details on how it gets where it is now is not important).
 - With `AccessMethod`, it will apply the rest of the algorith on a matching
   method found on the resource itself.
 - Gather the parameters passed to that method, derived from their respective
   names: `subject` means the logged-in user, `resource` the arbitrary object
   that was given to the `isGranted()` method.
 - If unrecognized parameters are given, it will fail, log and deny.
 - Use the `SubjectLocator` which is context-dependent to find the current
   runtime context-bound subject.
 - Check the `canDoThat()` method exists on `ThatService` or the resource
   and takes the given typed parameters corresponding to `subject` and
   `resource`.
 - Ensure that parameters that are about to be given to `canDoThat()` are
   type-compatible.
 - Call `$thatServiceInstance.canDoThat()` using the found `$subject` and the
   given `$resource`.
 - In our case, `$resource` being the command, then the command will be given
   to the service method.

The idea behind this implementation is to allow your domain code to remain
dependency-free about the access control framework, only exception being of
course the attributes declaration on your command. Yet, your access check
service will remain out-of-domain dependency-free.

In case of any error, such as parameters type mismatch, comprehensive errors
will be logged:

 - Always in a PSR-logger instance.
 - If in debug mode, exceptions will be  raised.
 - If in production mode, access will simply be denied.

If you don't pass a `"Service.method()"` string but rather a single method name
such as `"canDoThat()"` then the method must be either registered and identified
(it can be any callable that PHP supports) or exists as function.

You can also use function FQDN such as `MyVendor\SomeNamespace\foo()`.

# Resource locator

Consider that you are working in an application with a command bus and
wishes to do access checks on a dedicated resource which is not the
command itself.

Having the following entity class and dedicated repository in your
dependency injection container:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Model;

class SomeEntity
{
    public int $id;
    public string $name;
}

interface SomeEntityRepository
{
    /* @throws \DomainException */
    public function find(int $id): SomeEntity;
}
```

And the following command sent into the bus:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

class UpdateSomeEntity
{
    public int $entityId;
    public string $newName;
}
```

You probably want to check access at the bus level, but want to provide
the entity as being the resource on which the access policies will apply
and not the command itself.

Start by writing a resource locator, as such:

```php
namespace MyVendor\MyApp\SomeBoundingContext\ResourceLocator;

use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;
use MyVendor\MyApp\SomeBoundingContext\Model\SomeEntity;
use MyVendor\MyApp\SomeBoundingContext\Model\SomeEntityRepository;

class SomeResourceLocator implements ResourceLocator
{
    private SomeEntityRepository $repository;

    public function __construct(SomeEntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function loadResource(string $resourceType, $resourceId)
    {
        try {
            if (SomeEntity::class === $resourceType && \is_int($resourceId) {
                return $this->repository->find($resourceId);
            }
        } catch (\DomainException $e) {
            // Or let the exception pass, but it violates the contract.
        }
        return null;
    }
}
```

Then register it into the access control component configuration (considering
in this sample that you are using the Symfony container):

```yaml
services:
    MyVendor\MyApp\SomeBoundingContext\ResourceLocator\SomeResourceLocator:
        tags: ['access_control.resource_locator']
```

All you need for the authorization checker to find the correct resource for
access checks is to add the `AccessResource` attribute on your command:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

use MakinaCorpus\AccessControl\AccessResource;
use MakinaCorpus\AccessControl\AccessService;
use MyVendor\MyApp\SomeBoundingContext\Model\SomeEntity;

#[AccessResource(SomeEntity::class, "entityId")]
#[AccessService(ThatService.canDoThat(resource))]
class UpdateSomeEntity
{
    public int $entityId;
    // ... other properties.
}
```

This literally means: "fetch the `SomeClass` entity whose identifier can
be found in my `$entityId` property, then use the `ThatService.canDoThat()`
method passing the loaded entity as first parameter".

# Defining more than one attribute

When you define multiple attributes, checks will be done in order. Checks
will be behave as an OR condition, a single access check that allows will
allow everything.

For example:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

use MakinaCorpus\AccessControl\AccessMethod;
use MakinaCorpus\AccessControl\AccessRole;

#[AccessRole("ROLE_ADMIN")]
#[AccessMethod("ThatService.canDoThat(subject, resource)")]
class DoThisOrThatCommand
{
}
```

Will work if either one of the access control attribute says yes.

If you need to do an AND condition, you will need to explicit it using
the `AccessAllOrNothing()` attribute, such as:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

use MakinaCorpus\AccessControl\AccessAllOrNothing;
use MakinaCorpus\AccessControl\AccessMethod;
use MakinaCorpus\AccessControl\AccessRole;

#[AccessAllOrNothing]
#[AccessRole("ROLE_ADMIN")]
#[AccessMethod("ThatService.canDoThat(subject, resource)")]
class DoThisOrThatCommand
{
}
```

In this case, all attributes need to say yes for it to pass.

# Access delegation

Access delegation is a specific access policy that delegates the access checks
to another existing PHP class within the same project.

Consider you have the following bus command:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

use MakinaCorpus\AccessControl\AccessRole;

#[AccessRole("ROLE_ADMIN")]
class DoThisOrThatCommand
{
}
```

And you want to apply the same policy on a controller method:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Controller;

use MakinaCorpus\AccessControl\AccessDelegate;
use MyVendor\MyApp\SomeBoundingContext\Command\DoThisOrThatCommand

class SomeController
{
    #[AccessDelegate(DoThisOrThatCommand::class)]
    public function doThisOrDoThatAction(Request $request, /* ... */): Response
    {
    }
}
```

And you are good to do.

When using this, **the delegating object will be used as the resource**
**instead of the class you have delegated too**. In order to assess this
problem, use an explicit `AccessResource` attribute on the delegated
class to trigger the `ResourceLocator` resource loader.

# What this API is *not*

## Other non-implemented methods

A few well-known access control methods have not been, and probably will not
be implemented by this API:

 - Identity Based Access Control (IBAC): this framework aims to remain small
   and fast, and doesn't known nothing about the *subject* type or identity,
   because we don't know nothing about the *subject*, we cannot identify it.

 - Lattice Based Access Control (LBAC): it has never been a target since it's
   not very common to use those access control in application we commonly work
   on.

 - Attribute Based Access Control (ABAC): while it's a target we'd like to
   implement, it has very deep implication on entities/resources genericity
   that it would need to provide an efficient implementation of this model,
   for this reason, and because every project is different, we chose to not
   implement it for the time being. Note it can be efficiently replaced or
   implemented using the *Domain bound/driven access control*, if you really
   need it.

## Access-Control-List

Access-Control-List (ACL) are a vast topic that is not covered by this API,
although this API could be used as a front-end for an API system.

# What you need to implement to make it work

## Context-dependent subject locator

If you are using Symfony, the `Security` component will be used transparently
and will give you the current `UserInterface` if found. In the absence of
subject, access checks that requires it will fail and deny.

## Subject permission checker (optional)

There is no generic permission based access checks in Symfony, so you will
need to implement your own.

Implementing permission based access checks is optional.

## Resource locator (optional)

If you wish to use the resource loader and access resource attribute, you
need to implement your own resource locators.

Using the resource locator related attributes is optional.

## Subject role checker (optional)

If you are using Symfony, roles will be transparently handled using the
`Security` component.

Implementing role based access checks is optional.

## Services methods (optional)

For using service methods, you need to register your services into the
Symfony container, and tag them using the `access_control.service` tag.

Finding them will be delegated to the `ContainerServiceLocator` implementation.

# Symfony integration

This package provide a Symfony 5.x and 6.x integration.

## Setup

Enable it by adding to `config/bundles.php`:

```php

return [
    MakinaCorpus\AccessControl\Bridge\Symfony\AccessControlBundle::class => ['all' => true],
];
```

There is no configuration to be done.

## Integration with controller

All controller arguments will be available as context arguments for access
control policies, this is especially useful for `AccessMethod` and
`AccessService` policies. See below examples.

### Using an object argument method

You can use any controller argument object's method as the access control
method:

```php
namespace App\Controller;

use App\Entity\BlogPost;
use MakinaCorpus\AccessControl\AccessMethod;

class BlogPostController
{
    /**
     * Let's consider that you have an ArgumentValueResolver for the
     * BlogPost class here.
     */
    #[AccessMethod(post.isUserOwner(subject)]
    public function edit(BlogPost $post)
    {
    }
}
```

In this example, method access check will call the `BlogPost::isUserOwner()`
method on the `$post` instance controller argument, passing it the default
*subject*, ie. current logged in `UserInterface` if any.

### Using an argument as method parameter

```php
namespace App\Controller;

use App\Entity\BlogPost;
use MakinaCorpus\AccessControl\AccessMethod;

class BlogPostController
{
    /**
     * Let's consider that you have an ArgumentValueResolver for the
     * BlogPost class here.
     */
    #[AccessMethod(post.isTokenValid(token: accessToken)]
    public function view(BlogPost $post, string $accessToken)
    {
    }
}
```

In this example, method access check will call the `BlogPost::isTokenValid()`
method on the `$post` instance controller argument, passing it the `accessToken`
controller argument value, as being the `$token` named parameter of the
`isTokenValid()` method.

### Using request GET and POST parameters

Method expression don't support calling method with arguments on context
arguments yet, nevertheless, using an incomming request query parameter
is a common use case.

In order to work around this situation, when using an access policy over
a controller method, meta-arguments are provided by default:

 - `_get.PARAM_NAME`: will return the `PARAM_NAME` GET parameter value,
 - `_post.PARAM_NAME`: will return the `PARAM_NAME` POST parameter value,

Warning: those context arguments names can be shadowed by your controller
argument names.

### Using query POST parameters

@todo

### Symfony user is the default subject

If you don't specify a `subject` argument in your `AccessMethod` and
`AccessService` arguments, default one will always be the logged in Symfony's
`UserInterface` instance, if any.

# Note about PHP 8 attributes

All attributes class can also be used as Doctrine annotations transparently.

When using it throught the Symfony bundle, annotations reader will be
properly configured if registered in the Symfony container.
