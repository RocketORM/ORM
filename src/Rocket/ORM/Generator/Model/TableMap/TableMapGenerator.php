<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Model\TableMap;

use Rocket\ORM\Generator\Generator;
use Rocket\ORM\Generator\Schema\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class TableMapGenerator extends Generator
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
    public function __construct($modelNamespace = '\\Rocket\\ORM\\Model\\Map\\TableMap', array $templateDirs = [])
    {
        $class = new \ReflectionClass($modelNamespace);
        if (!$class->implementsInterface('\\Rocket\\ORM\\Model\\Map\\TableMapInterface')) {
            throw new \InvalidArgumentException('The table map model must implement Rocket\ORM\Model\Map\TableMapInterface');
        }

        $this->modelNamespace = $modelNamespace;
        $this->twig           = new \Twig_Environment(new \Twig_Loader_Filesystem(array_merge($templateDirs, [__DIR__ . '/../../Resources/Skeletons/Model/Map'])), [
            'cache'            => false,
            'strict_variables' => true
        ]);
    }

    /**
     * @param Schema $schema
     */
    public function generate(Schema $schema)
    {
        $outputDirectory = $schema->absoluteDirectory . DIRECTORY_SEPARATOR . 'TableMap';
        $this->createDirectory($outputDirectory);

        foreach ($schema->getTables() as $table) {
            $template = $this->twig->render('table_map.php.twig', [
                'table'  => $table
            ]);

            file_put_contents($outputDirectory . DIRECTORY_SEPARATOR . $table->phpName . 'TableMap.php', $template);
        }
    }
}
