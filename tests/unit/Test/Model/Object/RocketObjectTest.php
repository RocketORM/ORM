<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model\Object;

use Fixture\Car\Model\Company;
use Rocket\ORM\Record\ArrayRecord;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Record\Object\ArrayRecord
 */
class RocketObjectTest extends RocketTestCase
{
    /**
     * @test
     */
    public function construct()
    {
        $object = new ArrayRecord([
            'id' => 1,
            'name' => 'Foo'
        ], '\Fixture\Car\Model\Company');

        $this->assertCount(2, $object);
        $this->assertArrayHasKey('id', $object);
        $this->assertArrayHasKey('name', $object);

        $this->assertEquals(1, $object['id']);
        $this->assertEquals('Foo', $object['name']);

        return $object;
    }

    /**
     * @test
     *
     * @depends construct
     *
     * @param \Rocket\ORM\Record\ArrayRecord $object
     */
    public function hydrate(\Rocket\ORM\Record\ArrayRecord $object)
    {
        $company = $object->hydrate();
        $this->assertNotNull($company);
        $this->assertTrue($company instanceof Company);
    }
}
