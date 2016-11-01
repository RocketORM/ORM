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

use Rocket\ORM\Record\ArrayRecord;
use Rocket\ORM\Record\Query\Hydrator\ComplexQueryHydrator;

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
            new ArrayRecord([
                'foo' => 'bar',
                'bar' => 'foo'
            ], $this->modelNamespace)
        ];
    }
}
