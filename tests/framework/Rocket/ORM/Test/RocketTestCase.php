<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test;

use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $rootDir;

    /**
     * @var string
     */
    protected static $cacheDir = TEST_CACHE_DIR;

    /**
     * @var array
     */
    private static $configurationCache;


    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$rootDir = __DIR__ . '/../../../..';
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        if (!isset(self::$configurationCache)) {
            self::$configurationCache = require self::$cacheDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        }

        Rocket::setConfiguration(self::$configurationCache);
    }
}
