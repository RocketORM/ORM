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
class SimpleQueryHydrator implements QueryHydratorInterface
{
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @param string $modelNamespace
     */
    public function __construct($modelNamespace)
    {
        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @inheritdoc
     */
    public function hydrate(\PDOStatement $stmt)
    {
        $objects = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $objects[] = new ArrayRecord($row, $this->modelNamespace);
        }

        return $objects;
    }
}
