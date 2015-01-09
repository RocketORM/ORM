<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Generator\Schema\Loader;

use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaLoaderTest extends RocketTestCase
{
    /**
     * @var string
     */
    protected $schemaDirPath;

    /**
     * @var string
     */
    protected $validSchema;

    /**
     * @var SchemaTestHelper
     */
    protected $schemaHelper;


    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->schemaDirPath = $this->rootDir . '/resources/schemas';
        $this->validSchema = Yaml::parse($this->schemaDirPath . '/car_schema.yml');
        $this->schemaHelper = $this->getHelper('schema');
    }

    /**
     * @test
     */
    public function load()
    {
        // Wrong schema model class
        $error = null;
        try {
            new SchemaTransformer('\\Rocket\\ORM\\Rocket');
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
        }

        $this->assertTrue($error == 'The schema model must extend Rocket\ORM\Generator\Schema\Schema', 'Wrong schema model class');

        // Custom schema model class
        $transformer = new SchemaTransformer($this->getMockClass('\\Rocket\\ORM\\Generator\\Schema\\Schema', [], [], 'SchemaTest'));
        $this->assertTrue(null != $transformer, 'Custom schema model class');

        // Schema not found
        $this->schemaHelper->assertSchemaLoadingException(
            new SchemaLoader(__DIR__, [], new SchemaTransformer()),
            'Schema not found in path "' . __DIR__ . '"',
            'Schema not found'
        );

        // Format directory path
        $validSchema = $this->validSchema;
        $validSchema['directory'] = '../Model/';
        $schemaLoader = new InlineSchemaLoader([$validSchema]);
        $schemas = $schemaLoader->load();

        $this->assertCount(1, $schemas, 'Schemas count');
        $this->assertTrue('/../Model' == $schemas[0]->relativeDirectory, 'Model output directory format');

        // Good load
        $schemaLoader = new SchemaLoader($this->schemaDirPath, [], new SchemaTransformer());
        $schemas = $schemaLoader->load();

        $finder = new Finder();
        $finder->files()
            ->in($this->schemaDirPath)
        ;

        $finder->getIterator()->rewind();
        $this->validSchema = $finder->getIterator()->current();

        $this->assertCount($finder->count(), $schemas, 'Schemas count');
    }

    /**
     * @test
     */
    public function invalidColumnConfiguration()
    {
        // Wrong column name
        $wrongSchema = $this->validSchema;

        $idColumn = $wrongSchema['tables']['car']['columns']['id'];
        $idColumn['primaryKey_notfound'] = $idColumn['primaryKey'];
        unset($idColumn['primaryKey']);
        $wrongSchema['tables']['car']['columns']['id'] = $idColumn;

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Unrecognized option "primaryKey_notfound" under "schema.tables.car.columns.id" (schema : "inline_0")',
            'Wrong property for column'
        );

        // Wrong enum default value
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['car']['columns']['door_count']['default'] = 'notfound';

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Invalid default value "notfound" for enum column "door_count" on table "car" (schema : "inline_0")',
            'Default value for enum column'
        );

        // Wrong size or decimal value
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['car']['columns']['price']['size'] = $wrongSchema['tables']['car']['columns']['price']['decimal'];

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Invalid size value "' . $wrongSchema['tables']['car']['columns']['price']['size'] . '" for column "price" '
                . 'on table "car", the size should be greater than the decimal value "'
                . $wrongSchema['tables']['car']['columns']['price']['size'] . '" (schema : "inline_0")'
            ,
            'Wrong size or decimal value'
        );

        // Wrong default value for boolean
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['certificate']['columns']['is_valid']['default'] = 'not_a_boolean';

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'The default value "not_a_boolean" for boolean column "is_valid" on table "certificate" should be a boolean (schema : "inline_0")',
            'Wrong default value on boolean type'
        );
    }

    /**
     * @test
     */
    public function invalidRelationConfiguration()
    {
        // Wrong relation name
        $wrongSchema = $this->validSchema;

        $relations = $wrongSchema['tables']['car']['relations'];
        $relations['car_db.wheel_notfound'] = $relations['car_db.wheel'];
        unset($relations['car_db.wheel']);
        $wrongSchema['tables']['car']['relations'] = $relations;

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Invalid relation "car_db.wheel_notfound" (schema : "inline_0")',
            'Wrong relation name'
        );

        // Wrong local column
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['car']['relations']['car_db.wheel']['local'] = 'notfound';
        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Invalid local column value "notfound" for relation "car_db.wheel" (schema : "inline_0")',
            'Wrong relation local column'
        );

        // Wrong foreign column
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['car']['relations']['car_db.wheel']['foreign'] = 'notfound';
        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema]),
            'Invalid foreign column value "notfound" for relation "car_db.wheel" (schema : "inline_0")',
            'Wrong relation foreign column'
        );

        // Too much table for the same relation name
        $wrongSchema = $this->validSchema;
        $wrongSchema2 = $this->validSchema;

        $wrongSchema2['database'] = $wrongSchema['database'] . '2';
        $wrongSchema2['namespace'] = $wrongSchema['namespace'] . '2';

        $relations = $wrongSchema2['tables']['car']['relations'];
        $relations['wheel'] = $relations['car_db.wheel'];
        unset($relations['car_db.wheel']);
        $wrongSchema2['tables']['car']['relations'] = $relations;

        $this->schemaHelper->assertSchemaLoadingException(
            new InlineSchemaLoader([$wrongSchema, $wrongSchema2]),
            'Too much table for the relation "wheel", prefix it with the database or use the object namespace (schema : "inline_1")',
            'Wrong relation foreign column'
        );
    }
}
