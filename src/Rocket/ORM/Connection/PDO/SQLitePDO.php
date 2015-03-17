<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Connection\PDO;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SQLitePDO extends PDO
{
    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function isDatabaseCreated($databaseName)
    {
        // No need to create database in SQLite

        return true;
    }

    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function createDatabase($databaseName)
    {
        // No need to create database in SQLite

        return true;
    }

    /**
     * @return string
     */
    public static function getDriver()
    {
        return 'sqlite';
    }
}
