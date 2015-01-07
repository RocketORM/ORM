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

use Rocket\ORM\Model\Map\TableMap;

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
    protected $default;

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

    /**
     * @return string
     */
    public function typeAsString()
    {
        $reflection = new \ReflectionClass('\\Rocket\\ORM\\Model\\Map\\TableMap');
        foreach ($reflection->getConstants() as $name => $value) {
            if ($this->type == $value) {
                return $name;
            }
        }

        throw new \LogicException('Unknown value "' . $this->type . '" for constant TableMap::COLUMN_TYPE_*');
    }

    /**
     * @param bool $raw
     *
     * @return int|mixed
     */
    public function getDefault($raw = false)
    {
        if ($raw) {
            return $this->default;
        }

        if (TableMap::COLUMN_TYPE_ENUM == $this->type) {
            foreach ($this->values as $i => $value) {
                if ($value == $this->default) {
                    return $i;
                }
            }

            throw new \LogicException('The default value for column "' . $this->name . '" is not found');
        }

        return $this->default;
    }
}
