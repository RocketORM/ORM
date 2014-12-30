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

use Rocket\ORM\Generator\Utils\String;
use Rocket\ORM\Model\Map\TableMap;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaTransformer implements SchemaTransformerInterface
{
    /**
     * @param array  $schema The schema data
     * @param string $path   The absolute path to the schema file
     *
     * @return array
     */
    public function transformRoot(array $schema, $path)
    {
        $root = $schema;
        unset($root['tables']);

        // Escape anti slashes
        $root['namespace'] = str_replace('\\\\', '\\', $root['namespace']);
        $root['namespace'] = [
            'raw'     => $root['namespace'],
            'escaped' => str_replace('\\', '\\\\', $root['namespace'])
        ];

        // Add or delete slashes
        // Add first slash if missing
        if (0 < strpos($root['directory'], DIRECTORY_SEPARATOR)) {
            $root['directory'] = DIRECTORY_SEPARATOR . $root['directory'];
        }

        // Delete last slash if exists
        if (DIRECTORY_SEPARATOR === $root['directory'][strlen($root['directory']) - 1]) {
            $root['directory'] = substr($root['directory'], 0, -1);
        }

        // Delete the file in the path
        $pathParams = explode(DIRECTORY_SEPARATOR, $path);
        unset($pathParams[sizeof($pathParams) - 1]);

        $root['directory'] = join(DIRECTORY_SEPARATOR, $pathParams) . $root['directory'];

        return $root;
    }

    /**
     * @param array $rawTables
     *
     * @return array
     */
    public function transformTables(array $rawTables)
    {
        $tables = [];

        foreach ($rawTables as $tableName => $rawTable) {
            $table['name'] = $tableName;

            if (null != $rawTable['phpName']) {
                $table['phpName'] = $rawTable['phpName'];
            } else {
                $table['phpName'] = String::camelize($tableName);
            }

            $table['columns']     = $this->transformColumns($rawTable['columns']);
            $table['primaryKeys'] = $this->transformPrimaryKeys($table['columns']);
            $table['relations']   = $rawTable['relations']; // relations will be transformed when all schemas will be loaded

            $tables[] = $table;
            unset($table);
        }

        return $tables;
    }

    /**
     * @param array $rawColumns
     *
     * @return array
     */
    public function transformColumns(array $rawColumns)
    {
        $columns = [];
        foreach ($rawColumns as $columnName => $rawColumn) {
            $column = array_merge($rawColumn, [
                'name' => $columnName
            ]);

            if (null != $rawColumn['phpName']) {
                $column['phpName'] = $rawColumn['phpName'];
            } else {
                $column['phpName'] = String::camelize($columnName, false);
            }

            if (true === $column['autoincrement']) {
                $column['primaryKey'] = true;
            }

            if (true === $column['primaryKey']) {
                $column['required'] = true;
            }

            // Check, for enum type, if the default value exists in the values array
            if (TableMap::COLUMN_TYPE_ENUM === $column['type'] && null != $column['default']
                && !in_array($column['default'], $column['values'])) {
                throw new InvalidConfigurationException('Invalid default value "' . $column['default'] . '" for enum column "' . $column['name'] . '"');
            }

            // TODO size should be greater than decimal if float/double

            $columns[] = $column;
            unset($column);
        }

        return $columns;
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function transformPrimaryKeys(array $columns)
    {
        $primaryKeys = [];
        foreach ($columns as $column) {
            if (true === $column['primaryKey']) {
                $primaryKeys[] = [
                    'name'          => $column['name'],
                    'autoincrement' => $column['autoincrement']
                ];
            }
        }

        return $primaryKeys;
    }

    /**
     * @param array $rawRelations
     * @param array $columns
     * @param array $schemas
     *
     * @return array
     */
    public function transformRelations(array $rawRelations, array $columns, array $schemas)
    {
        $relations = [];

        foreach ($rawRelations as $with => $relation) {
            $relation['with'] = $with;

            // Check if local column exists
            $found = false;
            foreach ($columns as $column) {
                if ($relation['local'] === $column['name']) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new InvalidConfigurationException('Invalid local column value "' . $relation['local'] . '" for relation "' . $with . '"');
            }

            // Find the relation in loaded schemas
            $relatedSchema = $this->guessRelation($with, $schemas);
            $relation['with'] = $relatedSchema['root']['namespace']['escaped'] . '\\\\' . $relatedSchema['table']['phpName'];

            if (null == $relation['phpName']) {
                $relation['phpName'] = $relatedSchema['table']['phpName'];
            }

            // Check if foreign column exists
            $found = false;
            foreach ($relatedSchema['table']['columns'] as $column) {
                if ($relation['foreign'] === $column['name']) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new InvalidConfigurationException('Invalid foreign column value "' . $relation['foreign'] . '" for relation "' . $with . '"');
            }

            $relations[] = $relation;
            unset($relation);
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
     * @return array
     */
    protected function guessRelation($with, array $schemas)
    {
        if (false !== strpos($with, '\\')) {
            $guessedRelations = $this->getRelationByNamespace($with, $schemas);
        } else {
            if (false === strpos($with, '.')) {
                $guessedRelations = $this->getRelationsByTableName($with, $schemas);
            } else {
                $guessedRelations = $this->getRelationsByDatabaseAndTableName($with, $schemas);
            }
        }

        if (!isset($guessedRelations[0])) {
            throw new InvalidConfigurationException('Invalid relation "' . $with . '"');
        }

        if (1 < sizeof($guessedRelations)) {
            throw new InvalidConfigurationException('Too much relations for the value "' . $with . '", prefix it with the database or use the object namespace');
        }

        return $guessedRelations[0];
    }

    /**
     * @param string $with    A relation like "Example\Model\MyModel"
     * @param array  $schemas All loaded schemas
     *
     * @return array
     */
    protected function getRelationByNamespace($with, array $schemas)
    {
        $guessedRelations = [];
        foreach ($schemas as $schema) {
            foreach ($schema['tables'] as $table) {
                if ($with === sprintf('%s\\%s', $schema['root']['namespace']['raw'], $table['phpName'])) {
                    $guessedRelations[] = [
                        'root'  => $schema['root'],
                        'table' => $table
                    ];
                }
            }
        }

        return $guessedRelations;
    }

    /**
     * @param string $with    A relation like "my_table"
     * @param array  $schemas All loaded schemas
     *
     * @return array
     */
    protected function getRelationsByTableName($with, array $schemas)
    {
        $guessedRelations = [];
        foreach ($schemas as $schema) {
            foreach ($schema['tables'] as $table) {
                if ($with === $table['name']) {
                    $guessedRelations[] = [
                        'root'  => $schema['root'],
                        'table' => $table
                    ];
                }
            }
        }

        return $guessedRelations;
    }

    /**
     * @param string $with    A relation like "database.my_table"
     * @param array  $schemas All loaded schemas
     *
     * @return array
     */
    protected function getRelationsByDatabaseAndTableName($with, array $schemas)
    {
        $guessedRelations = [];
        foreach ($schemas as $schema) {
            foreach ($schema['tables'] as $table) {
                if ($with === sprintf('%s.%s', $schema['root']['database'], $table['name'])) {
                    $guessedRelations[] = [
                        'root'  => $schema['root'],
                        'table' => $table
                    ];
                }
            }
        }

        return $guessedRelations;
    }
}
