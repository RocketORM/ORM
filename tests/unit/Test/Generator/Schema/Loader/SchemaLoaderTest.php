<?php

namespace Test\Generator\Schema\Loader;

use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaLoaderTest extends RocketTestCase
{
    /**
     * @test
     */
    public function load()
    {
        $schemaLoader = new SchemaLoader(__DIR__ . '/../../../../../resources/schemas', [], new SchemaTransformer());
        $schemas = $schemaLoader->load();

        $this->assertCount(1, $schemas);
    }
}
