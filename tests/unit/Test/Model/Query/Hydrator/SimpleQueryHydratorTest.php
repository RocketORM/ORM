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

use Fixture\Car\Model\CompanyQuery;
use Rocket\ORM\Model\Query\Hydrator\SimpleQueryHydrator;
use Rocket\ORM\Model\Query\QueryInterface;
use Rocket\ORM\Test\Model\Query\Hydrator\QueryHydratorTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Model\Query\Hydrator\SimpleQueryHydrator
 */
class SimpleQueryHydratorTest extends QueryHydratorTestCase
{
    /**
     * @test
     */
    public function hydrate()
    {
        $query = CompanyQuery::create('c')
            ->where('name = ?', 'Honda')
            ->limit(1)
        ;

        $companies = $this->createHydrator($query)->hydrate($this->createPDOStatement($query));

        // Company integrity
        $this->assertCount(1, $companies);
        $this->assertArrayHasKey(0, $companies);

        $this->assertEquals('Honda', $companies[0]['name']);
        $this->assertCount(2, $companies[0]);
    }

    /**
     * @inheritdoc
     */
    protected function createHydrator(QueryInterface $query)
    {
        return new SimpleQueryHydrator(
            $this->getProtectedAttribute($query, 'modelNamespace')
        );
    }
}
