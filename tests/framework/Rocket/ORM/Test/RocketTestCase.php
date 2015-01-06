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

use Rocket\ORM\Generator\Config\ConfigLoader;
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


    public function __construct()
    {
        $this->rootDir = __DIR__ . '/../../../../';
    }

    public function setUp()
    {
        $configLoader = new ConfigLoader(__DIR__ . '/../../../../../rocket.yml');
        Rocket::setConfiguration($configLoader->all());
    }
}
