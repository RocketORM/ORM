<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Transformer;

use Rocket\ORM\Generator\Schema\Column;
use Rocket\ORM\Generator\Schema\Relation;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Table;
use Rocket\ORM\Generator\Utils\StringUtil;
use Rocket\ORM\Model\Map\TableMap;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaRelationTransformer implements SchemaRelationTransformerInterface
{
    /**
     * @param Table $table
     * @param array $schemas
     *
     * @throws InvalidConfigurationException
     */
    public function transform(Table $table, array $schemas)
    {
        $relations = $table->getRelations();
        foreach ($relations as $relation) {
            // Check if local column exists
            $localColumn = $table->getColumn($relation->local);
            if (null == $localColumn) {
                throw new InvalidConfigurationException('Invalid local column value "' . $relation->local . '" for relation "' . $relation->with . '"');
            }

            // Find the related relation in loaded schemas
            $relatedTable = $this->guessRelatedTable($relation->with, $schemas);
            $oldWith = $relation->with;
            $relation->with = $relatedTable->getSchema()->escapedNamespace . '\\\\' . $relatedTable->phpName;

            if (null == $relation->phpName) {
                $relation->phpName = $relatedTable->phpName;
            }

            // Check if foreign column exists
            $foreignColumn = $relatedTable->getColumn($relation->foreign);
            if (null == $foreignColumn) {
                throw new InvalidConfigurationException('Invalid foreign column value "' . $relation->foreign . '" for relation "' . $oldWith . '"');
            }

            // TODO check if the local column type == foreign column type

            // Relation type guessing
            $this->guessRelationType($localColumn, $relatedTable, $relation);

            // Then, save the related table for check if the related relation has been created
            $relation->setRelatedTable($relatedTable);
        }
    }

    /**
     * @param Table $table
     */
    public function transformRelatedRelations(Table $table)
    {
        // Create all related relations that are not already created
        foreach ($table->getRelations() as $i => $relation) {
            if (null != $relation->getRelatedTable()) {
                $this->createRelatedRelation($relation, $table, $relation->getRelatedTable());
            }
        }
    }

    /**
     * Relations can be named in three ways :
     *  - my_table
     *  - database.my_table
     *  - Example\Model\MyModel
     *
     * In some case, there can be more than one relation called with the same name.
     *
     * @param string $with    The relation
     * @param array  $schemas All loaded schemas
     *
     * @throws InvalidConfigurationException
     *
     * @return Table
     */
    protected function guessRelatedTable($with, array $schemas)
    {
        $tables = [];
        /** @var Schema $schema */
        foreach ($schemas as $schema) {
            $tables = array_merge($tables, $schema->findTables($with));
        }

        if (!isset($tables[0])) {
            throw new InvalidConfigurationException('Invalid relation "' . $with . '"');
        }

        if (1 < sizeof($tables)) {
            throw new InvalidConfigurationException('Too much table for the relation "' . $with . '", prefix it with the database or use the object namespace');
        }

        return $tables[0];
    }

    /**
     * @param Column   $local
     * @param Table    $relatedTable
     * @param Relation $relation
     */
    protected function guessRelationType(Column $local, Table $relatedTable, Relation $relation)
    {
        if (!$local->isPrimaryKey) {
            $relation->type = TableMap::RELATION_TYPE_ONE_TO_MANY;
        } else {
            if (1 < $relation->getLocalTable()->getPrimaryKeyCount()) {
                $relation->type = TableMap::RELATION_TYPE_ONE_TO_MANY;
            } elseif (1 < $relatedTable->getPrimaryKeyCount()) {
                $relation->type = TableMap::RELATION_TYPE_MANY_TO_ONE;
                $relation->phpName = StringUtil::pluralize($relation->phpName);
            } else {
                $relation->type = TableMap::RELATION_TYPE_ONE_TO_ONE;
            }
        }
    }

    /**
     * @param Relation $relation
     * @param Table    $table
     * @param Table    $relatedTable
     */
    protected function createRelatedRelation(Relation $relation, Table $table, Table $relatedTable)
    {
        // Inverse relation type
        $phpName = $table->phpName;
        if (TableMap::RELATION_TYPE_MANY_TO_ONE == $relation->type) {
            $relatedType = TableMap::RELATION_TYPE_ONE_TO_MANY;
        } elseif (TableMap::RELATION_TYPE_ONE_TO_MANY == $relation->type) {
            $relatedType = TableMap::RELATION_TYPE_MANY_TO_ONE;
            $phpName = StringUtil::pluralize($table->phpName);
        } else {
            $relatedType = TableMap::RELATION_TYPE_ONE_TO_ONE;
        }

        $relatedRelation = new Relation($table->getSchema()->escapedNamespace . '\\\\' . $table->phpName, [
            'local'    => $relation->foreign,
            'foreign'  => $relation->local,
            'type'     => $relatedType,
            'phpName'  => $phpName,
            'onUpdate' => $relation->onUpdate,
            'onDelete' => $relation->onDelete,
        ], false);
        $relatedRelation->setLocalTable($relatedTable);
        $relatedRelation->setRelatedTable($table);

        if (!$relatedTable->hasRelation($relatedRelation->with)) {
            $relatedTable->addRelation($relatedRelation);
        }
    }
}
