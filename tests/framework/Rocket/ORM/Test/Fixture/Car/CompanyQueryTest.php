<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Fixture\Car;

use Fixture\Car\Model\CompanyQuery;
use Rocket\ORM\Test\Model\Query\Hydrator\NullComplexQueryHydrator;
use Rocket\ORM\Test\Model\Query\Hydrator\NullSimpleQueryHydrator;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class CompanyQueryTest extends CompanyQuery
{
    /**
     * @inheritdoc
     */
    protected function getSimpleQueryHydrator()
    {
        return new NullSimpleQueryHydrator($this->modelNamespace);
    }

    /**
     * @inheritdoc
     */
    protected function getComplexQueryHydrator()
    {
        return new NullComplexQueryHydrator($this->modelNamespace, $this->alias, $this->joins);
    }
}
