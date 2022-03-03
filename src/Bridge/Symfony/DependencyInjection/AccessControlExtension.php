<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\AuthorizationContext;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\ContainerServiceLocator;
use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserRoleChecker;
use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserSubjectLocator;
use MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher\AccessControlKernelEventSubscriber;
use MakinaCorpus\AccessControl\PermissionChecker\ChainPermissionChecker;
use MakinaCorpus\AccessControl\PermissionChecker\PermissionChecker;
use MakinaCorpus\AccessControl\PolicyLoader\AnnotationPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\AttributePolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\ChainPolicyLoader;
use MakinaCorpus\AccessControl\PolicyLoader\PolicyLoader;
use MakinaCorpus\AccessControl\ResourceLocator\ChainResourceLocator;
use MakinaCorpus\AccessControl\ResourceLocator\ResourceLocator;
use MakinaCorpus\AccessControl\RoleChecker\ChainRoleChecker;
use MakinaCorpus\AccessControl\RoleChecker\RoleChecker;
use MakinaCorpus\AccessControl\ServiceLocator\ChainServiceLocator;
use MakinaCorpus\AccessControl\ServiceLocator\ServiceLocator;
use MakinaCorpus\AccessControl\SubjectLocator\ChainSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\MemoryCacheSubjectLocator;
use MakinaCorpus\AccessControl\SubjectLocator\SubjectLocator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

final class AccessControlExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $debugEnabled = false; // $config['debug']['enabled'] ?? new Parameter("kernel.debug");
        $policyLoaders = [];

        if ($config['attributes']['enabled'] ?? true) {
            if (PHP_VERSION_ID < 80000) {
                throw new InvalidArgumentException(\sprintf("access_control.attributes.enabled cannot be true with PHP < 8.0"));
            }
            $attributePolicyLoader = new Definition();
            $attributePolicyLoader->setClass(AttributePolicyLoader::class);
            $container->setDefinition(AttributePolicyLoader::class, $attributePolicyLoader);
            $policyLoaders[] = new Reference(AttributePolicyLoader::class);
        }

        if ($config['annotations']['enabled'] ?? false) {
            $annotationPolicyLoader = new Definition();
            $annotationPolicyLoader->setClass(AnnotationPolicyLoader::class);
            $annotationPolicyLoader->setArguments([
                new Reference('annotations.reader'),
            ]);
            $container->setDefinition(AnnotationPolicyLoader::class, $annotationPolicyLoader);
            $policyLoaders[] = new Reference(AnnotationPolicyLoader::class);
        }

        $policyLoaderChain = new Definition();
        $policyLoaderChain->setClass(ChainPolicyLoader::class);
        $policyLoaderChain->setArguments([$policyLoaders]);
        $container->setDefinition(ChainPolicyLoader::class, $policyLoaderChain);
        $container->setAlias(PolicyLoader::class, ChainPolicyLoader::class);

        $subjectLocatorChain = new Definition();
        $subjectLocatorChain->setClass(ChainSubjectLocator::class);
        $subjectLocatorChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainSubjectLocator::class, $subjectLocatorChain);

        $subjectLocatorCache = new Definition();
        $subjectLocatorCache->setClass(MemoryCacheSubjectLocator::class);
        $subjectLocatorCache->setArguments([new Reference(ChainSubjectLocator::class)]); // Set by compiler pass.
        $container->setDefinition(MemoryCacheSubjectLocator::class, $subjectLocatorCache);
        $container->setAlias(SubjectLocator::class, MemoryCacheSubjectLocator::class);

        $resourceLocatorChain = new Definition();
        $resourceLocatorChain->setClass(ChainResourceLocator::class);
        $resourceLocatorChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainResourceLocator::class, $resourceLocatorChain);
        $container->setAlias(ResourceLocator::class, ChainResourceLocator::class);

        $serviceLocatorChain = new Definition();
        $serviceLocatorChain->setClass(ChainServiceLocator::class);
        $serviceLocatorChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainServiceLocator::class, $serviceLocatorChain);
        $container->setAlias(ServiceLocator::class, ChainServiceLocator::class);

        $permissionCheckerChain = new Definition();
        $permissionCheckerChain->setClass(ChainPermissionChecker::class);
        $permissionCheckerChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainPermissionChecker::class, $permissionCheckerChain);
        $container->setAlias(PermissionChecker::class, ChainPermissionChecker::class);

        $roleCheckerChain = new Definition();
        $roleCheckerChain->setClass(ChainRoleChecker::class);
        $roleCheckerChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainRoleChecker::class, $roleCheckerChain);
        $container->setAlias(RoleChecker::class, ChainRoleChecker::class);

        $authorizationDefinition = new Definition();
        $authorizationDefinition->setClass(DefaultAuthorization::class);
        $authorizationDefinition->setArguments([
            new Reference(PolicyLoader::class),
            new Reference(SubjectLocator::class),
            new Reference(ResourceLocator::class),
            new Reference(ServiceLocator::class),
            new Reference(PermissionChecker::class),
            new Reference(RoleChecker::class),
            // @todo make this configurable
            false, // $denyIfNoPolicies = false
            $debugEnabled
        ]);
        $authorizationDefinition->addTag('profiling.profiler_aware');
        $container->setDefinition(DefaultAuthorization::class, $authorizationDefinition);
        $container->setAlias(Authorization::class, DefaultAuthorization::class);
        $container->setAlias(AuthorizationContext::class, DefaultAuthorization::class);

        // @todo register it conditionnally
        $authorizationDefinition->addMethodCall('setLogger', [new Reference('logger')]);
        $authorizationDefinition->addTag('monolog.logger', ['channel' => 'access_control']);

        // @todo register this only if configuration requires it.
        $containerServiceLocator = new Definition();
        $containerServiceLocator->setClass(ContainerServiceLocator::class);
        $containerServiceLocator->setArguments([]);
        $containerServiceLocator->addTag('access_control.service_locator');
        $container->setDefinition(ContainerServiceLocator::class, $containerServiceLocator);

        // @todo register this only if configuration requires it.
        if (false) {
            $userSubjectLocator = new Definition();
            $userSubjectLocator->setClass(UserSubjectLocator::class);
            $userSubjectLocator->setArguments([new Reference('security.helper')]);
            $userSubjectLocator->addTag('access_control.subject_locator');
            $container->setDefinition(UserSubjectLocator::class, $userSubjectLocator);
        }

        // @todo register this only if configuration requires it.
        if (false) {
            $userRoleChecker = new Definition();
            $userRoleChecker->setClass(UserRoleChecker::class);
            $userRoleChecker->addMethodCall('setRoleHierarchy', [new Reference('security.role_hierarchy')]);
            $userRoleChecker->addTag('access_control.role');
            $container->setDefinition(UserRoleChecker::class, $userRoleChecker);
        }

        // @todo register this only if configuration requires it.
        $kernelEventSubscriber = new Definition();
        $kernelEventSubscriber->setClass(AccessControlKernelEventSubscriber::class);
        $kernelEventSubscriber->setArguments([new Reference(Authorization::class)]);
        $kernelEventSubscriber->addTag('kernel.event_subscriber');
        $container->setDefinition(AccessControlKernelEventSubscriber::class, $kernelEventSubscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        return new AccessControlConfiguration();
    }
}
