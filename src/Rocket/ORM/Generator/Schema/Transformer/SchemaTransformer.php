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
use Rocket\ORM\Generator\Utils\String;
use Rocket\ORM\Model\Map\TableMap;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaTransformer implements SchemaTransformerInterface
{
    /**
     * @var string
     */
    protected $modelNamespace;


    /**
     * @param string $modelNamespace
     */
    public function __construct($modelNamespace = '\\Rocket\\ORM\\Generator\\Schema\\Schema')
    {
        $defaultSchemaNamespace = '\\Rocket\\ORM\\Generator\\Schema\\Schema';
        if ($modelNamespace != $defaultSchemaNamespace) {
            $class = new \ReflectionClass($modelNamespace);
            if (!$class->isSubclassOf($defaultSchemaNamespace)) {
                throw new \InvalidArgumentException('The schema model must extend Rocket\ORM\Generator\Schema\Schema');
            }
        }

        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @param array  $schemaData The schema data
     * @param string $path       The absolute path to the schema file
     *
     * @return Schema
     */
    public function transform(array $schemaData, $path)
    {
        /** @var Schema $schema */
        $schema = new $this->modelNamespace($schemaData);

        // Escape anti slashes
        $schema->namespace = str_replace('\\\\', '\\', $schema->namespace);
        $schema->escapedNamespace = str_replace('\\', '\\\\', $schema->namespace);

        // Add or delete slashes
        // Add first slash if missing
        if (0 < strpos($schema->relativeDirectory, DIRECTORY_SEPARATOR)) {
            $schema->relativeDirectory = DIRECTORY_SEPARATOR . $schema->relativeDirectory;
        }

        // Delete last slash if exists
        if (DIRECTORY_SEPARATOR === $schema->relativeDirectory[strlen($schema->relativeDirectory) - 1]) {
            $schema->relativeDirectory = substr($schema->relativeDirectory, 0, -1);
        }

        // Delete the file in the path
        $pathParams = explode(DIRECTORY_SEPARATOR, $path);
        unset($pathParams[sizeof($pathParams) - 1]);

        $schema->absoluteDirectory = join(DIRECTORY_SEPARATOR, $pathParams) . $schema->relativeDirectory;

        $this->transformTables($schema->getTables());

        return $schema;
    }

    /**
     * @param array|Table[] $tables
     */
    public function transformTables(array $tables)
    {
        foreach ($tables as $table) {
            if (null == $table->phpName) {
                $table->phpName = String::camelize($table->name);
            }

            $this->transformColumns($table->getColumns());
            $this->transformPrimaryKeys($table->getColumns());
        }
    }

    /**
     * @param array|Column[] $columns
     */
    public function transformColumns(array $columns)
    {
        foreach ($columns as $column) {
            if (null == $column->phpName) {
                $column->phpName = String::camelize($column->name, false);
            }

            if (true === $column->isAutoIncrement) {
                $column->isPrimaryKey = true;
            }

            if (true === $column->isPrimaryKey) {
                $column->isRequired = true;
            }

            // Check, for enum type, if the default value exists in the values array
            if (TableMap::COLUMN_TYPE_ENUM === $column->type && null != $column->default
                && !in_array($column->default, $column->values)) {
                throw new InvalidConfigurationException(
                    'Invalid default value "' . $column->default . '" for enum column "' . $column->name . '" on table "' . $column->getTable()->name . '"'
                );
            }

            // TODO size should be greater than decimal if float/double
        }
    }

    /**
     * @param array|Column[] $columns
     */
    public function transformPrimaryKeys(array $columns)
    {
        foreach ($columns as $column) {
            if (true === $column->isPrimaryKey) {
                $column->getTable()->addPrimaryKey($column);
            }
        }
    }

    /**
     * @param Table $table
     * @param array $schemas
     *
     * @return array
     */
    public function transformRelations(Table $table, array $schemas)
    {
        $relations = $table->getRelations();
        foreach ($relations as $relation) {
            // Check if local column exists
            $localColumn = null;
            foreach ($table->getColumns() as $column) {
                if ($relation->local === $column->name) {
                    $localColumn = $column;
                }
            }

            if (null == $localColumn) {
                throw new InvalidConfigurationException('Invalid local column value "' . $relation->local . '" for relation "' . $relation->with . '"');
            }

            // Find the related relation in loaded schemas
            $relatedTable = $this->guessRelatedTable($relation->with, $schemas);
            $relation->with = $relatedTable->getSchema()->escapedNamespace . '\\\\' . $relatedTable->phpName;

            if (null == $relation->phpName) {
                $relation->phpName = $relatedTable->phpName;
            }

            // Check if foreign column exists
            $foreignColumn = false;
            foreach ($relatedTable->getColumns() as $column) {
                if ($relation->foreign === $column->name) {
                    $foreignColumn = $column;
                }
            }

            if (null == $foreignColumn) {
                throw new InvalidConfigurationException('Invalid foreign column value "' . $relation->foreign . '" for relation "' . $relation->with . '"');
            }

            // Relation type guessing
            if (!$localColumn->isPrimaryKey) {
                $relation->type = TableMap::RELATION_TYPE_ONE_TO_MANY;
            } elseif (!$foreignColumn->isPrimaryKey) {
                $relation->type = TableMap::RELATION_TYPE_MANY_TO_ONE;
                $relation->phpName = $this->pluralize($relation->phpName);
            } else {
                if (1 < $table->getPrimaryKeyCount()) {
                    $relation->type = TableMap::RELATION_TYPE_ONE_TO_MANY;
                } elseif (1 < $relatedTable->getPrimaryKeyCount()) {
                    $relation->type = TableMap::RELATION_TYPE_MANY_TO_ONE;
                    $relation->phpName = $this->pluralize($relation->phpName);
                } else {
                    $relation->type = TableMap::RELATION_TYPE_ONE_TO_ONE;
                }
            }

            // Then, create the relation for the related table if doesn't exist yet
            $this->createRelatedRelation($relation, $table, $relatedTable);
        }

        return $relations;
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
            $phpName = $this->pluralize($table->phpName);
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
        ]);

        if (!$relatedTable->hasRelation($relatedRelation->with)) {
            $relatedTable->addRelation($relatedRelation);
        }
    }

    /**
     * Pluralizes English noun.
     *
     * @param  string  $word english noun to pluralize
     *
     * @return string
     *
     * @throws \LogicException
     *
     * @see https://github.com/whiteoctober/RestBundle/blob/master/Pluralization/Pluralization.php
     */
    protected function pluralize($word)
    {
        $plurals = [
            '/(quiz)$/i'                => '\1zes',
            '/^(ox)$/i'                 => '\1en',
            '/([m|l])ouse$/i'           => '\1ice',
            '/(matr|vert|ind)ix|ex$/i'  => '\1ices',
            '/(x|ch|ss|sh)$/i'          => '\1es',
            '/([^aeiouy]|qu)ies$/i'     => '\1y',
            '/([^aeiouy]|qu)y$/i'       => '\1ies',
            '/(hive)$/i'                => '\1s',
            '/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',
            '/sis$/i'                   => 'ses',
            '/([ti])um$/i'              => '\1a',
            '/(buffal|tomat)o$/i'       => '\1oes',
            '/(bu)s$/i'                 => '\1ses',
            '/(alias|status)/i'         => '\1es',
            '/(octop|vir)us$/i'         => '\1i',
            '/(ax|test)is$/i'           => '\1es',
            '/s$/i'                     => 's',
            '/$/'                       => 's'
        ];

        $uncountables = [
            'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'
        ];

        $irregulars = [
            'person'  => 'people',
            'man'     => 'men',
            'child'   => 'children',
            'sex'     => 'sexes',
            'move'    => 'moves'
        ];

        $lowerCasedWord = strtolower($word);
        foreach ($uncountables as $uncountable) {
            if ($uncountable == substr($lowerCasedWord, (-1 * strlen($uncountable)))) {
                return $word;
            }
        }

        foreach ($irregulars as $plural => $singular) {
            if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
                return preg_replace('/(' . $plural . ')$/i', substr($arr[0], 0, 1) . substr($singular, 1), $word);
            }
        }

        foreach ($plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        throw new \LogicException('Unknown plural for word "' . $word . '"');
    }
}
