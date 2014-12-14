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
use Rocket\ORM\Connection\ConnectionInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class PDO extends \PDO implements ConnectionInterface, LoggerAwareInterface
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

        return new static($config['dsn'], $config['username'], $config['password'], $options);
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
