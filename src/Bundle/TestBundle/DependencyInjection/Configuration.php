<?php
declare(strict_types=1);

namespace App\Bundle\TestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('test');
        $builder->getRootNode()
            ->children()
                ->arrayNode('rates_provider')
                    ->children()
                        ->scalarNode('url')->defaultValue('https://data.fixer.io/api/latest')->end()
                        ->scalarNode('mapping')->defaultValue('[rates]')->end()
                        ->scalarNode('currency')->defaultValue('EUR')->end()
                        ->arrayNode('auth')->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('bins_provider')
                ->children()
                    ->scalarNode('url')->defaultValue('https://lookup.binlist.net')->end()
                    ->scalarNode('mapping')->defaultValue('[country][alpha2]')->end()
                    ->arrayNode('auth')->scalarPrototype()->end()
                ->end()
            ->end()
        ->end();

        return $builder;
    }
}