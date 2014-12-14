<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM;

use Rocket\ORM\Connection\ConnectionFactory;
use Rocket\ORM\Connection\ConnectionInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class Rocket
{
    const VERSION = '0.1.0';

    const CONNECTION_MODE_WRITE = 0;
    const CONNECTION_MODE_READ  = 1;


    /**
     * @var array
     */
    protected static $config;

    /**
     * @var array
     */
    protected static $cons    = [];


    /**
     * @param string $name The connection name
     *
     * @return ConnectionInterface
     *
     * @throws Connection\Exception\ConnectionNotFoundException
     */
    public static function getConnection($name)
    {
        if (!isset(self::$cons[$name])) {
            self::$cons[$name] = ConnectionFactory::create(self::$config, $name);
        }

        return self::$cons[$name];
    }

    /**
     * @param array $config
     */
    public static function setConfiguration(array $config)
    {
        self::$config = $config;
    }
}
