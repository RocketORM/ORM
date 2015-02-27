<?php

namespace Test\Model\Query;

use Fixture\Car\Model\Company;
use Fixture\Car\Model\CompanyQuery;
use Rocket\ORM\Model\Object\RocketObject;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Model\Query\Query
 */
class QueryTest extends RocketTestCase
{
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

            $this->assertInternalType('array', $companies);
            $this->assertArrayHasKey(0, $companies);
            $this->assertArrayHasKey('Wheels', $companies[0]);
            $this->assertInternalType('array', $companies[0]['Wheels']);

            $this->assertArrayHasKey(0, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][0] instanceof RocketObject);
            $this->assertArrayHasKey(1, $companies[0]['Wheels']);
            $this->assertTrue($companies[0]['Wheels'][1] instanceof RocketObject);

            $this->assertEquals('GRAM_LIGHTS', $companies[0]['Wheels'][0]['unique_name']);
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

            // Wheel array integrity
            $this->assertInternalType('array', $companies);
            $this->assertArrayHasKey(0, $companies);
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
