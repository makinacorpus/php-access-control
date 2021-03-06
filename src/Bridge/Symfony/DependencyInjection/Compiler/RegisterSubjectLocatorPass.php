<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler;

use MakinaCorpus\AccessControl\SubjectLocator\ChainSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class RegisterSubjectLocatorPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chain = $container->getDefinition(ChainSubjectLocator::class);
        $services = [];

        foreach (\array_keys($container->findTaggedServiceIds('access_control.subject_locator', true)) as $id) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if (!$reflexion = $container->getReflectionClass($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" used for service "%s" cannot be found.', $class, $id));
            }
            if (!$reflexion->implementsInterface(SubjectLocator::class)) {
                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, SubjectLocator::class));
            }

            $services[] = new Reference($id);
        }

        $chain->setArgument(0, $services);
    }
}
