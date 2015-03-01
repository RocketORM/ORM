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
use Rocket\ORM\Rocket;
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
     * @depends where
     * @depends limit
     */
    public function find()
    {
        $query = CompanyQuery::create('c');
        $con = Rocket::getConnection(Rocket::getTableMap(get_class(new Company()))->getConnectionName());

        foreach ([null, $con] as $connection) {
            $companies = $query
                ->limit(2)
                ->find($connection)
            ;

            $this->assertNotNull($companies);
            $this->assertInternalType('array', $companies);
            $this->assertCount(2, $companies);
            $this->assertArrayHasKey(0, $companies);
            $this->assertInternalType('object', $companies[0]);
            $this->assertTrue($companies[0] instanceof RocketObject);

            $this->assertEquals('Cadillak', $companies[0]['name']);
            $this->assertEquals('Ferrari', $companies[1]['name']);
        }

        $companies = $query
            ->where('name = ?', 'FooBarFooBar')
        ->find();

        $this->assertEquals([], $companies);
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
        $query = CompanyQuery::create('c');
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
     * @depends find
     */
    public function innerJoinWith()
    {
        // With and without alias
        foreach (['c', null] as $alias) {
            if (null === $alias) {
                $query = CompanyQuery::create();
            } else {
                $query = CompanyQuery::create($alias);
            }

            $companies = $query
                ->innerJoinWith('Wheels')
                ->where('name = ?', 'Honda')
            ->find();

            // Company array integrity
            $this->assertInternalType('array', $companies);
            $this->assertArrayHasKey(0, $companies);

            // Wheel array integrity
            $this->assertArrayHasKey('Wheels', $companies[0]);
            $this->assertInternalType('array', $companies[0]['Wheels']);

            // Wheel integrity
            $this->assertArrayHasKey(0, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][0] instanceof RocketObject);
            $this->assertEquals('GRAM_LIGHTS', $companies[0]['Wheels'][0]['unique_name']);

            $this->assertArrayHasKey(1, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][1] instanceof RocketObject);
            $this->assertEquals('VOLK', $companies[0]['Wheels'][1]['unique_name']);

            unset($companies);
        }

        // Deep join with and without alias
        foreach (['c', null] as $alias) {
            if (null === $alias) {
                $query = CompanyQuery::create();
            } else {
                $query = CompanyQuery::create($alias);
            }

            $companies = $query
                ->innerJoinWith('Wheels')
                ->innerJoinWith('Wheels.Cars', 'cars')
                ->where('name = ?', 'Honda')
            ->find();

            // Company array integrity
            $this->assertInternalType('array', $companies);
            $this->assertArrayHasKey(0, $companies);

            // Wheel array integrity
            $this->assertArrayHasKey('Wheels', $companies[0]);
            $this->assertInternalType('array', $companies[0]['Wheels']);

            // Wheel integrity
            $this->assertArrayHasKey(0, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][0] instanceof RocketObject);
            $this->assertEquals('GRAM_LIGHTS', $companies[0]['Wheels'][0]['unique_name']);

            // Cars array integrity
            $this->assertArrayHasKey('Cars', $companies[0]['Wheels'][0]);
            $this->assertInternalType('array', $companies[0]['Wheels'][0]['Cars']);
            $this->assertCount(2, $companies[0]['Wheels'][0]['Cars']);

            // Car integrity
            foreach ([0 => 2, 1 => 3] as $i => $id) {
                $this->assertArrayHasKey($i, $companies[0]['Wheels'][0]['Cars']);
                $this->assertTrue($companies[0]['Wheels'][0]['Cars'][$i] instanceof RocketObject);
                $this->assertEquals($id, $companies[0]['Wheels'][0]['Cars'][$i]['id']);
            }

            // Wheel integrity
            $this->assertArrayHasKey(1, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][1] instanceof RocketObject);
            $this->assertEquals('VOLK', $companies[0]['Wheels'][1]['unique_name']);

            // Cars array integrity
            $this->assertArrayHasKey('Cars', $companies[0]['Wheels'][1]);
            $this->assertInternalType('array', $companies[0]['Wheels'][1]['Cars']);
            $this->assertCount(1, $companies[0]['Wheels'][1]['Cars']);

            // Car integrity
            $this->assertArrayHasKey(0, $companies[0]['Wheels'][1]['Cars']);
            $this->assertTrue($companies[0]['Wheels'][1]['Cars'][0] instanceof RocketObject);
            $this->assertEquals(4, $companies[0]['Wheels'][1]['Cars'][0]['id']);

            unset($companies);
        }

        // Deep join (one) with and without alias
        foreach (['c', null] as $alias) {
            if (null === $alias) {
                $query = CarQuery::create();
            } else {
                $query = CarQuery::create($alias);
            }

            $cars = $query
                ->innerJoinWith('Wheel', 'w')
                ->innerJoinWith('w.Company')
                ->where('Company.name = ?', 'Honda')
            ->find();

            // Car array integrity
            $this->assertInternalType('array', $cars);
            $this->assertCount(3, $cars);

            foreach ($cars as $car) {
                // Wheel integrity
                $this->assertArrayHasKey('Wheel', $car);
                $this->assertNotNull($car['Wheel']);
                $this->assertTrue($car['Wheel'] instanceof RocketObject);

                // Company integrity
                $this->assertArrayHasKey('Company', $car['Wheel']);
                $this->assertNotNull($car['Wheel']['Company']);
                $this->assertTrue($car['Wheel']['Company'] instanceof RocketObject);
                $this->assertEquals('Honda', $car['Wheel']['Company']['name']);
            }

            // Data integrity
            $this->assertEquals(2, $cars[0]['id']);
            $this->assertEquals('GRAM_LIGHTS', $cars[0]['Wheel']['unique_name']);

            $this->assertEquals(3, $cars[1]['id']);
            $this->assertEquals('GRAM_LIGHTS', $cars[1]['Wheel']['unique_name']);

            $this->assertEquals(4, $cars[2]['id']);
            $this->assertEquals('VOLK', $cars[2]['Wheel']['unique_name']);
        }
    }

    /**
     * @test
     *
     * @depends innerJoinWith
     */
    public function leftJoinWith()
    {
        // With and without alias
        foreach (['c', null] as $alias) {
            if (null === $alias) {
                $query = CarQuery::create();
            } else {
                $query = CarQuery::create($alias);
            }

            $cars = $query
                ->leftJoinWith('Wheel')
            ->find();

            // Car array integrity
            $this->assertInternalType('array', $cars);
            $this->assertArrayHasKey(0, $cars);

            // Wheel integry
            $this->assertArrayHasKey('Wheel', $cars[0]);
            $this->assertTrue($cars[0]['Wheel'] instanceof RocketObject);

            // Wheel integrity
            $this->assertEquals('VOSSEN', $cars[0]['Wheel']['unique_name']);

            for ($i=0; $i<7; $i++) {
                $this->assertArrayHasKey('Wheel', $cars[$i]);
                $this->assertNotNull($cars[$i]['Wheel']);
            }

            // Car without Wheel integrity
            for ($i=7; $i<10; $i++) {
                $this->assertArrayHasKey('Wheel', $cars[$i]);
                $this->assertNull($cars[$i]['Wheel']);
            }

            unset($cars);
        }

        // Deep join (many) with and without alias
        foreach (['w', null] as $alias) {
            if (null === $alias) {
                $query = WheelQuery::create();
            } else {
                $query = WheelQuery::create($alias);
            }

            $wheels = $query
                ->leftJoinWith('Cars')
                ->leftJoinWith('Cars.Wheel', 'w2')
                ->leftJoinWith('w2.Company', 'c')
            ->find();

            // Wheel array integrity
            $this->assertInternalType('array', $wheels);

            foreach ($wheels as $wheel) {
                $this->assertTrue($wheel instanceof RocketObject);
                $this->assertArrayHasKey('Cars', $wheel);
                $this->assertInternalType('array', $wheel['Cars']);

                // Wheel without cars
                if (in_array($wheel['unique_name'], [
                    'KONIG', 'WORK', 'AMERICAN_RACING', 'RONAL'
                ])) {
                    $this->assertCount(0, $wheel['Cars']);

                    continue;
                }

                // Car integrity
                foreach ($wheel['Cars'] as $car) {
                    $this->assertTrue($car instanceof RocketObject);
                    $this->assertArrayHasKey('Wheel', $car);

                    // Car without wheel
                    if (in_array($car['id'], [
                        8, 9, 10
                    ])) {
                        $this->assertNull($car['Wheel']);

                        continue;
                    }

                    $this->assertTrue($car['Wheel'] instanceof RocketObject);
                    $this->assertArrayHasKey('Company', $car['Wheel']);
                    $this->assertTrue($car['Wheel']['Company'] instanceof RocketObject);
                }
            }

            unset($wheels);
        }
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
