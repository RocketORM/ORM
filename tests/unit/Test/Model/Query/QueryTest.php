<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model\Query;

use Fixture\Car\Model\CarQuery;
use Fixture\Car\Model\Company;
use Fixture\Car\Model\CompanyQuery;
use Fixture\Car\Model\WheelQuery;
use Rocket\ORM\Model\Object\RocketObject;
use Rocket\ORM\Model\Query\Hydrator\QueryHydratorInterface;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\Fixture\Car\CompanyQueryTest;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Model\Query\Query
 * @covers \Rocket\ORM\Model\Query\SQLite\Query
 */
class QueryTest extends RocketTestCase
{
    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage The "Fixture\Car\Model\CompanyQuery" alias can be null
     */
    public function construct()
    {
        CompanyQuery::create(null);
    }

    /**
     * @test
     */
    public function where()
    {
        $query = CompanyQuery::create('c');

        $this->assertEquals($query, $query
            ->where('name = ?', 'Foo')
        );

        $expectedClauses = [
            [
                'clause'   => 'name = ?',
                'value'    => 'Foo',
                'operator' => 'AND'
            ]
        ];

        $this->assertProtectedAttributeEquals($expectedClauses, 'clauses', $query);

        $query->where('id = ?', 1);

        $expectedClauses[] = [
            'clause'   => 'id = ?',
            'value'    => 1,
            'operator' => 'AND'
        ];

        $this->assertProtectedAttributeEquals($expectedClauses, 'clauses', $query);
    }

    /**
     * @test
     *
     * @depends where
     */
    public function orWhere()
    {
        $query = CompanyQuery::create('c')
            ->where('name = ?', 'Foo')
        ;


        $this->assertEquals($query, $query->orWhere('id > ?', 1));

        $expectedClauses = [
            [
                'clause'   => 'name = ?',
                'value'    => 'Foo',
                'operator' => 'AND'
            ],
            [
                'clause'   => 'id > ?',
                'value'    => 1,
                'operator' => 'OR'
            ]
        ];

        $this->assertProtectedAttributeEquals($expectedClauses, 'clauses', $query);
    }

    /**
     * @test
     */
    public function limit()
    {
        $query = CompanyQuery::create('c');

        $this->assertEquals($query, $query->limit(10));
        $this->assertProtectedAttributeEquals(10, 'limit', $query);
        $this->assertEquals($query, $query->limit(5, 20));
        $this->assertProtectedAttributeEquals(5, 'limit', $query);
        $this->assertProtectedAttributeEquals(20, 'offset', $query);
    }

    /**
     * @test
     *
     * @depends where
     */
    public function getSqlQuery()
    {
        $query = CompanyQuery::create('c');

        $this->assertEquals(
            'SELECT c.id, c.name FROM `company` AS c',
            $query->getSqlQuery()
        );

        $this->assertEquals(
            "SELECT c.id, c.name FROM `company` AS c WHERE name = 'Foo'",
            $query
                ->where('name = ?', 'Foo')
                ->getSqlQuery()
        );

        // Should be reset
        $query = CompanyQuery::create('c');
        $this->assertEquals(
            'SELECT c.id, c.name FROM `company` AS c WHERE id = 1',
            $query
                ->where('id = ?', 1)
                ->getSqlQuery()
        );
    }

    /**
     * @test
     *
     * @depends getSqlQuery
     * @depends where
     * @depends limit
     */
    public function find()
    {
        $query = CompanyQueryTest::create('c');
        $con = Rocket::getConnection(Rocket::getTableMap(get_class(new Company()))->getConnectionName());

        foreach ([null, $con] as $connection) {
            $query
                ->limit(2)
            ;

            $this->assertEquals('SELECT c.id, c.name FROM `company` AS c LIMIT 2', $query->getSqlQuery());

            $companies = $query->find($connection);
            $this->assertNotNull($companies);
            $this->assertCount(2, $companies);
        }
    }

