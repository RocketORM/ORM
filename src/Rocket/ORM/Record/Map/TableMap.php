<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record\Map;

use Rocket\ORM\Record\Map\Exception\RelationAlreadyExistsException;

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

    const RELATION_TYPE_ONE_TO_MANY  = 100;
    const RELATION_TYPE_MANY_TO_ONE  = 101;
    const RELATION_TYPE_ONE_TO_ONE   = 102;
    const RELATION_TYPE_MANY_TO_MANY = 103;

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
     * @param string     $name
     * @param string     $phpName
     * @param int        $type
     * @param null|int   $size
     * @param int        $decimal
     * @param null|array $values
     * @param null|bool  $default
     * @param bool       $isRequired
     */
    public function addColumn($name, $phpName, $type, $size = null, $decimal = 0, array $values = null, $default = null, $isRequired = false)
    {
        $this->columns[$name] = [
            'name'     => $name,
            'phpName'  => $phpName,
            'type'     => $type,
            'size'     => $size,
            'decimal'  => $decimal,
            'values'   => $values,
            'default'  => $default,
            'required' => $isRequired
        ];
    }

    /**
     * @param string $classNamespace
     * @param string $phpName
     * @param int    $type
     * @param string $local
     * @param string $foreign
     *
     * @throws RelationAlreadyExistsException
     */
    public function addRelation($classNamespace, $phpName, $type, $local, $foreign)
    {
        if (isset($this->relations[$phpName])) {
            throw new RelationAlreadyExistsException('The relation between ' . str_replace(['TableMap\\', 'TableMap'], '', get_called_class()) . ' and "' . $classNamespace . '" already exists');
        }

        $this->relations[$phpName] = [
            'namespace' => $classNamespace,
            'phpName'   => $phpName,
            'type'      => $type,
            'local'     => $local,
            'foreign'   => $foreign,
            'is_many'   => self::RELATION_TYPE_MANY_TO_MANY === $type || self::RELATION_TYPE_MANY_TO_ONE === $type
            //'table_map_namespace' => constant($classNamespace . 'Query::TABLE_MAP_NAMESPACE')
        ];
    }

    /**
     * @param array $row
     *
     * @return string
     *
     * @internal Used by the query class
     */
    public function getPrimaryKeysHash(array $row)
    {
        $hash = '';
        foreach ($this->getPrimaryKeys() as $primaryKey) {
            $hash .= $row[$primaryKey['name']];
        }

        // if hash is too long, transform it into MD5 hash
        if (isset($hash[33])) {
            return md5($hash);
        }

        return $hash;
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
     * @return array
     */
    public function getRelation($name)
    {
        if (!isset($this->relations[$name])) {
            throw new \InvalidArgumentException('The relation with name "' . $name . '" is not found for table "' . $this->getTableName() . '"');
        }

        return $this->relations[$name];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getColumn($name)
    {
        if (!isset($this->columns[$name])) {
            throw new \InvalidArgumentException('The column with name "' . $name . '" is not found for table "' . $this->getTableName() . '"');
        }

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
