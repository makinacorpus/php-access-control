<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony;

use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterPermissionCheckerPass;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterResourceLocatorPass;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterRoleCheckerPass;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterServiceLocatorPass;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterServicePass;
use MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection\Compiler\RegisterSubjectLocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @codeCoverageIgnore
 */
final class AccessControlBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterPermissionCheckerPass());
        $container->addCompilerPass(new RegisterResourceLocatorPass());
        $container->addCompilerPass(new RegisterRoleCheckerPass());
        $container->addCompilerPass(new RegisterServiceLocatorPass());
        $container->addCompilerPass(new RegisterServicePass());
        $container->addCompilerPass(new RegisterSubjectLocatorPass());
    }
}
