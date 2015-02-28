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

use Rocket\ORM\Model\Query\Query as BaseQuery;

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
        // Remove the database name

        return preg_replace('/`([a-zA-Z0-9_-]+)`\./', '', parent::buildRelationClauses());
    }
}
