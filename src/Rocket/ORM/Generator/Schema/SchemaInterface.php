<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface SchemaInterface
{
    /**
     * @return array
     */
    public function getRoot();

    /**
     * @return array
     */
    public function getTables();
}
