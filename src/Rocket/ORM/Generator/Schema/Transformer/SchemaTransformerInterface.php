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

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface SchemaTransformerInterface
{
    /**
     * @param array  $schema The schema data
     * @param string $path   The absolute path to the schema file
     *
     * @return array
     */
    public function transformRoot(array $schema, $path);

    /**
     * @param array $rawTables
     *
     * @return array
     */
    public function transformTables(array $rawTables);

    /**
     * @param array $rawColumns
     *
     * @return array
     */
    public function transformColumns(array $rawColumns);

    /**
     * @param array $columns
     *
     * @return array
     */
    public function transformPrimaryKeys(array $columns);

    /**
     * @param array $rawRelations
     * @param array $columns
     * @param array $schemas
     *
     * @return array
     */
    public function transformRelations(array $rawRelations, array $columns, array $schemas);
}
