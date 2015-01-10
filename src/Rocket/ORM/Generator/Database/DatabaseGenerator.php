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

use Rocket\ORM\Generator\GeneratorInterface;
use Rocket\ORM\Generator\Schema\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class DatabaseGenerator implements GeneratorInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;


    /**
     * @param array $templateDirs
     */
    public function __construct(array $templateDirs = [])
    {
        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array_merge($templateDirs, [__DIR__ . '/../Resources/Skeletons/Database'])), [
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
        $this->generateDatabase($schema);
        $this->generateSql($schema);
    }

    /**
     * @param Schema $schema
     */
    public function generateDatabase(Schema $schema)
    {
        // TODO implement this method
    }

    /**
     * @param Schema $schema
     */
    public function generateSql(Schema $schema)
    {
        $template = $this->twig->render('schema.sql.twig', [
            'schema'  => $schema
        ]);

        // TODO save or execute output
    }
}
