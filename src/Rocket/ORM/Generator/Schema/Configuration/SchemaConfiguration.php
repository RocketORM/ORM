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

use Rocket\ORM\Model\Map\TableMap;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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

        $this->addSchemaConfigurationNode($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    protected function addSchemaConfigurationNode(ArrayNodeDefinition $node)
    {
        $node
            ->fixXmlConfig('table')
            ->children()
                ->scalarNode('connection')
                    ->defaultValue('default')
                ->end()
                ->scalarNode('database')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('directory')
                    ->defaultValue('/../Model')
                ->end()
                ->scalarNode('namespace')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()

                ->append($this->getTableConfigurationNode())
            ->end()
        ;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getTableConfigurationNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('tables');
        $node
            ->requiresAtLeastOneElement()
            ->isRequired()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->fixXmlConfig('column')

            ->children()
                ->scalarNode('phpName')
                    ->defaultNull()
                ->end()
            ->end()

            ->append($this->getTableColumnConfigurationNode())
        ;

        return $node;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getTableColumnConfigurationNode()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('columns');
        $node
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
                        ->cannotBeEmpty()
                        ->beforeNormalization()
                        ->ifString()
                            ->then(function ($value) {
                                return TableMap::convertColumnTypeToConstant($value);
                            })
                        ->end()
                        ->validate()
                            ->ifNotInArray(range(1, 8))
                            ->thenInvalid('Invalid column type for value "%s"')
                        ->end()
                    ->end()
                    ->integerNode('size')
                        ->min(1)
                        ->max(255)
                        ->defaultNull()
                    ->end()
                    // TODO unsigned
                    ->integerNode('decimal')
                        ->min(1)
                        ->max(20)
                        ->defaultNull()
                    ->end()
                    ->scalarNode('default')
                        ->defaultNull()
                    ->end()
                    ->booleanNode('nullable')
                        ->defaultTrue()
                    ->end()
                    ->booleanNode('primaryKey')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('autoincrement')
                        ->defaultFalse()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
