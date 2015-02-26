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
    protected $rootDir;

    /**
     * @var string
     */
    protected $cacheDir = TEST_CACHE_DIR;


    /**
     *
     */
    public function __construct()
    {
        $this->rootDir = __DIR__ . '/../../../..';
    }

    /**
     *
     */
    public function setUp()
    {
        Rocket::setConfiguration(
            require $this->cacheDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'
        );
    }
}
