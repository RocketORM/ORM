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

use Rocket\ORM\Model\Object\RocketObject;
use Rocket\ORM\Model\Query\Hydrator\ComplexQueryHydrator;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class NullComplexQueryHydrator extends ComplexQueryHydrator
{
    /**
     * @inheritdoc
     */
    public function hydrate(\PDOStatement $stmt)
    {
        return [
            new RocketObject([
                'foo' => 'bar',
                'bar' => 'foo'
            ], $this->modelNamespace)
        ];
    }
}
