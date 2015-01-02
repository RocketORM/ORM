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
class Schema
{
    /**
     * @var string
     */
    public $connection;

    /**
     * @var string
     */
    public $namespace;

    /**
     * @var string
     */
    public $escapedNamespace;

    /**
     * @var string
     */
    public $relativeDirectory;

    /**
     * @var string
     */
    public $absoluteDirectory;

    /**
     * @var string
     */
    public $database;

    /**
     * @var array|Table[]
     */
    protected $tables = [];


    /**
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->connection        = $schema['connection'];
        $this->namespace         = $schema['namespace'];
        $this->relativeDirectory = $schema['directory'];
        $this->database          = $schema['database'];

        foreach ($schema['tables'] as $tableName => $data) {
            $table = new Table($tableName, $data);
            $table->setSchema($this);

            $this->tables[] = $table;
        }
    }

    /**
     * @return Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param string $name
     *
     * @return array|Table[]
     */
    public function findTables($name)
    {
        if (false !== strpos($name, '\\')) {
            $tables = $this->getTablesByNamespace($name);
        } else {
            if (false === strpos($name, '.')) {
                $tables = $this->getTablesByTableName($name);
            } else {
                $tables = $this->getTablesByDatabaseAndTableName($name);
            }
        }

        return $tables;
    }

    /**
     * @param string $namespace A table namespace like "Example\Model\MyModel"
     *
     * @return array|Table[]
     */
    protected function getTablesByNamespace($namespace)
    {
        $namespace = str_replace('\\\\', '\\', $namespace);
        $tables = [];

        foreach ($this->tables as $table) {
            if ($namespace === sprintf('%s\\%s', $this->namespace, $table->phpName)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param string $name A table name like "my_table"
     *
     * @return array|Table[]
     */
    protected function getTablesByTableName($name)
    {
        $tables = [];
        foreach ($this->tables as $table) {
            if ($name === $table->name) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    /**
     * @param string $prefixedName A table name like "database.my_table"
     *
     * @return array|Table[]
     */
    protected function getTablesByDatabaseAndTableName($prefixedName)
    {
        $tables = [];
        foreach ($this->tables as $table) {
            if ($prefixedName === sprintf('%s.%s', $this->database, $table->name)) {
                $tables[] = $table;
            }
        }

        return $tables;
    }
}
