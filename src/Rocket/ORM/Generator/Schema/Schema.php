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
class Schema implements SchemaInterface
{
    /**
     * @var array
     */
    protected $data;


    /**
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->data = $schema;
    }

    /**
     * @return array
     */
    public function getRoot()
    {
        $root = $this->data;
        unset($root['tables']);

        return $root;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->data['tables'];
    }
}
