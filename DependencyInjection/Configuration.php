<?php

namespace Survos\LocationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder $builder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('survos_location');

        $rootNode = $builder->getRootNode();
        $rootNode->children()
            ->scalarNode('db')
                ->isRequired()
                ->defaultValue('location.db')
            ->end()

            ->scalarNode('user_provider')
                ->isRequired()
                ->defaultValue('\App\Entity\User')
            ->end()
            ->arrayNode('bar')
                ->isRequired()
                ->scalarPrototype()
                    ->defaultValue([
                        'survos_location.ipsum',
                        'survos_location.lorem',
                    ])
                ->end()
            ->end()
            ->integerNode('integer_foo')
                ->isRequired()
                ->defaultValue(2)
                ->min(1)
            ->end()
            ->integerNode('integer_bar')
                ->isRequired()
                ->defaultValue(50)
                ->min(1)
            ->end()
            ->end();

        return $builder;
    }
}
