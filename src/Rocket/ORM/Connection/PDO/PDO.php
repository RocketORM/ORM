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

use Rocket\ORM\Connection\ConnectionFactoryInterface;
use Rocket\ORM\Connection\ConnectionInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class PDO extends \PDO implements ConnectionFactoryInterface, ConnectionInterface
{
    /**
     * @param array $config
     *
     * @return static
     */
    public static function create(array $config)
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ];

        if (isset($config['options'])) {
            // array_merge() cannot work here because the array keys are integer
            $options = $config['options'] + $options;
        }


        // No database in the dns configuration, it is handled by the schema itself
        $config['dsn'] = preg_replace('/dbname=[a-zA-Z0-9-_]+;?/', '', $config['dsn']);

        return new static($config['dsn'], $config['username'], $config['password'], $options);
    }

    /**
     * // TODO rename "isDatabaseExists"
     *
     * @param string $databaseName
     *
     * @return bool
     */
    public function isDatabaseCreated($databaseName)
    {
        $stmt = $this->query("SHOW DATABASES LIKE '" . $databaseName . "'");
        $stmt->execute();

        return 0 < $stmt->rowCount();
    }

    /**
     * @param string $databaseName
     *
     * @return bool
     */
    public function createDatabase($databaseName)
    {
        $stmt = $this->prepare('CREATE DATABASE IF NOT EXISTS ' . $databaseName);

        return $stmt->execute();
    }

    /**
     * @return string
     */
    public static function getDriver()
    {
        return 'mysql';
    }
}
