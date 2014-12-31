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
class Schema implements SchemaInterface
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
     * @var Table[]
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
}
