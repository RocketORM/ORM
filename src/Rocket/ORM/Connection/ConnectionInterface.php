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
    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function isDatabaseCreated($databaseName);

    /**
     * @param string $databaseName
     *
     * @return void
     */
    public function createDatabase($databaseName);

    /**
     * @return string
     */
    public static function getDriver();
}
