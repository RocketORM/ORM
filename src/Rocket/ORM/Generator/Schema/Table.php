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
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $phpName;

    /**
     * @var string
     */
    public $type;

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
     * @var Schema
     */
    protected $schema;


    /**
     * @param string $name
     * @param array  $data
     */
    public function __construct($name, array $data)
    {
        $this->name    = $name;
        $this->phpName = $data['phpName'];
        $this->type    = $data['type'];

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

    /**
     * @param string $name
     *
     * @return Column
     */
    public function getColumn($name)
    {
        foreach ($this->columns as $column) {
            if ($name == $column->name) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getRelationsByTable()
    {
        $relations = [];
        foreach ($this->relations as $relation) {
            $relations[$relation->getTable()->name][] = $relation;
        }

        return $relations;
    }

    /**
     * @return bool
     */
    public function hasForeignKey()
    {
        foreach ($this->relations as $relation) {
            if ($relation->isForeignKey()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getForeignKeys()
    {
        $foreignKeys = [];
        foreach ($this->relations as $relation) {
            if ($relation->isForeignKey()) {
                $foreignKeys[$relation->getRelatedTable()->name][] = $relation;
            }
        }

        return $foreignKeys;
    }

    /**
     * @return bool
     */
    public function hasDefaultColumn()
    {
        foreach ($this->columns as $column) {
            if (null != $column->getDefault(true)) {
                return true;
            }
        }

        return false;
    }
}
