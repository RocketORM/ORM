<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaConfiguration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('schema');

        $rootNode
            ->fixXmlConfig('table')

            ->children()
                ->scalarNode('connection')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('directory')
                    ->defaultValue('/../Model')
                ->end()
                ->scalarNode('namespace')
                    ->isRequired()
                ->end()

                ->arrayNode('tables')
                    ->requiresAtLeastOneElement()
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('column')
                        ->children()

                            ->arrayNode('columns')
                                ->requiresAtLeastOneElement()
                                ->isRequired()
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('phpName')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('type')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('size')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('decimal')
                                            ->defaultNull()
                                        ->end()
                                        ->scalarNode('default')
                                            ->defaultNull()
                                        ->end()
                                        ->booleanNode('nullable')
                                            ->defaultTrue()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
