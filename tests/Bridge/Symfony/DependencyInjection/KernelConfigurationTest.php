<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\AccessControlExtension;
use MakinaCorpus\AccessControl\PermissionChecker\ChainPermissionChecker;
use MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker;
use MakinaCorpus\AccessControl\PolicyLoader\ChainPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;
use MakinaCorpus\AccessControl\ResourceLocator\ChainResourceLocator;
use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;
use MakinaCorpus\AccessControl\RoleChecker\ChainRoleChecker;
use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;
use MakinaCorpus\AccessControl\ServiceLocator\ChainServiceLocator;
use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use MakinaCorpus\AccessControl\SubjectLocator\ChainSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class KernelConfigurationTest extends TestCase
{
    private function getContainer()
    {
        // Code inspired by the SncRedisBundle, all credits to its authors.
        return new ContainerBuilder(new ParameterBag([
            'kernel.debug'=> false,
            'kernel.bundles' => [],
            'kernel.cache_dir' => \sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => \dirname(__DIR__),
        ]));
    }

    private function getMinimalConfig(): array
    {
        return [
        ];
    }

    /**
     * Test default config for resulting tagged services
     */
    public function testTaggedServicesConfigLoad()
    {
        $extension = new AccessControlExtension();
        $config = $this->getMinimalConfig();
        $extension->load([$config], $container = $this->getContainer());

        self::assertTrue($container->hasAlias(Authorization::class));
        self::assertTrue($container->hasAlias(PermissionChecker::class));
        self::assertTrue($container->hasAlias(PolicyLoader::class));
        self::assertTrue($container->hasAlias(ResourceLocator::class));
        self::assertTrue($container->hasAlias(RoleChecker::class));
        self::assertTrue($container->hasAlias(ServiceLocator::class));
        self::assertTrue($container->hasAlias(SubjectLocator::class));
        self::assertTrue($container->hasDefinition(ChainPermissionChecker::class));
        self::assertTrue($container->hasDefinition(ChainPolicyLoader::class));
        self::assertTrue($container->hasDefinition(ChainResourceLocator::class));
        self::assertTrue($container->hasDefinition(ChainRoleChecker::class));
        self::assertTrue($container->hasDefinition(ChainServiceLocator::class));
        self::assertTrue($container->hasDefinition(ChainSubjectLocator::class));
        self::assertTrue($container->hasDefinition(DefaultAuthorization::class));
    }
}
