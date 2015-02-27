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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Rocket\ORM\Connection\ConnectionFactoryInterface;
use Rocket\ORM\Connection\ConnectionInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class PDO extends \PDO implements ConnectionFactoryInterface, ConnectionInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;


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
            $options = array_merge($options, $config['options']);
        }

        // No database in the dns configuration, it is handled by the schema itself
        $config['dsn'] = preg_replace('/dbname=(a-zA-Z0-9-_)+;?/', '', $config['dsn']);

        return new static($config['dsn'], $config['username'], $config['password'], $options);
    }

    /**
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
     * @return void
     */
    public function createDatabase($databaseName)
    {
        $stmt = $this->prepare('CREATE DATABASE IF NOT EXISTS ' . $databaseName);
        $stmt->execute();
    }

    /**
     * @return string
     */
    public static function getDriver()
    {
        return 'mysql';
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
