<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Connection;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface ConnectionInterface
{
    const CONNECTION_MODE_WRITE = 'write';
    const CONNECTION_MODE_READ  = 'read';


    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function isDatabaseCreated($databaseName);

    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function createDatabase($databaseName);

    /**
     * @return string
     */
    public static function getDriver();
}
