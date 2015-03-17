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
 * @covers \Rocket\ORM\Connection\PDO\PDO
 */
class PDOTest extends RocketTestCase
{
    /**
     * @var string
     */
    protected static $username;

    /**
     * @var string
     */
    protected static $password;


    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$username = 'root';
        if (null != getenv('DB_USERNAME')) {
            self::$username = getenv('DB_USERNAME');
        }

        self::$password = null;
        if (null != getenv('DB_PASSWORD')) {
            self::$password = getenv('DB_PASSWORD');
        }

        self::deleteDatabase();
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        self::deleteDatabase();

        parent::tearDownAfterClass();
    }

    /**
     * Delete database if exists
     */
    protected static function deleteDatabase()
    {
        $pdo = new \PDO('mysql:host=127.0.0.1', self::$username, self::$password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $pdo->exec('DELETE DATABASE `foo`');
    }

    /**
     * @test
     *
     * @group mysql
     *
     * @return PDO
     */
    public function create()
    {
        $pdo = PDO::create([
            'dsn'      => 'mysql:host=127.0.0.1',
            'username' => self::$username,
            'password' => self::$password
        ]);

        $this->assertNotNull($pdo);
        $this->assertTrue($pdo instanceof \PDO);
        $this->assertTrue($pdo instanceof PDO);
        $this->assertFalse($pdo instanceof SQLitePDO);
        unset($pdo);

        // With options
        $pdo = PDO::create([
            'dsn'      => 'mysql:host=127.0.0.1',
            'username' => self::$username,
            'password' => self::$password,
            'options'  => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_SILENT
            ]
        ]);

        $this->assertNotNull($pdo);
        $this->assertTrue($pdo instanceof \PDO);
        $this->assertTrue($pdo instanceof PDO);
        $this->assertFalse($pdo instanceof SQLitePDO);
        unset($pdo);

        // With database in DSN
        $pdo = PDO::create([
            'dsn'      => 'mysql:host=127.0.0.1;dbname=foo',
            'username' => self::$username,
            'password' => self::$password
        ]);

        $this->assertNotNull($pdo);
        $this->assertTrue($pdo instanceof \PDO);
        $this->assertTrue($pdo instanceof PDO);
        $this->assertFalse($pdo instanceof SQLitePDO);

        return $pdo;
    }

    /**
     * @test
     *
     * @depends create
     * @group mysql
     *
     * @param PDO $pdo
     */
    public function getDriver(PDO $pdo)
    {
        $this->assertEquals('mysql', $pdo->getDriver());
    }

    /**
     * @test
     *
     * @depends create
     * @group mysql
     *
     * @param PDO $pdo
     *
     * @return PDO
     */
    public function createDatabase(PDO $pdo)
    {
        $this->assertTrue($pdo->createDatabase('foo'));

        return $pdo;
    }

    /**
     * @test
     *
     * @depends createDatabase
     * @group mysql
     *
     * @param PDO $pdo
     */
    public function isDatabaseCreated(PDO $pdo)
    {
        $this->assertTrue($pdo->isDatabaseCreated('foo'));
    }
}
