<?php

namespace AppVerk\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('app_verk_app_user', 'array')->children();

        $rootNode
            ->arrayNode('acl')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enabled')->defaultValue(false)->end()
                    ->scalarNode('redirect_path')->defaultValue(null)->end()
                ->end()
            ->end();

        $this->addEntitiesConfig($rootNode);

        return $treeBuilder;
    }

    private function addEntitiesConfig(NodeBuilder $rootNode)
    {
        $rootNode
            ->arrayNode('entities')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('user_class')->defaultValue('AppBundle\\Entity\\User')->cannotBeEmpty()->end()
            ->scalarNode('role_class')->defaultValue('AppBundle\\Entity\\Role')->cannotBeEmpty()->end()
            ->end()
            ->end();
    }
}
