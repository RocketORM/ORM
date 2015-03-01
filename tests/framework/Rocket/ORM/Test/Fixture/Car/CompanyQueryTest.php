<?php

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
