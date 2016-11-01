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

use Rocket\ORM\Generator\Model\Object\ObjectGenerator;
use Rocket\ORM\Generator\Schema\Schema;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @method markTestSkipped($message)
 */
trait ModelTestHelper
{
    /**
     * @param array  $schemas
     * @param string $modelNamespace
     * @param array  $templateDirs
     */
    public function generateObjects(array $schemas, $modelNamespace = '\\Rocket\\ORM\\Record\\Record', $templateDirs = [])
    {
        if (!isset($schemas[0]) || !$schemas[0] instanceof Schema) {
            $this->markTestSkipped('Schema must be an instance of \\Rocket\\ORM\\Generator\\Schema\\Schema');
        }

        $generator = new ObjectGenerator($modelNamespace, $templateDirs);

        foreach ($schemas as $schema) {
            $generator->generate($schema);
        }
    }
}
