<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model\Map;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class TableMap implements TableMapInterface
{
    const COLUMN_TYPE_INTEGER  = 1;
    const COLUMN_TYPE_STRING   = 2;
    const COLUMN_TYPE_DOUBLE   = 3;
    const COLUMN_TYPE_BOOLEAN  = 4;

    const RELATION_TYPE_ONE_TO_MANY  = 0;
    const RELATION_TYPE_ONE_TO_ONE   = 1;
    const RELATION_TYPE_MANY_TO_ONE  = 2;
    const RELATION_TYPE_MANY_TO_MANY = 3;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $classNamespace;

    /**
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var string
     */
    protected $databaseName;


    /**
     * @param string $name
     * @param bool   $isAutoincrement
     */
    public function addPrimaryKey($name, $isAutoincrement = false)
    {
        $this->primaryKeys[] = [
            'name'             => $name,
            'is_autoincrement' => $isAutoincrement
        ];
    }

    /**
     * @param string   $name
     * @param string   $phpName
     * @param int      $type
     * @param null|int $size
     * @param int      $scale
     * @param bool     $isNullable
     */
    public function addColumn($name, $phpName, $type, $size = null, $scale = 0, $isNullable = false)
    {
        $this->columns[$name] = [
            'name'        => $name,
            'phpName'     => $phpName,
            'type'        => $type,
            'size'        => $size,
            'scale'       => $scale,
            'is_nullable' => $isNullable
        ];
    }

    /**
     * @param string $classNamespace
     * @param string $phpName
     * @param int    $type
     * @param string $local
     * @param string $foreign
     */
    public function addRelation($classNamespace, $phpName, $type, $local, $foreign)
    {
        $this->relations[$phpName] = [
            'namespace' => $classNamespace,
            'phpName' => $phpName,
            'type' => $type,
            'local' => $local,
            'foreign' => $foreign,
            'table_map_namespace' => constant($classNamespace . 'Query::TABLE_MAP_NAMESPACE')
        ];
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassNamespace()
    {
        return $this->classNamespace;
    }

    /**
     * @param string $classNamespace
     */
    public function setClassNamespace($classNamespace)
    {
        $this->classNamespace = $classNamespace;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }


    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasColumn($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasRelation($name)
    {
        return isset($this->relations[$name]);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getRelation($name)
    {
        return $this->relations[$name];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getColumn($name)
    {
        return $this->columns[$name];
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->databaseName;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->databaseName = $database;
    }
}
