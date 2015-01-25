<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Database\Table;

use Rocket\ORM\Connection\PDO\SQLitePDO;
use Rocket\ORM\Generator\Generator;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class DatabaseTableGenerator extends Generator
{
    /**
     * @var string
     */
    protected $inputPath;


    /**
     * @param string $inputPath
     */
    public function __construct($inputPath)
    {
        $this->inputPath = $inputPath;
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function generate(Schema $schema)
    {
        if (!is_dir($this->inputPath)) {
            throw new \RuntimeException('The input file path "' . $this->inputPath . '" is not a directory');
        }

        $con = Rocket::getConnection($schema->connection);
        if (!$con instanceof SQLitePDO) {
            $con->exec('use ' . $schema->database);
        }

        $file = file_get_contents($this->inputPath . DIRECTORY_SEPARATOR . $schema->database . '.sql');
        $file = preg_replace('/(-- ?(.)*)*/', '', $file); // delete comments

        $queries = explode(';', $file);
        foreach ($queries as $query) {
            $query = trim($query);
            if ('' == $query) {
                continue;
            }

            $con->exec($query);
        }
    }
}
