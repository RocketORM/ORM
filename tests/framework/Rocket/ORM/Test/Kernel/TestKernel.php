<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Kernel;

use Rocket\ORM\Config\ConfigLoader;
use Rocket\ORM\Generator\Database\DatabaseGenerator;
use Rocket\ORM\Generator\Database\Table\DatabaseTableGenerator;
use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Rocket;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class TestKernel
{
    /**
     * @var string
     */
    protected $databaseOutputDir;

    /**
     * @var string
     */
    protected $databaseInputDir;

    /**
     * @var string
     */
    protected $configPath;

    /**
     * @var string
     */
    protected $schemaDir;

    /**
     * @var array|Schema[]
     */
    protected $schemas;


    /**
     * @param string $databaseOutputDir
     * @param string $configPath
     * @param string $schemaDir
     * @param string $databaseInputDir
     */
    public function __construct($databaseOutputDir, $databaseInputDir, $configPath, $schemaDir)
    {
        $this->databaseOutputDir = $databaseOutputDir;
        $this->databaseInputDir  = $databaseInputDir;
        $this->configPath        = $configPath;
        $this->schemaDir         = $schemaDir;
    }

    /**
     * Initialize kernel
     */
    public function init()
    {
        $this->loadConfig();
        $this->loadSchemas();
        $this->generateSql();
        $this->loadDatabases();
    }

    /**
     * Load configuration
     */
    protected function loadConfig()
    {
        Rocket::setConfiguration((new ConfigLoader($this->configPath))->all());
    }

    /**
     * Generate schemas
     */
    protected function loadSchemas()
    {
        $this->schemas = (new SchemaLoader($this->schemaDir, [], new SchemaTransformer()))->load();
    }

    /**
     * Generate SQL files
     */
    protected function generateSql()
    {
        $databaseGenerator = new DatabaseGenerator($this->databaseInputDir);
        foreach ($this->schemas as $schema) {
            $databaseGenerator->generate($schema);
        }
    }

    /**
     * Delete old databases and generate new
     */
    protected function loadDatabases()
    {
        // Delete old databases
        $iterator = (new Finder())
            ->files()
            ->in($this->databaseOutputDir)
            ->name('*.sq3')
        ;

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            unlink($file->getRealPath());
        }

        // Generate new databases
        $tableGenerator = new DatabaseTableGenerator($this->databaseInputDir);
        foreach ($this->schemas as $schema) {
            $tableGenerator->generate($schema);
        }
    }
}
