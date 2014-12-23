<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\TableMap;

use Rocket\ORM\Generator\Schema\SchemaInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class TableMapGenerator
{
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var \Twig_Loader_Filesystem
     */
    protected $twig;


    /**
     * @param string $modelNamespace
     * @param array  $templatePaths
     */
    public function __construct($modelNamespace = '\\Rocket\\ORM\\Model\\Map\\TableMap', array $templatePaths = [])
    {
        $class = new \ReflectionClass($modelNamespace);
        if (!$class->implementsInterface('\\Rocket\\ORM\\Model\\Map\\TableMapInterface')) {
            throw new \InvalidArgumentException('The table map model must implement Rocket\ORM\Model\Map\TableMapInterface');
        }

        $this->modelNamespace = $modelNamespace;
        $this->twig           = new \Twig_Environment(new \Twig_Loader_Filesystem(array_merge($templatePaths, [__DIR__ . '/../Resources/Skeletons'])), [
            'cache' => false
        ]);
    }

    /**
     * @param SchemaInterface $schema
     */
    public function generate(SchemaInterface $schema)
    {
        $root = $schema->getRoot();

        foreach ($schema->getTables() as $tableName => $table) {
            $template = $this->twig->render('Map/table_map.php.twig', [
                'schema' => $root,
                'model'  => $table
            ]);

            // TODO save to dir
            dump($template);
        }
    }
}