<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record\Query;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface QueryInterface
{
    /**
     * @return string
     */
    public function getSqlQuery();
}
