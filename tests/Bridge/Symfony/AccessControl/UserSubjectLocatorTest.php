<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\AccessControl;

use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserSubjectLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\User;

final class UserSubjectLocatorTest extends TestCase
{
    public function testWithUserContext(): void
    {
        if (\class_exists(InMemoryUser::class)) {
            $subject = new InMemoryUser('foo', null, ['ROLE_TESTING']);
        } else {
            $subject = new User('foo', null, ['ROLE_TESTING']);
        }

        $token = new PreAuthenticatedToken($subject, [], 'default');

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $container = new Container();
        $container->set('security.token_storage', $tokenStorage);

        $security = new Security($container);

        $instance = new UserSubjectLocator($security);

        self::assertSame($subject, self::toArray($instance->findSubject())[0]);
    }

    public function testWithoutUserContext(): void
    {
        $token = new NullToken();

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($token);

        $container = new Container();
        $container->set('security.token_storage', $tokenStorage);

        $security = new Security($container);

        $instance = new UserSubjectLocator($security);

        self::assertEmpty(self::toArray($instance->findSubject()));
    }

    private static function toArray(iterable $value): array
    {
        return \is_array($value) ? $value : \iterator_to_array($value);
    }
}
