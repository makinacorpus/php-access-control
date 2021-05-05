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

@todo

# Permission Based Access Control (PBAC)

@todo

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

## Using service method

Let's dive into an exemple, assume you have a bus command:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

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
    public function canDoThat(UserInterface $subject, $command)
    {
        if ($command->issuer !== $subject->getUsername()) {
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

# Defining more than one attribute

When you define multiple attributes, checks will be done in order. Checks
will be behave as an OR condition, a single access check that allows will
allow everything.

For example:

```php
namespace MyVendor\MyApp\SomeBoundingContext\Command;

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

#[AccessAllOrNothing]
#[AccessRole("ROLE_ADMIN")]
#[AccessMethod("ThatService.canDoThat(subject, resource)")]
class DoThisOrThatCommand
{
}
```

In this case, all attributes need to say yes for it to pass.

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

## Subject permission checker

There is no generic permission based access checks in Symfony, so you will
need to implement your own.

Implementing permission based access checks is optional.

## Subject role checker

If you are using Symfony, roles will be transparently han^dled using the
`Security` component.

Implementing role based access checks is optional.

## Services methods

All you need is to implement services with methods, and register those
services into the `AuthorizationServiceRegistry`, this is transparent when
using the Symfony framework.

# Note about PHP 8 attributes

All attributes class can also be used as Doctrine annotations transparently.

When using it throught the Symfony bundle, annotations reader will be
properly configured if registered in the Symfony container.

# Todo list

 - Introduce an explicit context class for calling isGranted*() method.
 - When using the isGrantedMethod(), pass method arguments along with their
   names to the argument resolver, throught the context. This allows to
   transparently resolved controller action arguments as variables in
   access checks, for example.
 - Implement the service resolver.
 - Implement a custom policy handler interface, allowing applications
   using us to develop their own policies.
