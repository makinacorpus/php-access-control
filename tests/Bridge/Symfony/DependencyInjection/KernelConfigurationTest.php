<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Tests\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\AccessControlExtension;
use MakinaCorpus\AccessControl\Permission\ChainPermissionChecker;
use MakinaCorpus\AccessControl\Permission\PermissionChecker;
use MakinaCorpus\AccessControl\Policy\ChainPolicyLoader;
use MakinaCorpus\AccessControl\Policy\PolicyLoader;
use MakinaCorpus\AccessControl\Role\ChainRoleChecker;
use MakinaCorpus\AccessControl\Role\RoleChecker;
use MakinaCorpus\AccessControl\Subject\ChainSubjectLocator;
use MakinaCorpus\AccessControl\Subject\SubjectLocator;
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

        self::assertTrue($container->hasAlias(PolicyLoader::class));
        self::assertTrue($container->hasAlias(Authorization::class));
        self::assertTrue($container->hasAlias(PermissionChecker::class));
        self::assertTrue($container->hasAlias(RoleChecker::class));
        self::assertTrue($container->hasAlias(SubjectLocator::class));
        self::assertTrue($container->hasDefinition(ChainPolicyLoader::class));
        self::assertTrue($container->hasDefinition(ChainPermissionChecker::class));
        self::assertTrue($container->hasDefinition(ChainRoleChecker::class));
        self::assertTrue($container->hasDefinition(ChainSubjectLocator::class));
        self::assertTrue($container->hasDefinition(DefaultAuthorization::class));
    }
}
