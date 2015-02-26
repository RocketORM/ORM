<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model;

use Fixture\Car\Model\Car;
use Rocket\ORM\Test\Generator\Model\ModelTestHelper;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @coversNothing
 */
class ModelTest extends RocketTestCase
{
    use ModelTestHelper, SchemaTestHelper;

    /**
     * @test
     */
    public function methodValidation()
    {
        $car = new Car();
        $date = \DateTime::createFromFormat('Y-m-d', '2014-01-01');

        $this->assertTrue($car->setDoorCount(5) instanceof Car);
        $this->assertTrue($car->setPrice(19990.50) instanceof Car);
        $this->assertTrue($car->setWheelName('Wheel name') instanceof Car);
        $this->assertTrue($car->setReleasedAt($date) instanceof Car);

        $this->assertEquals(5, $car->getDoorCount());
        $this->assertEquals(19990.50, $car->getPrice());
        $this->assertEquals('Wheel name', $car->getWheelName());
        $this->assertTrue($car->getReleasedAt() instanceof \DateTime);
        $this->assertEquals($date->getTimestamp(), $car->getReleasedAt()->getTimestamp());

        // TODO relations validation
    }
}
