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
     * @param array $values The model values, required values must be provided
     * @param bool  $isNew  In case of the model is new, and will be inserted, pass the "true"
     *
     * @return void
     */
    public function hydrate(array $values, $isNew = false);
}