    /**
     * @test
     *
     * @depends limit
     * @depends where
     * @depends find
     */
    public function findOne()
    {
        $query = CompanyQueryTest::create('c');
        $con = Rocket::getConnection(Rocket::getTableMap(get_class(new Company()))->getConnectionName());

        foreach ([null, $con] as $connection) {
            $company = $query->findOne($connection);

            $this->assertNotNull($company);
            $this->assertInternalType('object', $company);
            $this->assertTrue($company instanceof RocketObject);
        }

        $company = $query
            ->where('name = ?', 'FooBarFooBar')
        ->findOne();

        $this->assertEquals(null, $company);
    }

    /**
     * @test
     *
     * @depends getSqlQuery
     * @depends find
     */
    public function innerJoinWith()
    {
        // With and without alias
        $this->assertEquals(
            'SELECT '
                . 'c.id, c.name, Wheels.unique_name AS "Wheels.unique_name", '
                . 'Wheels.score AS "Wheels.score", '
                . 'Wheels.company_id AS "Wheels.company_id" '
            . 'FROM `company` AS c '
            . 'INNER JOIN `wheel` Wheels ON c.id = Wheels.company_id '
            . 'WHERE name = \'Honda\'',
            CompanyQuery::create('c')
                ->innerJoinWith('Wheels')
                ->where('name = ?', 'Honda')
            ->getSqlQuery()
        );

        $this->assertEquals(
            'SELECT '
                . 'Company.id, Company.name, Wheels.unique_name AS "Wheels.unique_name", '
                . 'Wheels.score AS "Wheels.score", '
                . 'Wheels.company_id AS "Wheels.company_id" '
            . 'FROM `company` AS Company '
            . 'INNER JOIN `wheel` Wheels ON Company.id = Wheels.company_id '
            . 'WHERE name = \'Honda\'',
            CompanyQuery::create()
                ->innerJoinWith('Wheels')
                ->where('name = ?', 'Honda')
            ->getSqlQuery()
        );

        // Deep join with and without alias
        $this->assertEquals('SELECT '
                . 'c.id, c.name, Wheels.unique_name AS "Wheels.unique_name", '
                . 'Wheels.score AS "Wheels.score", '
                . 'Wheels.company_id AS "Wheels.company_id", cars.id AS "cars.id", '
                . 'cars.door_count AS "cars.door_count", '
                . 'cars.wheel_unique_name AS "cars.wheel_unique_name", '
                . 'cars.price AS "cars.price", cars.released_at AS "cars.released_at" '
            . 'FROM `company` AS c '
            . 'INNER JOIN `wheel` Wheels ON c.id = Wheels.company_id '
            . 'INNER JOIN `car` cars ON Wheels.unique_name = cars.wheel_unique_name '
            . 'WHERE name = \'Honda\'',
            CompanyQuery::create('c')
                ->innerJoinWith('Wheels')
                ->innerJoinWith('Wheels.Cars', 'cars')
                ->where('name = ?', 'Honda')
            ->getSqlQuery()
        );

        $this->assertEquals('SELECT '
                . 'Company.id, Company.name, Wheels.unique_name AS "Wheels.unique_name", '
                . 'Wheels.score AS "Wheels.score", '
                . 'Wheels.company_id AS "Wheels.company_id", cars.id AS "cars.id", '
                . 'cars.door_count AS "cars.door_count", '
                . 'cars.wheel_unique_name AS "cars.wheel_unique_name", '
                . 'cars.price AS "cars.price", cars.released_at AS "cars.released_at" '
            . 'FROM `company` AS Company '
            . 'INNER JOIN `wheel` Wheels ON Company.id = Wheels.company_id '
            . 'INNER JOIN `car` cars ON Wheels.unique_name = cars.wheel_unique_name '
            . 'WHERE name = \'Honda\'',
            CompanyQuery::create()
                ->innerJoinWith('Wheels')
                ->innerJoinWith('Wheels.Cars', 'cars')
                ->where('name = ?', 'Honda')
            ->getSqlQuery()
        );

        // Deep join (one) with and without alias
        $this->assertEquals('SELECT '
                . 'c.id, c.door_count, c.wheel_unique_name, c.price, c.released_at, '
                . 'w.unique_name AS "w.unique_name", w.score AS "w.score", '
                . 'w.company_id AS "w.company_id", Company.id AS "Company.id", '
                . 'Company.name AS "Company.name" '
            . 'FROM `car` AS c '
            . 'INNER JOIN `wheel` w ON c.wheel_unique_name = w.unique_name '
            . 'INNER JOIN `company` Company ON w.company_id = Company.id '
            . 'WHERE Company.name = \'Honda\'',
            CarQuery::create('c')
                ->innerJoinWith('Wheel', 'w')
                ->innerJoinWith('w.Company')
                ->where('Company.name = ?', 'Honda')
            ->getSqlQuery()
        );

        $this->assertEquals('SELECT '
                . 'Car.id, Car.door_count, Car.wheel_unique_name, Car.price, Car.released_at, '
                . 'w.unique_name AS "w.unique_name", w.score AS "w.score", '
                . 'w.company_id AS "w.company_id", Company.id AS "Company.id", '
                . 'Company.name AS "Company.name" '
            . 'FROM `car` AS Car '
            . 'INNER JOIN `wheel` w ON Car.wheel_unique_name = w.unique_name '
            . 'INNER JOIN `company` Company ON w.company_id = Company.id '
            . 'WHERE Company.name = \'Honda\'',
            CarQuery::create()
                ->innerJoinWith('Wheel', 'w')
                ->innerJoinWith('w.Company')
                ->where('Company.name = ?', 'Honda')
            ->getSqlQuery()
        );

        $companies = CompanyQueryTest::create('c')
            ->innerJoinWith('Wheels')
        ->find();

        $this->assertNotNull($companies);
    }

