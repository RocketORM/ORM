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
class Table
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $phpName;

    /**
     * @var array|Column[]
     */
    protected $columns = [];

    /**
     * @var array|Relation[]
     */
    protected $relations = [];

    /**
     * @var array|Column[]
     */
    protected $primaryKeys = [];


    /**
     * @param string $name
     * @param array  $data
     */
    public function __construct($name, array $data)
    {
        $this->name    = $name;
        $this->phpName = $data['phpName'];

        foreach ($data['columns'] as $columnName => $columnData) {
            $column = new Column($columnName, $columnData);
            $column->setTable($this);

            $this->columns[] = $column;
        }

        foreach ($data['relations'] as $with => $relationData) {
            $relation = new Relation($with, $relationData);
            $relation->setTable($this);

            $this->relations[] = $relation;
        }
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param Schema $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param Column[] $columns
     */
    public function setColumns(array $columns)
    {
        if (isset($columns[0]) && !$columns[0] instanceof Column) {
            throw new \InvalidArgumentException('The table columns array items must extends \\Rocket\\ORM\\Generator\\Schema\\Column');
        }

        $this->columns = $columns;
    }

    /**
     * @return array|Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param Column $column
     */
    public function addPrimaryKey(Column $column)
    {
        $this->primaryKeys[$column->name] = $column;
    }

    /**
     * @return array|Column[]
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * @return int
     */
    public function getPrimaryKeyCount()
    {
        $count = 0;
        foreach ($this->columns as $column) {
            if ($column->isPrimaryKey) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param Relation $relation
     */
    public function addRelation(Relation $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRelation($name)
    {
        foreach ($this->relations as $relation) {
            if ($name == $relation->with) {
                return true;
            }
        }

        return false;
    }
}
