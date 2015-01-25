<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Model\Query;

use Rocket\ORM\Generator\Generator;
use Rocket\ORM\Generator\Schema\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class QueryGenerator extends Generator
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
    public function __construct($modelNamespace = '\\Rocket\\ORM\\Model\\Query\\Query', array $templateDirs = [])
    {
        $class = new \ReflectionClass($modelNamespace);
        if (!$class->implementsInterface('\\Rocket\\ORM\\Model\\Query\\QueryInterface')) {
            throw new \InvalidArgumentException('The table map model must implement Rocket\ORM\Model\Query\QueryInterface');
        }

        $this->modelNamespace = $modelNamespace;
        $this->twig           = new \Twig_Environment(new \Twig_Loader_Filesystem(array_merge($templateDirs, [__DIR__ . '/../../Resources/Skeletons/Model/Query'])), [
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
        // First generate base
        $this->generateBase($schema);

        $outputDirectory = $schema->absoluteDirectory;
        $this->createDirectory($outputDirectory);

        foreach ($schema->getTables() as $table) {
            $outputFile = $outputDirectory . DIRECTORY_SEPARATOR . $table->phpName . 'Query.php';

            // Check if file already exists, do not override the file
            if (is_file($outputFile)) {
                continue;
            }

            $template = $this->twig->render('query.php.twig', [
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

        foreach ($schema->getTables() as $table) {
            $template = $this->twig->render('base_query.php.twig', [
                'table'  => $table
            ]);

            file_put_contents($outputDirectory . DIRECTORY_SEPARATOR . 'Base' . $table->phpName . 'Query.php', $template);
        }
    }
}
