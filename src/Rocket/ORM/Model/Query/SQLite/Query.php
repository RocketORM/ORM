<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model\Query\SQLite;

use Rocket\ORM\Model\Map\TableMapInterface;
use Rocket\ORM\Model\Query\Query as BaseQuery;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Query extends BaseQuery
{
    /**
     * @inheritdoc
     */
    protected function buildRelationClauses()
    {
        $query = '';
        foreach ($this->joins as $alias => $join) {
            /** @var TableMapInterface $relationTableMap */
            $tableMap = Rocket::getTableMap($join['relation']['namespace']);
            $query .= sprintf(' %s JOIN `%s` %s ON %s.%s = %s.%s',
                $join['type'],
                $tableMap->getTableName(),
                $alias,
                $join['from'],
                $join['relation']['local'],
                $alias,
                $join['relation']['foreign']
            );

            unset($tableMap);
        }

        return $query;
    }
}
