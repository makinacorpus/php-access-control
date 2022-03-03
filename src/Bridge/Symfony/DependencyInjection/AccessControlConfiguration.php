<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class AccessControlConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('access_control');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('attributes')
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('debug')
                    ->children()
                        ->booleanNode('enabled')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('symfony_security')
                    ->children()
                        ->arrayNode('user_subject_locator')
                            ->children()
                                ->booleanNode('enabled')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('user_role_checker')
                            ->children()
                                ->booleanNode('enabled')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
