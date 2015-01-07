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
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;
use Rocket\ORM\Test\Helper\TestHelper;
use Symfony\Component\Finder\Finder;

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

        $this->assertEquals($error, $assertion, $assertionMessage);
    }

    /**
     * @param string|null                $schemasDir
     * @param SchemaTransformerInterface $schemaTransformer
     * @param string                     $schemaModelNamespace
     *
     * @return array|\Rocket\ORM\Generator\Schema\Schema[]
     */
    public function getSchemas($schemasDir = null, SchemaTransformerInterface $schemaTransformer = null,
                               $schemaModelNamespace = '\\Rocket\\ORM\\Generator\\Schema\\Schema')
    {
        if (null == $schemasDir) {
            $schemasDir = __DIR__ . '/../../../../../../resources/schemas';
        }

        if (null == $schemaTransformer) {
            $schemaTransformer = new SchemaTransformer($schemaModelNamespace);
        }

        $schemaLoader = new SchemaLoader($schemasDir, [], $schemaTransformer);
        $schemas = $schemaLoader->load();

        $finder = new Finder();
        $finder
            ->files()
            ->in($schemasDir)
        ;

        $this->assertCount($finder->count(), $schemas, 'Schemas count');

        return $schemas;
    }

    /**
     * @return string
     */
    public function getHelperName()
    {
        return 'schema';
    }
}
