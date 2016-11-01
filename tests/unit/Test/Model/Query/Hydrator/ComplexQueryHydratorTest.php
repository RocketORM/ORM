<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model\Query\Hydrator;

use Fixture\Car\Model\CarQuery;
use Fixture\Car\Model\CompanyQuery;
use Fixture\Car\Model\WheelQuery;
use Rocket\ORM\Record\ArrayRecord;
use Rocket\ORM\Record\Query\Hydrator\ComplexQueryHydrator;
use Rocket\ORM\Record\Query\QueryInterface;
use Rocket\ORM\Test\Model\Query\Hydrator\QueryHydratorTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Record\Query\Hydrator\ComplexQueryHydrator
 */
class ComplexQueryHydratorTest extends QueryHydratorTestCase
{
    /**
     * @test
     */
    public function hydrateSimpleInnerRelation()
    {
        $query = CompanyQuery::create('c')
            ->innerJoinWith('Wheels')
            ->where('name = ?', 'Honda')
        ;

        $companies = $this->createHydrator($query)->hydrate($this->createPDOStatement($query));

        // Company array integrity
        $this->assertInternalType('array', $companies);
        $this->assertCount(1, $companies);
        $this->assertArrayHasKey(0, $companies);

        // Wheel array integrity
        $this->assertArrayHasKey('Wheels', $companies[0]);
        $this->assertInternalType('array', $companies[0]['Wheels']);

        // Wheel integrity
        $this->assertArrayHasKey(0, $companies[0]['Wheels']);
        $this->assertTrue($companies[0]['Wheels'][0] instanceof ArrayRecord);
        $this->assertEquals('GRAM_LIGHTS', $companies[0]['Wheels'][0]['unique_name']);

        $this->assertArrayHasKey(1, $companies[0]['Wheels']);
        $this->assertTrue($companies[0]['Wheels'][1] instanceof ArrayRecord);
        $this->assertEquals('VOLK', $companies[0]['Wheels'][1]['unique_name']);
    }

    /**
     * @test
     */
    public function hydrateComplexInnerRelation()
    {
        $query = CompanyQuery::create('c')
            ->innerJoinWith('Wheels')
            ->innerJoinWith('Wheels.Cars', 'cars')
            ->where('name = ?', 'Honda')
        ;

        $companies = $this->createHydrator($query)->hydrate($this->createPDOStatement($query));

        // Company array integrity
        $this->assertInternalType('array', $companies);
        $this->assertCount(1, $companies);
        $this->assertArrayHasKey(0, $companies);

        // Wheel array integrity
        $this->assertArrayHasKey('Wheels', $companies[0]);
        $this->assertInternalType('array', $companies[0]['Wheels']);

        // Wheel integrity
        $this->assertArrayHasKey(0, $companies[0]['Wheels']);
        $this->assertTrue($companies[0]['Wheels'][0] instanceof ArrayRecord);
        $this->assertEquals('GRAM_LIGHTS', $companies[0]['Wheels'][0]['unique_name']);

        // Cars array integrity
        $this->assertArrayHasKey('Cars', $companies[0]['Wheels'][0]);
        $this->assertInternalType('array', $companies[0]['Wheels'][0]['Cars']);
        $this->assertCount(2, $companies[0]['Wheels'][0]['Cars']);

        // Car integrity
        foreach ([2, 3] as $i => $id) {
            $this->assertArrayHasKey($i, $companies[0]['Wheels'][0]['Cars']);
            $this->assertTrue($companies[0]['Wheels'][0]['Cars'][$i] instanceof ArrayRecord);
            $this->assertEquals($id, $companies[0]['Wheels'][0]['Cars'][$i]['id']);
        }

        // Wheel integrity
        $this->assertArrayHasKey(1, $companies[0]['Wheels']);
        $this->assertTrue($companies[0]['Wheels'][1] instanceof ArrayRecord);
        $this->assertEquals('VOLK', $companies[0]['Wheels'][1]['unique_name']);

        // Cars array integrity
        $this->assertArrayHasKey('Cars', $companies[0]['Wheels'][1]);
        $this->assertInternalType('array', $companies[0]['Wheels'][1]['Cars']);
        $this->assertCount(1, $companies[0]['Wheels'][1]['Cars']);

        // Car integrity
        $this->assertArrayHasKey(0, $companies[0]['Wheels'][1]['Cars']);
        $this->assertTrue($companies[0]['Wheels'][1]['Cars'][0] instanceof ArrayRecord);
        $this->assertEquals(4, $companies[0]['Wheels'][1]['Cars'][0]['id']);
    }

    /**
     * @test
     */
    public function hydrateSimpleLeftRelation()
    {
        $query = CarQuery::create('c')
            ->leftJoinWith('Wheel')
        ;

        $cars = $this->createHydrator($query)->hydrate($this->createPDOStatement($query));

        // Car array integrity
        $this->assertInternalType('array', $cars);
        $this->assertCount(10, $cars);
        $this->assertArrayHasKey(0, $cars);

        // Wheel integry
        $this->assertArrayHasKey('Wheel', $cars[0]);
        $this->assertTrue($cars[0]['Wheel'] instanceof ArrayRecord);

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
    }

    /**
     * @test
     */
    public function hydrateComplexLeftRelation()
    {
        $query = WheelQuery::create('w')
            ->leftJoinWith('Cars')
            ->leftJoinWith('Cars.Wheel', 'w2')
            ->leftJoinWith('w2.Company', 'c')
        ;

        $wheels = $this->createHydrator($query)->hydrate($this->createPDOStatement($query));

        // Wheel array integrity
        $this->assertInternalType('array', $wheels);

        foreach ($wheels as $wheel) {
            $this->assertTrue($wheel instanceof ArrayRecord);
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
                $this->assertTrue($car instanceof ArrayRecord);
                $this->assertArrayHasKey('Wheel', $car);

                // Car without wheel
                if (in_array($car['id'], [
                    8, 9, 10
                ])) {
                    $this->assertNull($car['Wheel']);

                    continue;
                }

                $this->assertTrue($car['Wheel'] instanceof ArrayRecord);
                $this->assertArrayHasKey('Company', $car['Wheel']);
                $this->assertTrue($car['Wheel']['Company'] instanceof ArrayRecord);
            }
        }
    }

    /**
     * @param QueryInterface $query
     *
     * @return ComplexQueryHydrator
     */
    protected function createHydrator(QueryInterface $query)
    {
        return new ComplexQueryHydrator(
            $this->getProtectedAttribute($query, 'modelNamespace'),
            $this->getProtectedAttribute($query, 'alias'),
            $this->getProtectedAttribute($query, 'joins')
        );
    }
}
