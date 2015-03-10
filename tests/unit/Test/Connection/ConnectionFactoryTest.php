<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Connection;

use Rocket\ORM\Connection\ConnectionFactory;
use Rocket\ORM\Connection\ConnectionInterface;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Connection\ConnectionFactory
 */
class ConnectionFactoryTest extends RocketTestCase
{
    /**
     * @test
     */
    public function create()
    {
        $con = ConnectionFactory::create($this->getRocketConfiguration(), 'car', ConnectionInterface::CONNECTION_MODE_READ);

        $this->assertNotNull($con);
        $this->assertTrue($con instanceof ConnectionInterface);
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Connection\Exception\ConnectionNotFoundException
     * @expectedExceptionMessage The connection with name "foobar" is not found in the configuration
     */
    public function createNotFoundConnectionException()
    {
        ConnectionFactory::create($this->getRocketConfiguration(), 'foobar', ConnectionInterface::CONNECTION_MODE_READ);
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Connection\Exception\ConnectionModeException
     * @expectedExceptionMessage Trying to use connection named "car" with the mode "write", but got "read"
     */
    public function createWrongConnectionModeException()
    {
        $config = $this->getRocketConfiguration();
        $config['connections']['car']['mode'] = ConnectionInterface::CONNECTION_MODE_READ;

        ConnectionFactory::create($config, 'car', ConnectionInterface::CONNECTION_MODE_WRITE);
    }

    /**
     * @test
     */
    public function getClassNamespace()
    {
        $config = [
            'connections' => [
                'foo' => [
                    'params' => [
                        'dsn' => 'mysql:foo:'
                    ]
                ]
            ]
        ];

        $this->assertEquals(ConnectionFactory::WRAPPER_DEFAULT_CLASS, ConnectionFactory::getClassNamespace($config, 'foo'));

        $config['connections']['foo']['params']['dsn'] = 'sqlite::memory:';
        $this->assertEquals(ConnectionFactory::WRAPPER_DEFAULT_SQLITE_CLASS, ConnectionFactory::getClassNamespace($config, 'foo'));

        $config['connection_class'] = 'FooBar';
        $this->assertEquals('FooBar', ConnectionFactory::getClassNamespace($config, 'foo'));

        $config['connections']['foo']['class'] = 'BarFoo';
        $this->assertEquals('BarFoo', ConnectionFactory::getClassNamespace($config, 'foo'));
    }

    /**
     * @return array
     */
    protected function getRocketConfiguration()
    {
        $rocket = new Rocket();
        $reflection = new \ReflectionObject($rocket);
        $attribute = $reflection->getProperty('config');
        $attribute->setAccessible(true);

        return $attribute->getValue($rocket);
    }
}