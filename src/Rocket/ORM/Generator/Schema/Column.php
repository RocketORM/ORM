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
     * @var string
     */
    public $description;


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
        $this->description     = $data['description'];
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
    public function getTypeConstantName()
    {
        $reflection = new \ReflectionClass('\\Rocket\\ORM\\Model\\Map\\TableMap');
        foreach ($reflection->getConstants() as $name => $value) {
            if ($this->type == $value) {
                return $name;
            }
        }

        throw new \LogicException(
            'Unknown value "' . $this->type . '" for constant TableMap::COLUMN_TYPE_* for column "' . $this->name . '"'
        );
    }

    /**
     * @param bool $raw
     *
     * @return int|mixed
     */
    public function getDefault($raw = false)
    {
        if (TableMap::COLUMN_TYPE_ENUM == $this->type) {
            if ($raw) {
                return $this->default;
            }

            foreach ($this->values as $i => $value) {
                if ($value == $this->default) {
                    return $i;
                }
            }

            throw new \LogicException('The default value for column "' . $this->name . '" is not found');
        }

        return $this->default;
    }

    /**
     * @return string
     */
    public function getAttributePhpDoc()
    {
        $doc = "/**" . PHP_EOL;

        $startDoc = '     * ';
        if (null != $this->description) {
            $doc .= $startDoc . str_replace('*/', '', $this->description) . PHP_EOL . $startDoc . PHP_EOL;
        }

        return $doc . $startDoc . '@var ' . $this->getTypeAsPhpDoc() . PHP_EOL . '     */';
    }

    /**
     * @return string
     */
    public function getTypeAsPhpDoc()
    {
        switch ($this->type) {
            case TableMap::COLUMN_TYPE_BOOLEAN:  return 'bool';
            case TableMap::COLUMN_TYPE_DATE:
            case TableMap::COLUMN_TYPE_DATETIME: return '\DateTime';
            case TableMap::COLUMN_TYPE_DOUBLE:   return 'double';
            case TableMap::COLUMN_TYPE_ENUM:
            case TableMap::COLUMN_TYPE_INTEGER:  return 'int';
            case TableMap::COLUMN_TYPE_FLOAT:    return 'float';
            case TableMap::COLUMN_TYPE_STRING:
            case TableMap::COLUMN_TYPE_TEXT:     return 'string';
        }

        return 'mixed';
    }

    /**
     * @param bool $firstLetterUpper
     *
     * @return string
     */
    public function getPhpName($firstLetterUpper = false)
    {
        if ($firstLetterUpper) {
            return ucfirst($this->phpName);
        }

        return $this->phpName;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        // Do not prefix method name when the column phpName starts by "is" or "has"
        if (0 === strpos($this->phpName, 'is') || 0 === strpos($this->phpName, 'has')) {
            return $this->phpName;
        }

        return 'get' . $this->getPhpName(true);
    }

    /**
     * @return string
     */
    public function getTypeAsString()
    {
        $constant = $this->getTypeConstantName();
        $parts = explode('_', $constant);

        return strtolower($parts[sizeof($parts) - 1]);
    }
}
