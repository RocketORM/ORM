<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Generator\Model;

use Rocket\ORM\Generator\Model\TableMap\TableMapGenerator;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Test\Helper\TestHelper;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class TableMapTestHelper extends \PHPUnit_Framework_TestCase implements TestHelper
{
    /**
     * @param array  $schemas
     * @param string $modelNamespace
     * @param array  $templateDirs
     */
    public function generate(array $schemas, $modelNamespace = '\\Rocket\\ORM\\Model\\Map\\TableMap', $templateDirs = [])
    {
        if (!isset($schemas[0]) || !$schemas[0] instanceof Schema) {
            $this->markTestSkipped('Schema must be an instance of \\Rocket\\ORM\\Generator\\Schema\\Schema');
        }

        $generator = new TableMapGenerator($modelNamespace, $templateDirs);

        foreach ($schemas as $schema) {
            $generator->generate($schema);
        }
    }

    /**
     * @return string
     */
    public function getHelperName()
    {
        return 'table_map';
    }
}
