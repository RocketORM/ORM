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

use Rocket\ORM\Config\Exception\ConfigurationKeyNotFoundException;
use Rocket\ORM\Connection\ConnectionFactory;
use Rocket\ORM\Connection\ConnectionInterface;
use Rocket\ORM\Model\Map\TableMapInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class Rocket
{
    const VERSION = '0.1.0';


    /**
     * @var array
     */
    protected static $config;

    /**
     * @var array
     */
    protected static $configCache = [];

    /**
     * @var array
     */
    protected static $cons    = [];

    /**
     * @var array
     */
    protected static $tableMaps = [];


    /**
     * @param string $name The connection name
     * @param string $mode The connection mode, a Rocket class constant
     *
     * @return ConnectionInterface|\PDO
     *
     * @throws Connection\Exception\ConnectionNotFoundException
     */
    public static function getConnection($name = null, $mode = ConnectionInterface::CONNECTION_MODE_WRITE)
    {
        if (null == $name) {
            $name = self::getConfiguration('default_connection');
        }

        if (!isset(self::$cons[$name])) {
            self::$cons[$name] = ConnectionFactory::create(self::$config, $name, $mode);
        }

        return self::$cons[$name];
    }

    /**
     * @param null|string $name
     *
     * @return string
     */
    public static function getConnectionDriver($name = null)
    {
        if (null == $name) {
            $name = self::getConfiguration('default_connection');
        }

        /** @var ConnectionInterface $class */
        $class = ConnectionFactory::getClassNamespace(self::$config, $name);

        return $class::getDriver();
    }

    /**
     * @param array $config
     */
    public static function setConfiguration(array $config)
    {
        self::$config = $config;
        self::$configCache = [];
    }

    /**
     * @param string $key
     *
     * @return mixed
     *
     * @throws ConfigurationKeyNotFoundException
     */
    public static function getConfiguration($key)
    {
        if (isset(self::$configCache[$key])) {
            return self::$configCache[$key];
        }

        $parts = explode('.', $key);
        $config = self::$config;

        foreach ($parts as $part) {
            if (!array_key_exists($part, $config)) {
                throw new ConfigurationKeyNotFoundException('No configuration found for the key "' . $key . '"');
            }

            $config = $config[$part];
        }

        // Save to avoid next iteration
        self::$configCache[$key] = $config;

        return $config;
    }

    /**
     * @param string $classNamespace
     *
     * @return TableMapInterface
     *
     * @throws \RuntimeException
     */
    public static function getTableMap($classNamespace)
    {
        if (!isset(self::$tableMaps[$classNamespace])) {
            $namespaceParts = explode('\\', $classNamespace);
            $size = sizeof($namespaceParts);
            $className = $namespaceParts[$size - 1];

            unset($namespaceParts[$size - 1]);
            $tableMapNamespace = join('\\', $namespaceParts) . '\\TableMap\\' . $className . 'TableMap';

            if (!class_exists($tableMapNamespace)) {
                throw new \InvalidArgumentException('The table map class "' . $tableMapNamespace . '" does not exist');
            }

            $tableMap = new $tableMapNamespace();
            if (!$tableMap instanceof TableMapInterface) {
                throw new \InvalidArgumentException(
                    'The "' . $tableMapNamespace . '" table map must implement '
                    . '"\Rocket\Model\TableMap\TableMapInterface"'
                );
            }

            $tableMap->configure();
            self::$tableMaps[$classNamespace] = $tableMap;
        }

        return self::$tableMaps[$classNamespace];
    }
}
