<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Database;

use Rocket\ORM\Generator\Generator;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class DatabaseGenerator extends Generator
{
    /**
     * @var string
     */
    protected $outputPath;

    /**
     * @var \Twig_Environment
     */
    protected $twig;


    /**
     * @param string $outputPath
     * @param array  $templateDirs
     */
    public function __construct($outputPath, array $templateDirs = [])
    {
        $this->outputPath = $outputPath;

        $loader = new \Twig_Loader_Filesystem(array_merge($templateDirs, [
            __DIR__ . '/../Resources/Skeletons/Database'
        ]));
        $loader->addPath(__DIR__ . '/../Resources/Skeletons/Database/Driver/SQLite', 'sqlite');

        $this->twig = new \Twig_Environment($loader, [
            'cache'            => false,
            'strict_variables' => true
        ]);

    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function generate(Schema $schema)
    {
        $this->createDirectory($this->outputPath);

        // Allow overriding template for a given driver
        $driver = Rocket::getConnectionDriver($schema->connection);

        $template = $this->twig->resolveTemplate(['@' . $driver . '/schema.sql.twig', 'schema.sql.twig'])->render([
            'schema' => $schema,
            'driver' => $driver
        ]);

        file_put_contents($this->outputPath . DIRECTORY_SEPARATOR . $schema->database . '.sql', $template);
    }
}
