<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record\Map;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface TableMapInterface
{
    /**
     * Configure the table map
     *
     * @return void
     */
    public function configure();

    /**
     * @return string
     */
    public function getConnectionName();

    /**
     * @return string
     */
    public function getClassNamespace();

    /**
     * @return array
     */
    public function getPrimaryKeys();

    /**
     * @return array
     */
    public function getRelations();

    /**
     * @param string $columnName
     *
     * @return array
     */
    public function getColumn($columnName);

    /**
     * @param string $columnName
     *
     * @return bool
     */
    public function hasColumn($columnName);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRelation($name);

    /**
     * @param string $name
     *
     * @return array
     */
    public function getRelation($name);

    /**
     * @param array $row
     *
     * @return string
     */
    public function getPrimaryKeysHash(array $row);

    /**
     * @return array
     */
    public function getColumns();

    /**
     * @return string
     */
    public function getTableName();

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @return string
     */
    public function getDatabase();
}
