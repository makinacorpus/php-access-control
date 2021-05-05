<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\AccessControl\Role\ChainRoleChecker;
use MakinaCorpus\AccessControl\Role\RoleChecker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class RegisterRoleCheckerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chain = $container->getDefinition(ChainRoleChecker::class);
        $services = [];

        foreach (\array_keys($container->findTaggedServiceIds('access_control.role', true)) as $id) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if (!$reflexion = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$reflexion->implementsInterface(RoleChecker::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, RoleChecker::class));
            }

            $services[] = new Reference($id);
        }

        $chain->setArgument(0, $services);
    }
}
