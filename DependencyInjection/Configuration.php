<?php

namespace Devtrw\ParseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('devtrw_parse');

        $rootNode
            ->children()
            ->scalarNode('app_id')
            ->isRequired()
            ->end()
            ->scalarNode('master_key')
            ->defaultFalse()
            ->end()
            ->scalarNode('rest_key')
            ->isRequired()
            ->end()
            ->scalarNode('base_url')
            ->isRequired()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
