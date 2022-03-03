<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\ContainerServiceLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class RegisterServicePass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $containerLocator = $container->getDefinition(ContainerServiceLocator::class);
        $services = [];

        foreach (\array_keys($container->findTaggedServiceIds('access_control.service', true)) as $id) {
            $definition = $container->getDefinition($id);

            $class = $definition->getClass();
            if (!$reflectionClass = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            $realClass = $reflectionClass->getName();

            // Register using the declared class.
            $services[$class] = new Reference($id);
            // Register using the service identifier (alias or interface or name).
            if ($id !== $class) {
                $services[$id] = new Reference($id);
            }
            // Register using the real class if different.
            if ($class !== $realClass) {
                $services[$realClass] = new Reference($id);
            }
            // Register using the local class name.
            $services[$reflectionClass->getShortName()] = new Reference($id);
        }

        $containerLocator->setArgument(0, ServiceLocatorTagPass::register($container, $services));
    }
}
