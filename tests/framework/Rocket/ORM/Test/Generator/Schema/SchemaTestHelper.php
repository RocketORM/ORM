<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Generator\Schema;

use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Test\Helper\TestHelper;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaTestHelper extends \PHPUnit_Framework_TestCase implements TestHelper
{
    /**
     * @param SchemaLoader $schemaLoader
     * @param string       $assertion
     * @param string|null  $assertionMessage
     */
    public function assertSchemaLoadingException(SchemaLoader $schemaLoader, $assertion, $assertionMessage = null)
    {
        $error = null;
        try {
            $schemaLoader->load();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertTrue($error == $assertion, $assertionMessage);
    }

    /**
     * @return string
     */
    public function getHelperName()
    {
        return 'schema';
    }
}
