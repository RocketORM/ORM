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
    protected $cacheDir;

    /**
     * @var string
     */
    protected $sqlInputDir;

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
     * @param string $cacheDir
     * @param string $configPath
     * @param string $schemaDir
     * @param string $sqlInputDir
     */
    public function __construct($cacheDir, $sqlInputDir, $configPath, $schemaDir)
    {
        $this->cacheDir    = $cacheDir;
        $this->sqlInputDir = $sqlInputDir;
        $this->configPath  = $configPath;
        $this->schemaDir   = $schemaDir;
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
        $config = (new ConfigLoader($this->configPath))->all();
        $cacheFolder = $this->cacheDir . DIRECTORY_SEPARATOR . 'config';
        if (!is_dir($cacheFolder)) {
            mkdir($cacheFolder);
        }

        file_put_contents(
            $cacheFolder . DIRECTORY_SEPARATOR . 'config.php',
            '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($config, true) . ';' . PHP_EOL
        );

        // Needed for generation
        Rocket::setConfiguration($config);
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
        $databaseGenerator = new DatabaseGenerator($this->sqlInputDir);
        foreach ($this->schemas as $schema) {
            $databaseGenerator->generate($schema);
        }
    }

    /**
     * Delete old databases and generate new
     */
    protected function loadDatabases()
    {
        try {
            // Delete old databases
            $iterator = (new Finder())
                ->files()
                ->in($this->cacheDir . DIRECTORY_SEPARATOR . 'databases')
                ->name('*.sq3')
            ;

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                unlink($file->getRealPath());
            }
        } catch (\InvalidArgumentException $e) {
            // Directory is not exist
            mkdir($this->cacheDir . DIRECTORY_SEPARATOR . 'databases');
        }

        // Generate new databases
        $tableGenerator = new DatabaseTableGenerator($this->sqlInputDir);
        foreach ($this->schemas as $schema) {
            $tableGenerator->generate($schema);
        }
    }
}
