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
        $tablesNode = $builder->root('tables');
        $tablesNode
            ->requiresAtLeastOneElement()
            ->isRequired()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->fixXmlConfig('column')
            ->fixXmlConfig('relation')

            ->children()
                ->scalarNode('phpName')
                    ->defaultNull()
                ->end()
            ->end()
            ->children()
                ->scalarNode('type')
                    ->defaultValue('InnoDB')
                ->end()
            ->end()

            ->append($this->getTableColumnConfigurationNode())
            ->append($this->getTableRelationConfigurationNode())
        ;

        return $tablesNode;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getTableColumnConfigurationNode()
    {
        $builder = new TreeBuilder();
        $columnsNode = $builder->root('columns');
        $columnsNode
            ->requiresAtLeastOneElement()
            ->isRequired()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->fixXmlConfig('value')
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
                                return TableMap::convertColumnTypeToConstant($value); // @codeCoverageIgnore
                            })
                        ->end()
                        ->validate()
                            ->ifNotInArray(range(1, 9))
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
                    ->arrayNode('values')
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('required')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('primaryKey')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('autoIncrement')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('description')
                        ->defaultNull()
                    ->end()
                ->end()
            ->end()
        ;

        return $columnsNode;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected function getTableRelationConfigurationNode()
    {
        $onActionBehaviors = ['CASCADE', 'NO ACTION', 'RESTRICT', 'SET NULL', 'SET DEFAULT'];

        $builder = new TreeBuilder();
        $relationsNode = $builder->root('relations');
        $relationsNode
            ->useAttributeAsKey('with')
            ->prototype('array')
                ->children()
                    ->scalarNode('name')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('phpName')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('local')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('foreign')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('onUpdate')
                        ->defaultValue('RESTRICT')
                        ->validate()
                            ->ifNotInArray($onActionBehaviors)
                            ->thenInvalid('Invalid "onUpdate" behavior for value "%s", available : ' . join(', ', $onActionBehaviors))
                        ->end()
                    ->end()
                    ->scalarNode('onDelete')
                        ->defaultValue('RESTRICT')
                        ->validate()
                            ->ifNotInArray($onActionBehaviors)
                            ->thenInvalid('Invalid "onDelete" behavior for value "%s", available : ' . join(', ', $onActionBehaviors))
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $relationsNode;
    }
}
