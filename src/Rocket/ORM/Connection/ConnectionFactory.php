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

use Rocket\ORM\Connection\Exception\ConnectionNotFoundException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConnectionFactory
{
    const WRAPPER_DEFAULT_CLASS = '\\Rocket\\ORM\\Connection\\Pdo\\Pdo';

    public static function create(array $config, $name)
    {
        if (!isset($config['connections'][$name])) {
            throw new ConnectionNotFoundException('The connection with name "' . $name . '" is not found in the configuration');
        }

        if (isset($config['connections'][$name]['class']) && null != $config['connections'][$name]['class']) {
            $class = $config['connections'][$name]['class'];
        } elseif (isset($config['connection_class']) && null != $config['connection_class']) {
            $class = $config['connection_class'];
        } else {
            $class = self::WRAPPER_DEFAULT_CLASS;
        }

        /** @var ConnectionInterface $connection */
        $connection = $class::create($config['connections'][$name]['params']);

        return $connection;
    }
}
