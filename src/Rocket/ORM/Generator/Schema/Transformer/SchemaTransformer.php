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

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaTransformer implements SchemaTransformerInterface
{
    /**
     * @param array $schema
     *
     * @return array
     */
    public function transformRoot(array $schema)
    {
        $root = $schema;
        unset($root['tables']);

        // Escape anti slashes
        $root['namespace'] = str_replace('\\\\', '\\', $root['namespace']);
        $root['namespace'] = str_replace('\\', '\\\\', $root['namespace']);

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

            $table['columns']     = $this->transformColumns($rawTable['columns']);
            $table['primaryKeys'] = $this->transformPrimaryKeys($table['columns']);
            //$table['relations']   = $this->transformRelations($rawTable['relations']);

            $tables[] = $table;
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

            $columns[] = $column;
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
                    'name'              => $column['name'],
                    'isAutoincremented' => $column['autoincrement']
                ];
            }
        }

        return $primaryKeys;
    }

    /**
     * @param array $rawRelations
     *
     * @return array
     */
    public function transformRelations(array $rawRelations)
    {
        $relations = [];

        // TODO not implemented yet

        return $relations;
    }
}
