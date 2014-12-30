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
            $table['tableName'] = $tableName;

            if (null != $rawTable['phpName']) {
                $table['className'] = $rawTable['phpName'];
            } else {
                $table['className'] = String::camelize($tableName);
            }

            if (!isset($table['phpName']) || null == $table['phpName']) {
                $table['phpName'] = String::camelize($tableName);
            }

            $table['columns']     = $this->transformColumns($rawTable['columns']);
            $table['primaryKeys'] = $this->transformPrimaryKeys($table['columns']);
            $table['relations']   = $this->transformRelations($rawTable['relations'], $table['columns']);

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
            if (TableMap::COLUMN_TYPE_ENUM === $column['type'] && isset($column['default']) && null != $column['default']
                && !in_array($column['default'], $column['values'])) {
                throw new InvalidConfigurationException('Invalid default value ("' . $column['default'] . '") for enum column "' . $column['name'] . '"');
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
     *
     * @return array
     */
    public function transformRelations(array $rawRelations, array $columns)
    {
        $relations = [];

        foreach ($rawRelations as $with => $relation) {
            $relation['with'] = $with;

            if (!isset($relation['phpName']) || null == $relation['phpName']) {
                $relation['phpName'] = String::camelize($relation['with']);
            }

            // Check if local column value exists
            $found = false;
            foreach ($columns as $column) {
                if ($relation['local'] === $column['name']) {
                    $found = true;
                }
            }

            if (!$found) {
                throw new InvalidConfigurationException('Invalid local column value "' . $relation['local'] . '" for relation "' . $with . '"');
            }

            $relations[] = $relation;
            unset($relation);
        }

        return $relations;
    }
}