    /**
     * @test
     *
     * @depends innerJoinWith
     */
    public function leftJoinWith()
    {
        // With and without alias
        $this->assertEquals('SELECT '
                . 'c.id, c.door_count, c.wheel_unique_name, c.price, c.released_at, '
                . 'Wheel.unique_name AS "Wheel.unique_name", '
                . 'Wheel.score AS "Wheel.score", '
                . 'Wheel.company_id AS "Wheel.company_id" '
            . 'FROM `car` AS c '
            . 'LEFT JOIN `wheel` Wheel ON c.wheel_unique_name = Wheel.unique_name',
            CarQuery::create('c')
                ->leftJoinWith('Wheel')
            ->getSqlQuery()
        );

        $this->assertEquals('SELECT '
                . 'Car.id, Car.door_count, Car.wheel_unique_name, Car.price, Car.released_at, '
                . 'Wheel.unique_name AS "Wheel.unique_name", '
                . 'Wheel.score AS "Wheel.score", '
                . 'Wheel.company_id AS "Wheel.company_id" '
            . 'FROM `car` AS Car '
            . 'LEFT JOIN `wheel` Wheel ON Car.wheel_unique_name = Wheel.unique_name',
            CarQuery::create()
                ->leftJoinWith('Wheel')
            ->getSqlQuery()
        );

        // Deep join (many) with and without alias
        $this->assertEquals('SELECT '
                . 'w.unique_name, w.score, w.company_id, '
                . 'Cars.id AS "Cars.id", Cars.door_count AS "Cars.door_count", '
                . 'Cars.wheel_unique_name AS "Cars.wheel_unique_name", '
                . 'Cars.price AS "Cars.price", '
                . 'Cars.released_at AS "Cars.released_at", '
                . 'w2.unique_name AS "w2.unique_name", '
                . 'w2.score AS "w2.score", '
                . 'w2.company_id AS "w2.company_id", '
                . 'c.id AS "c.id", c.name AS "c.name" '
            . 'FROM `wheel` AS w '
            . 'LEFT JOIN `car` Cars ON w.unique_name = Cars.wheel_unique_name '
            . 'LEFT JOIN `wheel` w2 ON Cars.wheel_unique_name = w2.unique_name '
            . 'LEFT JOIN `company` c ON w2.company_id = c.id',
            WheelQuery::create('w')
                ->leftJoinWith('Cars')
                ->leftJoinWith('Cars.Wheel', 'w2')
                ->leftJoinWith('w2.Company', 'c')
            ->getSqlQuery()
        );

        $this->assertEquals('SELECT '
                . 'Wheel.unique_name, Wheel.score, Wheel.company_id, '
                . 'Cars.id AS "Cars.id", Cars.door_count AS "Cars.door_count", '
                . 'Cars.wheel_unique_name AS "Cars.wheel_unique_name", '
                . 'Cars.price AS "Cars.price", '
                . 'Cars.released_at AS "Cars.released_at", '
                . 'w2.unique_name AS "w2.unique_name", '
                . 'w2.score AS "w2.score", '
                . 'w2.company_id AS "w2.company_id", '
                . 'c.id AS "c.id", c.name AS "c.name" '
            . 'FROM `wheel` AS Wheel '
            . 'LEFT JOIN `car` Cars ON Wheel.unique_name = Cars.wheel_unique_name '
            . 'LEFT JOIN `wheel` w2 ON Cars.wheel_unique_name = w2.unique_name '
            . 'LEFT JOIN `company` c ON w2.company_id = c.id',
            WheelQuery::create()
                ->leftJoinWith('Cars')
                ->leftJoinWith('Cars.Wheel', 'w2')
                ->leftJoinWith('w2.Company', 'c')
            ->getSqlQuery()
        );
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Model\Query\Exception\RelationNotFoundException
     * @expectedExceptionMessage Unknown relation with "FooBar" for model "\Fixture\Car\Model\Company"
     *
     * @covers \Rocket\ORM\Model\Query\Exception\RelationNotFoundException
     */
    public function joinRelationNotFoundException()
    {
        CompanyQuery::create('c')
            ->innerJoinWith('FooBar')
        ->find();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Model\Query\Exception\RelationAliasNotFoundException
     * @expectedExceptionMessage Unknown alias for relation "FooBar" for model "\Fixture\Car\Model\Company"
     *
     * @covers \Rocket\ORM\Model\Query\Exception\RelationAliasNotFoundException
     */
    public function joinDeepRelationAliasNotFoundException()
    {
        CompanyQuery::create('c')
            ->innerJoinWith('Wheels', 'w')
            ->innerJoinWith('FooBar.Company', 'comp')
        ->find();
    }

    /**
     * @test
     *
     * @depends getSqlQuery
     * @depends where
     */
    public function buildClauses()
    {
        // Clause generation validation where there is a value or not
        $this->assertEquals("SELECT c.id, c.name FROM `company` AS c WHERE name = 'BMW' AND id IS NOT NULL", CompanyQuery::create('c')
            ->where('name = ?', 'BMW')
            ->where('id IS NOT NULL')
        ->getSqlQuery());

        $this->assertEquals("SELECT c.id, c.name FROM `company` AS c WHERE id IS NOT NULL AND name = 'BMW'", CompanyQuery::create('c')
            ->where('id IS NOT NULL')
            ->where('name = ?', 'BMW')
        ->getSqlQuery());
    }

    /**
     * @test
     *
     * @depends getSqlQuery
     * @depends limit
     */
    public function buildLimit()
    {
        $this->assertEquals("SELECT c.id, c.name FROM `company` AS c LIMIT 1", CompanyQuery::create('c')
            ->limit(1)
        ->getSqlQuery());

        $this->assertEquals("SELECT c.id, c.name FROM `company` AS c LIMIT 1,2", CompanyQuery::create('c')
            ->limit(1, 2)
        ->getSqlQuery());
    }

    /**
     * @test
     */
    public function getSimpleQueryHydrator()
    {
        $query = CompanyQuery::create('c');
        $reflection = new \ReflectionObject($query);
        $method = $reflection->getMethod('getSimpleQueryHydrator');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($query) instanceof QueryHydratorInterface);
    }

    /**
     * @test
     */
    public function getComplexQueryHydrator()
    {
        $query = CompanyQuery::create('c');
        $reflection = new \ReflectionObject($query);
        $method = $reflection->getMethod('getComplexQueryHydrator');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($query) instanceof QueryHydratorInterface);
    }

    /**
     * @param mixed        $expectedValue
     * @param string       $attributeName
     * @param CompanyQuery $query
     */
    protected function assertProtectedAttributeEquals($expectedValue, $attributeName, CompanyQuery $query)
    {
        $reflection = new \ReflectionObject($query);
        $attribute = $reflection->getProperty($attributeName);
        $attribute->setAccessible(true);
        $this->assertEquals($expectedValue, $attribute->getValue($query));
    }
}
