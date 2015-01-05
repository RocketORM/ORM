<?php

namespace Rocket\ORM\Test;

use Rocket\ORM\Generator\Config\ConfigLoader;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $configLoader = new ConfigLoader(__DIR__ . '/../../../../../rocket.yml');
        Rocket::setConfiguration($configLoader->all());
    }
}
