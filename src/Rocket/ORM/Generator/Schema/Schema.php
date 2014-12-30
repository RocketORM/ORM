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
    protected $root;

    /**
     * @var array
     */
    protected $tables;


    /**
     * @param array                      $schema
     */
    public function __construct(array $schema)
    {
        $this->root   = $schema['root'];
        $this->tables = $schema['tables'];
    }

    /**
     * @return array
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }
}
