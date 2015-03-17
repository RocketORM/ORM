<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Connection\PDO;

use Rocket\ORM\Connection\PDO\PDO;
use Rocket\ORM\Connection\PDO\SQLitePDO;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Connection\PDO\SQLitePDO
 */
class SQLitePDOTest extends RocketTestCase
{
    /**
     * @test
     *
     * @return SQLitePDO
     */
    public function create()
    {
        // With options
        $pdo = SQLitePDO::create([
            'dsn'      => 'sqlite::memory:',
            'username' => null,
            'password' => null,
            'options'  => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT
            ]
        ]);

        $this->assertNotNull($pdo);
        $this->assertTrue($pdo instanceof \PDO);
        $this->assertTrue($pdo instanceof PDO);
        $this->assertTrue($pdo instanceof SQLitePDO);
        unset($pdo);

        // Without options
        $pdo = SQLitePDO::create([
            'dsn'      => 'sqlite::memory:',
            'username' => null,
            'password' => null
        ]);

        $this->assertNotNull($pdo);
        $this->assertTrue($pdo instanceof \PDO);
        $this->assertTrue($pdo instanceof PDO);
        $this->assertTrue($pdo instanceof SQLitePDO);

        return $pdo;
    }

    /**
     * @test
     *
     * @depends create
     *
     * @param SQLitePDO $pdo
     */
    public function getDriver(SQLitePDO $pdo)
    {
        $this->assertEquals('sqlite', $pdo->getDriver());
    }

    /**
     * @test
     *
     * @depends create
     *
     * @param SQLitePDO $pdo
     *
     * @return SQLitePDO
     */
    public function createDatabase(SQLitePDO $pdo)
    {
        $this->assertTrue($pdo->createDatabase('foo'));

        return $pdo;
    }

    /**
     * @test
     *
     * @depends createDatabase
     *
     * @param SQLitePDO $pdo
     */
    public function isDatabaseCreated(SQLitePDO $pdo)
    {
        $this->assertTrue($pdo->isDatabaseCreated('foo'));
    }
}