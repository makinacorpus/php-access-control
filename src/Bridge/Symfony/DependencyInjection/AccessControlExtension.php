<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection;

use MakinaCorpus\AccessControl\Authorization;
use MakinaCorpus\AccessControl\Authorization\DefaultAuthorization;
use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserRoleChecker;
use MakinaCorpus\AccessControl\Bridge\Symfony\AccessControl\UserSubjectLocator;
use MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher\AccessControlKernelEventSubscriber;
use MakinaCorpus\AccessControl\Permission\ChainPermissionChecker;
use MakinaCorpus\AccessControl\Permission\PermissionChecker;
use MakinaCorpus\AccessControl\Policy\AnnotationPolicyLoader;
use MakinaCorpus\AccessControl\Policy\ChainPolicyLoader;
use MakinaCorpus\AccessControl\Policy\PolicyLoader;
use MakinaCorpus\AccessControl\Role\ChainRoleChecker;
use MakinaCorpus\AccessControl\Role\RoleChecker;
use MakinaCorpus\AccessControl\Subject\ChainSubjectLocator;
use MakinaCorpus\AccessControl\Subject\SubjectLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        // @todo Register attribute (if PHP 8) or annotation (if configured) loaders.
        // @todo Raise exception if none (API is pointless without).
        //    pour l'instant c'est hardcodé
        $annotationPolicyLoader = new Definition();
        $annotationPolicyLoader->setClass(AnnotationPolicyLoader::class);
        $annotationPolicyLoader->setArguments([
            new Reference('annotations.reader'),
        ]);
        $container->setDefinition(AnnotationPolicyLoader::class, $annotationPolicyLoader);

        $policyLoaderChain = new Definition();
        $policyLoaderChain->setClass(ChainPolicyLoader::class);
        $policyLoaderChain->setArguments([[
            new Reference(AnnotationPolicyLoader::class),
        ]]);
        $container->setDefinition(ChainPolicyLoader::class, $policyLoaderChain);
        $container->setAlias(PolicyLoader::class, ChainPolicyLoader::class);

        $subjectLocatorChain = new Definition();
        $subjectLocatorChain->setClass(ChainSubjectLocator::class);
        $subjectLocatorChain->setArguments([[]]); // Set by compiler pass.
        $container->setDefinition(ChainSubjectLocator::class, $subjectLocatorChain);
        $container->setAlias(SubjectLocator::class, ChainSubjectLocator::class);

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
            new Reference(PermissionChecker::class),
            new Reference(RoleChecker::class),
        ]);
        $container->setDefinition(DefaultAuthorization::class, $authorizationDefinition);
        $container->setAlias(Authorization::class, DefaultAuthorization::class);

        // @todo register this only if configuration requires it.
        $userSubjectLocator = new Definition();
        $userSubjectLocator->setClass(UserSubjectLocator::class);
        $userSubjectLocator->setArguments([new Reference('security.helper')]);
        $userSubjectLocator->addTag('access_control.subject');
        $container->setDefinition(UserSubjectLocator::class, $userSubjectLocator);

        // @todo register this only if configuration requires it.
        $userRoleChecker = new Definition();
        $userRoleChecker->setClass(UserRoleChecker::class);
        $userRoleChecker->addTag('access_control.role');
        $container->setDefinition(UserRoleChecker::class, $userRoleChecker);

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
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new AccessControlConfiguration();
    }
}
