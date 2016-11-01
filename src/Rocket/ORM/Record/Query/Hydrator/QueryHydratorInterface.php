<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record\Query\Hydrator;

use Rocket\ORM\Record\ArrayRecord;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface QueryHydratorInterface
{
    /**
     * @param \PDOStatement $stmt
     *
     * @return array|\Rocket\ORM\Record\ArrayRecord|null
     */
    public function hydrate(\PDOStatement $stmt);
}
