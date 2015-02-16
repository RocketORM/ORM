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
class Relation
{
    /**
     * @var string
     */
    public $with;

    /**
     * @var string
     */
    public $phpName;

    /**
     * @var string
     */
    public $local;

    /**
     * @var Column
     */
    public $localColumn;

    /**
     * @var string
     */
    public $foreign;

    /**
     * @var Column
     */
    public $foreignColumn;

    /**
     * @var string
     */
    public $onDelete = 'RESTRICT';

    /**
     * @var string
     */
    public $onUpdate = 'RESTRICT';

    /**
     * @var int
     */
    public $type;

    /**
     * @var Table
     */
    protected $localTable;

    /**
     * A related table is the table where the relation is explicitly mentioned in the schema configuration,
     * so it can be NULL for related relations
     *
     * @var Table
     */
    protected $relatedTable;


    /**
     * @param string $with
     * @param array  $data
     */
    public function __construct($with, array $data)
    {
        $this->with     = $with;
        $this->phpName  = $data['phpName'];
        $this->local    = $data['local'];
        $this->foreign  = $data['foreign'];
        $this->onDelete = $data['onDelete'];
        $this->onUpdate = $data['onUpdate'];

        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
    }


    /**
     * @return Table
     */
    public function getLocalTable()
    {
        return $this->localTable;
    }

    /**
     * @param Table $table
     */
    public function setLocalTable(Table $table)
    {
        $this->localTable  = $table;
        $this->localColumn = $table->getColumn($this->local);
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore LogicException cannot be reached by a test
     */
    public function getTypeConstantName()
    {
        $reflection = new \ReflectionClass('\\Rocket\\ORM\\Model\\Map\\TableMap');
        foreach ($reflection->getConstants() as $name => $value) {
            if ($this->type == $value) {
                return $name;
            }
        }

        throw new \LogicException('Unknown value "' . $this->type . '" for constant TableMap::RELATION_TYPE_*');
    }

    /**
     * @return Table
     */
    public function getRelatedTable()
    {
        return $this->relatedTable;
    }

    /**
     * @param Table $relatedTable
     */
    public function setRelatedTable($relatedTable)
    {
        $this->relatedTable  = $relatedTable;
        $this->foreignColumn = $relatedTable->getColumn($this->foreign);
    }

    /**
     * @return bool
     */
    public function isForeignKey()
    {
        return null != $this->relatedTable;
    }

    /**
     * @param bool $firstUpper True if the first letter must be upper case
     *
     * @return string
     */
    public function getPhpName($firstUpper = true)
    {
        if (!$firstUpper) {
            return lcfirst($this->phpName);
        }

        return $this->phpName;
    }

    /**
     * @return bool
     */
    public function isMany()
    {
        return TableMap::RELATION_TYPE_MANY_TO_ONE === $this->type
               || TableMap::RELATION_TYPE_MANY_TO_MANY === $this->type;
    }
}
