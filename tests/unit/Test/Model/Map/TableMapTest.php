<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model\Map;

use Rocket\ORM\Generator\Model\TableMap\TableMapGenerator;
use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Model\Map\Exception\RelationAlreadyExistsException;
use Rocket\ORM\Model\Map\TableMap;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\Generator\Model\TableMapTestHelper;
use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class TableMapTest extends RocketTestCase
{
    /**
     * @var SchemaTestHelper
     */
    protected $schemaHelper;

    /**
     * @var TableMapTestHelper
     */
    protected $tableMapHelper;

    /**
     * @var array
     */
    protected $validSchema;

    /**
     * @var string
     */
    protected $schemaDirPath;


    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->schemaHelper = $this->getHelper('schema');
        $this->tableMapHelper = $this->getHelper('table_map');

        $this->schemaDirPath = $this->rootDir . '/resources/schemas';
        $this->validSchema = Yaml::parse($this->schemaDirPath . '/car_schema.yml');
    }


    /**
     * @test
     */
    public function generationValidation()
    {
        $tableMapGenerator = new TableMapGenerator();

        // Double relation generation
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['company']['relations']['car_db.wheel'] = [
            'local' => 'id',
            'foreign' => 'company_id'
        ];

        $schemaLoader = new InlineSchemaLoader([$this->rootDir . '/resources/schemas/car_schema.yml' => $wrongSchema]);
        $schemas = $schemaLoader->load();

        $this->assertCount(1, $schemas, 'Schema count');
        $tableMapGenerator->generate($schemas[0]);

        $tableMap = null;
        try {
            /** @var TableMap $tableMap */
            $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Company');
        } catch (RelationAlreadyExistsException $e) {
            // Nothing
        }

        $this->assertNotNull($tableMap, 'Relation already exists');

        // Good generation
        $schemaLoader = new SchemaLoader($this->schemaDirPath, [], new SchemaTransformer());
        $schemas = $schemaLoader->load();

        foreach ($schemas as $schema) {
            $tableMapGenerator->generate($schema);
        }

        // Double relation exception
        $error = false;
        try {
            $tableMap->addRelation('Fixture\\Car\\Model\\Wheel', null, null, null, null, null);
        } catch (RelationAlreadyExistsException $e) {
            $error = true;
        }

        $this->assertTrue($error, 'Double relation exception');

        // Column type constant not found
        $error = false;
        try {
            TableMap::convertColumnTypeToConstant('notfound');
        } catch (\InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error, 'Column type constant exception');

        // Wrong table map model namespace
        $error = null;
        try {
            new TableMapGenerator('\\Rocket\\ORM\\Rocket');
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals('The table map model must implement Rocket\ORM\Model\Map\TableMapInterface', $error);
    }

    /**
     * @test
     */
    public function commonValidation()
    {
        $this->tableMapHelper->generate($this->schemaHelper->getSchemas());

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Company');

        // Assert values
        $this->assertEquals('car_db', $tableMap->getDatabase(), 'Table map database');
        $this->assertEquals('car', $tableMap->getConnectionName(), 'Table map connection');
        $this->assertEquals('Company', $tableMap->getClassName(), 'Table map class name');
        $this->assertEquals('company', $tableMap->getTableName(), 'Table map table name');
        $this->assertEquals('Fixture\\Car\\Model', $tableMap->getClassNamespace(), 'Table map table namespace');

        // Assert counts
        $this->assertCount(2, $tableMap->getColumns(), 'Wrong column count');
        $this->assertCount(1, $tableMap->getPrimaryKeys(), 'Wrong primary key count');
        $this->assertCount(2, $tableMap->getRelations(), 'Wrong relation count');
    }

    /**
     * @test
     */
    public function columnsValidation()
    {
        $this->tableMapHelper->generate($this->schemaHelper->getSchemas());

        // Assert columns
        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Car');

        // car.id
        $this->assertTrue($tableMap->hasColumn('id'));
        $column = $tableMap->getColumn('id');
        $this->assertNotNull($column);
        $this->assertEquals('id', $column['name']);
        $this->assertEquals('id', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_INTEGER, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertNull($column['default']);
        $this->assertTrue($column['required']);

        // car.door_count
        $this->assertTrue($tableMap->hasColumn('door_count'));
        $column = $tableMap->getColumn('door_count');
        $this->assertNotNull($column);
        $this->assertEquals('door_count', $column['name']);
        $this->assertEquals('doorCount', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_ENUM, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNotNull($column['values']);
        $this->assertEquals(3, $column['values'][0]);
        $this->assertEquals(5, $column['values'][1]);
        $this->assertEquals(0, $column['default']); // by key
        $this->assertTrue($column['required']);

        // car.wheel_unique_name
        $this->assertTrue($tableMap->hasColumn('wheel_unique_name'));
        $column = $tableMap->getColumn('wheel_unique_name');
        $this->assertNotNull($column);
        $this->assertEquals('wheel_unique_name', $column['name']);
        $this->assertEquals('wheelName', $column['phpName']); // custom
        $this->assertEquals(TableMap::COLUMN_TYPE_STRING, $column['type']);
        $this->assertEquals(255, $column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertNull($column['default']);
        $this->assertTrue($column['required']);

        // car.price
        $this->assertTrue($tableMap->hasColumn('price'));
        $column = $tableMap->getColumn('price');
        $this->assertNotNull($column);
        $this->assertEquals('price', $column['name']);
        $this->assertEquals('price', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_DOUBLE, $column['type']);
        $this->assertEquals(4, $column['size']);
        $this->assertEquals(2, $column['decimal']);
        $this->assertNull($column['values']);
        $this->assertNull($column['default']);
        $this->assertFalse($column['required']);

        // car.released_at
        $this->assertTrue($tableMap->hasColumn('released_at'));
        $column = $tableMap->getColumn('released_at');
        $this->assertNotNull($column);
        $this->assertEquals('released_at', $column['name']);
        $this->assertEquals('releasedAt', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_DATE, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertNull($column['default']);
        $this->assertFalse($column['required']);

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Certificate');

        // certificate.created_at
        $this->assertTrue($tableMap->hasColumn('created_at'));
        $column = $tableMap->getColumn('created_at');
        $this->assertNotNull($column);
        $this->assertEquals('created_at', $column['name']);
        $this->assertEquals('createdAt', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_DATETIME, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertNull($column['default']);
        $this->assertTrue($column['required']);

        // certificate.is_valid
        $this->assertTrue($tableMap->hasColumn('is_valid'));
        $column = $tableMap->getColumn('is_valid');
        $this->assertNotNull($column);
        $this->assertEquals('is_valid', $column['name']);
        $this->assertEquals('isValid', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_BOOLEAN, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertFalse($column['default']);
        $this->assertTrue($column['required']);

        // certificate.precision
        $this->assertTrue($tableMap->hasColumn('precision'));
        $column = $tableMap->getColumn('precision');
        $this->assertNotNull($column);
        $this->assertEquals('precision', $column['name']);
        $this->assertEquals('precision', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_FLOAT, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertEquals('10.5', $column['default']);
        $this->assertFalse($column['required']);

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Approval');

        // approval.comment
        $this->assertTrue($tableMap->hasColumn('comment'));
        $column = $tableMap->getColumn('comment');
        $this->assertNotNull($column);
        $this->assertEquals('comment', $column['name']);
        $this->assertEquals('comment', $column['phpName']);
        $this->assertEquals(TableMap::COLUMN_TYPE_TEXT, $column['type']);
        $this->assertNull($column['size']);
        $this->assertNull($column['decimal']);
        $this->assertNull($column['values']);
        $this->assertEquals('Put a comment about the certificate', $column['default']);
        $this->assertFalse($column['required']);

        // Wrong column
        $error = false;
        try {
            $tableMap->getColumn('notfound');
        } catch (\InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error, 'Wrong column name');
        $this->assertFalse($tableMap->hasColumn('notfound'), 'Wrong column name');
    }

    /**
     * @test
     */
    public function relationsValidation()
    {
        $this->tableMapHelper->generate($this->schemaHelper->getSchemas());

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Wheel');

        // Assert specified relation (one to many)
        $this->assertTrue($tableMap->hasRelation('Fixture\\Car\\Model\\Company'));
        $relation = $tableMap->getRelation('Fixture\\Car\\Model\\Company');
        $this->assertEquals('Fixture\\Car\\Model\\Company', $relation['namespace']);
        $this->assertEquals('Company', $relation['phpName']);
        $this->assertEquals(TableMap::RELATION_TYPE_ONE_TO_MANY, $relation['type']);
        $this->assertEquals('company_id', $relation['local']);
        $this->assertEquals('id', $relation['foreign']);

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Company');

        // Assert non specified relation (many to one)
        $this->assertTrue($tableMap->hasRelation('Fixture\\Car\\Model\\Wheel'));
        $relation = $tableMap->getRelation('Fixture\\Car\\Model\\Wheel');
        $this->assertEquals('Fixture\\Car\\Model\\Wheel', $relation['namespace']);
        $this->assertEquals('Wheels', $relation['phpName']);
        $this->assertEquals(TableMap::RELATION_TYPE_MANY_TO_ONE, $relation['type']);
        $this->assertEquals('id', $relation['local']);
        $this->assertEquals('company_id', $relation['foreign']);

        // Assert specified relation (one to one)
        $this->assertTrue($tableMap->hasRelation('Fixture\\Car\\Model\\Validator'));
        $relation = $tableMap->getRelation('Fixture\\Car\\Model\\Validator');
        $this->assertEquals('Fixture\\Car\\Model\\Validator', $relation['namespace']);
        $this->assertEquals('Validator', $relation['phpName']);
        $this->assertEquals(TableMap::RELATION_TYPE_ONE_TO_ONE, $relation['type']);
        $this->assertEquals('id', $relation['local']);
        $this->assertEquals('company_id', $relation['foreign']);

        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Validator');

        // Assert non specified relation (one to one)
        $this->assertTrue($tableMap->hasRelation('Fixture\\Car\\Model\\Company'));
        $relation = $tableMap->getRelation('Fixture\\Car\\Model\\Company');
        $this->assertEquals('Fixture\\Car\\Model\\Company', $relation['namespace']);
        $this->assertEquals('Company', $relation['phpName']);
        $this->assertEquals(TableMap::RELATION_TYPE_ONE_TO_ONE, $relation['type']);
        $this->assertEquals('company_id', $relation['local']);
        $this->assertEquals('id', $relation['foreign']);
    }
}
