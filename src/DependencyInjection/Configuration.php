<?php

namespace Ruspanzer\LoggableBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ruspanzer_loggable');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('table_prefix')->defaultValue('ruspanzer')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
