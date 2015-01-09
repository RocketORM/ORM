<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface ModelInterface
{
    /**
     * @param array $values
     *
     * @return void
     */
    public function hydrate(array $values);
}
