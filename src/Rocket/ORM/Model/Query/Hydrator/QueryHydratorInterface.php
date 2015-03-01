<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model\Query\Hydrator;

use Rocket\ORM\Model\Object\RocketObject;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface QueryHydratorInterface
{
    /**
     * @param \PDOStatement $stmt
     *
     * @return array|RocketObject|null
     */
    public function hydrate(\PDOStatement $stmt);
}
