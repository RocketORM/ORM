<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator;

use Rocket\ORM\Generator\Schema\SchemaInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface GeneratorInterface
{
    public function generate(SchemaInterface $schema);
}
