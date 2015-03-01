<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Model\Query\Hydrator;

use Rocket\ORM\Model\Query\Hydrator\QueryHydratorInterface;
use Rocket\ORM\Model\Query\QueryInterface;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class QueryHydratorTestCase extends RocketTestCase
{
    /**
     * @param object $object
     * @param string $attributeName
     *
     * @return mixed
     */
    protected function getProtectedAttribute($object, $attributeName)
    {
        $reflection = new \ReflectionObject($object);
        $attribute = $reflection->getProperty($attributeName);
        $attribute->setAccessible(true);

        return $attribute->getValue($object);
    }

    /**
     * @param QueryInterface $query
     *
     * @return \PDOStatement
     */
    protected function createPDOStatement(QueryInterface $query)
    {
        $con = Rocket::getConnection(
            Rocket::getTableMap($this->getProtectedAttribute($query, 'modelNamespace'))->getConnectionName()
        );

        $stmt = $con->prepare($query->getSqlQuery());
        $stmt->execute();

        return $stmt;
    }

    /**
     * @param QueryInterface $query
     *
     * @return QueryHydratorInterface
     */
    protected abstract function createHydrator(QueryInterface $query);
}
