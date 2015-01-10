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

use Rocket\ORM\Generator\GeneratorInterface;
use Rocket\ORM\Generator\Schema\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ObjectGenerator implements GeneratorInterface
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
        $this->twig           = new \Twig_Environment(new \Twig_Loader_Filesystem(array_merge($templateDirs, [__DIR__ . '/../../Resources/Skeletons/Model/Object'])), [
            'cache' => false
        ]);
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
        if (!is_dir($outputDirectory)) {
            if (!@mkdir($outputDirectory, 755, true)) {
                throw new \RuntimeException('Cannot create model directory, error message : ' . error_get_last()['message']);
            }
        }

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
        if (!is_dir($outputDirectory)) {
            if (!@mkdir($outputDirectory, 755, true)) {
                throw new \RuntimeException('Cannot create model directory, error message : ' . error_get_last()['message']);
            }
        }

        foreach ($schema->getTables() as $table) {
            $template = $this->twig->render('base_object.php.twig', [
                'table'  => $table
            ]);

            file_put_contents($outputDirectory . DIRECTORY_SEPARATOR . 'Base' . $table->phpName . '.php', $template);
        }
    }
}
