<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test;

use Fixture\Car\Model\TableMap\CompanyTableMap;
use Rocket\ORM\Connection\ConnectionInterface;
use Rocket\ORM\Model\Map\TableMap;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Rocket
 */
class RocketTest extends RocketTestCase
{
    /**
     * @test
     */
    public function configuration()
    {
        // Reset cache
        $rocket = new Rocket();
        $reflection = new \ReflectionObject($rocket);
        $attribute = $reflection->getProperty('configCache');
        $attribute->setAccessible(true);
        $attribute->setValue([]);

        $this->assertEquals('car', Rocket::getConfiguration('default_connection'));
        $this->assertEquals('\Rocket\ORM\Model\Map\TableMap', Rocket::getConfiguration('model.table_map'));

        // Validate cache
        $cache = $attribute->getValue($rocket);

        $this->assertInternalType('array', $cache);
        $this->assertCount(2, $cache);
        $this->assertArrayHasKey('default_connection', $cache);
        $this->assertEquals('car', $cache['default_connection']);

        // From cache
        $this->assertEquals('car', Rocket::getConfiguration('default_connection'));

        $config = [
            'foo' => 'bar',
            'deep' => [
                'foobar' => 'barfoo'
            ]
        ];

        Rocket::setConfiguration($config);

        $this->assertEquals('bar', Rocket::getConfiguration('foo'));
        $this->assertEquals('barfoo', Rocket::getConfiguration('deep.foobar'));

        // Cache validation
        $cache = $attribute->getValue($rocket);

        $this->assertInternalType('array', $cache);
        $this->assertCount(2, $cache);
        $this->assertArrayNotHasKey('default_connection', $cache);
        $this->assertArrayHasKey('foo', $cache);
        $this->assertEquals('bar', $cache['foo']);
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Config\Exception\ConfigurationKeyNotFoundException
     * @expectedExceptionMessage No configuration found for the key "foo"
     *
     * @covers \Rocket\ORM\Config\Exception\ConfigurationKeyNotFoundException
     */
    public function getConfigurationNotFoundException()
    {
        Rocket::getConfiguration('foo');
    }

    /**
     * @test
     */
    public function getConnectionDriver()
    {
        $this->assertEquals('sqlite', Rocket::getConnectionDriver('car'));
        $this->assertEquals('sqlite', Rocket::getConnectionDriver());
    }

    /**
     * @test
     */
    public function getTableMap()
    {
        // Reset cache
        $rocket = new Rocket();
        $reflection = new \ReflectionObject($rocket);
        $attribute = $reflection->getProperty('tableMaps');
        $attribute->setAccessible(true);
        $attribute->setValue([]);

        $namespace = '\Fixture\Car\Model\Company';
        $tableMap = Rocket::getTableMap($namespace);
        $this->assertNotNull($tableMap);
        $this->assertTrue($tableMap instanceof TableMap);
        $this->assertTrue($tableMap instanceof CompanyTableMap);

        // Validate cache
        $cache = $attribute->getValue($rocket);

        $this->assertArrayHasKey($namespace, $cache);
        $this->assertEquals($tableMap, $cache[$namespace]);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The table map class "\Foo\TableMap\BarTableMap" does not exist
     */
    public function getTableMapClassNotFoundException()
    {
        Rocket::getTableMap('\Foo\Bar');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The "\Rocket\ORM\Test\Fixture\Car\TableMap\CompanyTableMap" table map must implement "\Rocket\Model\TableMap\TableMapInterface"
     */
    public function getTableMapWrongInstanceException()
    {
        Rocket::getTableMap('\Rocket\ORM\Test\Fixture\Car\Company');
    }

    /**
     * @test
     */
    public function getConnection()
    {
        // Reset cache
        $rocket = new Rocket();
        $reflection = new \ReflectionObject($rocket);
        $attribute = $reflection->getProperty('cons');
        $attribute->setAccessible(true);
        $attribute->setValue([]);

        $con = Rocket::getConnection();
        $this->assertNotNull($con);
        $this->assertTrue($con instanceof ConnectionInterface);

        // Validate cache
        $cache = $attribute->getValue($rocket);
        $this->assertCount(1, $cache);
        $this->assertArrayHasKey('car', $cache);
        $this->assertEquals($con, $cache['car']);

        // From cache with name
        $con2 = Rocket::getConnection('car');
        $this->assertNotNull($con2);
        $this->assertTrue($con2 instanceof ConnectionInterface);

        $this->assertEquals($con, $con2);
        $this->assertCount(1, $attribute->getValue($rocket));
    }
} 