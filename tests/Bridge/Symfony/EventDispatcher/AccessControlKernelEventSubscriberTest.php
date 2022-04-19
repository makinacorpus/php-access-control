<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher\AccessControlKernelEventSubscriber;
use MakinaCorpus\AccessControl\PolicyLoader\AttributePolicyLoader;
use MakinaCorpus\AccessControl\Tests\Mock\FixedSubjectLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class AccessControlKernelEventSubscriberTest extends TestCase
{
    public function testWithControllerObject(): void
    {
        self::markTestSkipped();
    }

    public function testWithControllerString(): void
    {
        self::markTestSkipped();
    }

    public function testWithControllerArray(): void
    {
        self::markTestSkipped();
    }

    public function testArgumentsAreResolved(): void
    {
        $authorization = new DefaultAuthorization(
            new AttributePolicyLoader(),
            new FixedSubjectLocator(new \stdClass()),
            /* ?ResourceLocator $resourceLocator */ null,
            /* ?ServiceLocator $serviceLocator */ null, // This what we test here.
            /* ?PermissionChecker $permissionChecker */ null,
            /* ?RoleChecker $roleChecker */ null,
            false,
            true // WITH DEBUG
        );

        $subscriber = new AccessControlKernelEventSubscriber($authorization);

        $baz = new class () {
            /**
             * Method that will be called by AccessMethod
             */
            public function test(int $foo, string $bar, mixed $fizz, mixed $buzz)
            {
                // Tests that all values are propagated from value array below.
                // And also default values of controller for $fizz and $buzz.
                return 7 === $foo && 'bla' === $bar && $fizz === null && $buzz === 12;
            }
        };

        $subscriber->onKernelControllerArguments(
            $this->createControllerArgumentsEvent(
                [new ControllerWithArguments(), 'action'],
                [
                    7,
                    "bla",
                    $baz,
                    // Other arguments will be derived from the function default
                    // values. This is a god test, it tests everything at once.
                ]
            )
        );

        // No error means we are OK.
        self::assertTrue(true);

        self::expectException(AccessDeniedException::class);
        $subscriber->onKernelControllerArguments(
            $this->createControllerArgumentsEvent(
                [new ControllerWithArguments(), 'action'],
                [
                    11, // We change this one.
                    "bla",
                    $baz,
                ]
            )
        );
    }

    private function createControllerArgumentsEvent(mixed $controller, array $arguments): ControllerArgumentsEvent
    {
        return new ControllerArgumentsEvent(
            $this->createKernel(),
            $controller,
            $arguments,
            new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );
    }

    private function createKernel(): HttpKernelInterface
    {
        return new class () implements HttpKernelInterface {
            /**
             * {@inheritdoc}
             */
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                return new Response('OK');
            }
        };
    }
}

class ControllerWithArguments
{
    #[\MakinaCorpus\AccessControl\AccessMethod("baz.test(foo, baz, fizz, buzz)")]
    public function action(int $foo, string $bar, $baz, $fizz = null, $buzz = 12)
    {
    }
}

#[\MakinaCorpus\AccessControl\AccessMethod("baz.test(foo, baz, fizz, buzz)")]
function controller_arguments(int $foo, string $bar, $baz, $fizz = null, $buzz = 12)
{
}
