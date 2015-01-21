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

use Rocket\ORM\Connection\Exception\ConnectionModeException;
use Rocket\ORM\Connection\Exception\ConnectionNotFoundException;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConnectionFactory
{
    const WRAPPER_DEFAULT_CLASS        = '\\Rocket\\ORM\\Connection\\PDO\\PDO';
    const WRAPPER_DEFAULT_SQLITE_CLASS = '\\Rocket\\ORM\\Connection\\PDO\\SQLitePDO';

    /**
     * @param array  $config
     * @param string $name
     * @param int    $mode
     *
     * @return ConnectionInterface
     *
     * @throws ConnectionNotFoundException
     * @throws ConnectionModeException
     */
    public static function create(array $config, $name, $mode)
    {
        if (!isset($config['connections'][$name])) {
            throw new ConnectionNotFoundException('The connection with name "' . $name . '" is not found in the configuration');
        } elseif (isset($config['connections'][$name]['mode']) && null != $config['connections'][$name]['mode'] && $mode != $config['connections'][$name]) {
            throw new ConnectionModeException('Trying to use connection named "' . $name . '" with the mode "'
                . (Rocket::CONNECTION_MODE_WRITE == $mode ? 'write' : 'read') . '", but got "' . $config['connections'][$name]['mode'] . '"')
            ;
        }

        if (isset($config['connections'][$name]['class']) && null != $config['connections'][$name]['class']) {
            $class = $config['connections'][$name]['class'];
        } elseif (isset($config['connection_class']) && null != $config['connection_class']) {
            $class = $config['connection_class'];
        } else {
            $class = self::WRAPPER_DEFAULT_CLASS;
            $dsn = $config['connections'][$name]['params']['dsn'];
            $driver = substr($dsn, 0, strpos($dsn, ':'));

            if ('sqlite' == $driver) {
                $class = self::WRAPPER_DEFAULT_SQLITE_CLASS;
            }
        }

        /** @var ConnectionInterface $class */
        $connection = $class::create($config['connections'][$name]['params']);

        return $connection;
    }
}
