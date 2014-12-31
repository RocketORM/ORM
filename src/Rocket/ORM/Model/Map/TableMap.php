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
    const COLUMN_TYPE_FLOAT    = 5;
    const COLUMN_TYPE_TEXT     = 6;
    const COLUMN_TYPE_DATE     = 7;
    const COLUMN_TYPE_DATETIME = 8;
    const COLUMN_TYPE_ENUM     = 9;

    const RELATION_TYPE_ONE_TO_MANY  = 1;
    const RELATION_TYPE_MANY_TO_ONE  = 2;
    const RELATION_TYPE_ONE_TO_ONE   = 3;
    const RELATION_TYPE_MANY_TO_MANY = 4;

    /**
     * @var string
     */
    protected $connectionName;

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
    protected $database;


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
     * @param bool     $isRequired
     */
    public function addColumn($name, $phpName, $type, $size = null, $scale = 0, $default = null, $isRequired = false)
    {
        $this->columns[$name] = [
            'name'        => $name,
            'phpName'     => $phpName,
            'type'        => $type,
            'size'        => $size,
            'scale'       => $scale,
            'default'     => $default,
            'is_required' => $isRequired
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
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * @param string $connectionName
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;
    }

    /**
     * @param string $stringType
     *
     * @return int
     */
    public static function convertColumnTypeToConstant($stringType)
    {
        switch (strtolower($stringType)) {
            case 'boolean':  return self::COLUMN_TYPE_BOOLEAN;
            case 'text':     return self::COLUMN_TYPE_TEXT;
            case 'varchar':  return self::COLUMN_TYPE_STRING;
            case 'integer':  return self::COLUMN_TYPE_INTEGER;
            case 'double':   return self::COLUMN_TYPE_DOUBLE;
            case 'float':    return self::COLUMN_TYPE_FLOAT;
            case 'date':     return self::COLUMN_TYPE_DATE;
            case 'datetime': return self::COLUMN_TYPE_DATETIME;
            case 'enum':     return self::COLUMN_TYPE_ENUM;
        }

        throw new \InvalidArgumentException('Invalid column type for value "' . $stringType . '"');
    }
}
