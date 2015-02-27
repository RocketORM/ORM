<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Model\Object;

use Rocket\ORM\Generator\Generator;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ObjectGenerator extends Generator
{
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var \Twig_Environment
     */
    protected $twig;


    /**
     * @param string $modelNamespace
     * @param array  $templateDirs
     */
    public function __construct($modelNamespace = '', array $templateDirs = [])
    {
        $this->modelNamespace = $modelNamespace;

        $loader = new \Twig_Loader_Filesystem(array_merge($templateDirs, [
            __DIR__ . '/../../Resources/Skeletons/Model/Object'
        ]));
        $loader->addPath(__DIR__ . '/../../Resources/Skeletons/Model/Object/Driver/SQLite', 'sqlite');

        $this->twig = new \Twig_Environment($loader, [
            'cache'            => false,
            'strict_variables' => true
        ]);

        $this->twig->addFilter(new \Twig_SimpleFilter('type', function ($variable, $type) {
            switch ($type) {
                case 'string': return is_string($variable);
            }

            throw new \LogicException('Unknown type "' . $type . '" for the Twig filter "type"');
        }));
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function generate(Schema $schema)
    {
        // First generate base
        $this->generateBase($schema);

        $outputDirectory = $schema->absoluteDirectory;
        $this->createDirectory($outputDirectory);

        foreach ($schema->getTables() as $table) {
            $outputFile = $outputDirectory . DIRECTORY_SEPARATOR . $table->phpName . '.php';

            // Check if file already exists, do not override the file
            if (is_file($outputFile)) {
                continue;
            }

            $template = $this->twig->render('object.php.twig', [
                'table'  => $table
            ]);

            file_put_contents($outputFile, $template);
        }
    }

    /**
     * @param Schema $schema
     */
    protected function generateBase(Schema $schema)
    {
        $outputDirectory = $schema->absoluteDirectory . DIRECTORY_SEPARATOR . 'Base';
        $this->createDirectory($outputDirectory);

        // Allow overriding template for a given driver
        $dsn = Rocket::getConfiguration('connections.' . $schema->connection)['params']['dsn'];
        $driver = substr($dsn, 0, strpos($dsn, ':'));

        foreach ($schema->getTables() as $table) {
            $template = $this->twig->resolveTemplate([
                '@' . $driver . '/base_object.php.twig',
                'base_object.php.twig'
            ])->render([
                'table'  => $table
            ]);

            file_put_contents($outputDirectory . DIRECTORY_SEPARATOR . 'Base' . $table->phpName . '.php', $template);
        }
    }
}
