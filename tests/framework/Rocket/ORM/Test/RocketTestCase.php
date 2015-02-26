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
     *
     */
    public function __construct()
    {
        self::$rootDir = __DIR__ . '/../../../..';
    }

    /**
     *
     */
    public function setUp()
    {
        Rocket::setConfiguration(
            require self::$cacheDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'
        );
    }
}
