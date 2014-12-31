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
class Column
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $phpName;

    /**
     * @var int
     */
    public $type;

    /**
     * @var int
     */
    public $size;

    /**
     * @var int
     */
    public $decimal;

    /**
     * @var mixed
     */
    public $default;

    /**
     * @var bool
     */
    public $isRequired = true;

    /**
     * @var bool
     */
    public $isPrimaryKey = false;

    /**
     * @var bool
     */
    public $isAutoIncrement = false;

    /**
     * @var array
     */
    public $values;


    /**
     * @param string $name
     * @param array  $data
     */
    public function __construct($name, array $data)
    {
        $this->name            = $name;
        $this->phpName         = $data['phpName'];
        $this->size            = $data['size'];
        $this->type            = $data['type'];
        $this->decimal         = $data['decimal'];
        $this->default         = $data['default'];
        $this->isRequired      = $data['required'];
        $this->isPrimaryKey    = $data['primaryKey'];
        $this->isAutoIncrement = $data['autoIncrement'];
        $this->values          = $data['values'];
    }

    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param Table $table
     */
    public function setTable(Table $table)
    {
        $this->table = $table;
    }
}
