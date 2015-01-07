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

        $this->schemaDirPath = $this->rootDir . 'resources/schemas';
        $this->validSchema = Yaml::parse($this->schemaDirPath . '/car_schema.yml');
    }


    /**
     * @test
     */
    public function generate()
    {
        $tableMapGenerator = new TableMapGenerator();

        // Double relation generation
        $wrongSchema = $this->validSchema;
        $wrongSchema['tables']['company']['relations']['car_db.wheel'] = [
            'local' => 'id',
            'foreign' => 'company_id'
        ];

        $schemaLoader = new InlineSchemaLoader([$this->rootDir . 'resources/schemas/car_schema.yml' => $wrongSchema]);
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
    }

    /**
     * @test
     */
    public function valuesValidation()
    {
        $this->tableMapHelper->generate($this->schemaHelper->getSchemas());

        /** @var TableMap $tableMap */
        $tableMap = Rocket::getTableMap('\\Fixture\\Car\\Model\\Company');

        // Assert values
        $this->assertTrue('car_db' == $tableMap->getDatabase(), 'Table map database');
        $this->assertTrue('default' == $tableMap->getConnectionName(), 'Table map connection');
        $this->assertTrue('Company' == $tableMap->getClassName(), 'Table map class name');
        $this->assertTrue('company' == $tableMap->getTableName(), 'Table map table name');
        $this->assertTrue('Fixture\\Car\\Model' == $tableMap->getClassNamespace(), 'Table map table namespace');

        // Assert columns
        try {
            $this->assertTrue($tableMap->hasColumn('id'), 'Table map column');
            $this->assertTrue($tableMap->hasColumn('name'), 'Table map column');
            $this->assertNotNull($tableMap->getColumn('id'), 'Table map column');
            $this->assertNotNull($tableMap->getColumn('name'), 'Table map column');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(false, 'Missing column');
        }

        $error = false;
        try {
            $tableMap->getColumn('notfound');
        } catch (\InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error, 'Wrong column name');
        $this->assertFalse($tableMap->hasColumn('notfound'), 'Wrong column name');
        $this->assertCount(2, $tableMap->getColumns(), 'Wrong column count');

        // Assert relations
        try {
            $this->assertNotNull($tableMap->hasRelation('Fixture\\Car\\Model\\Wheel'), 'Table map relation');
            $this->assertNotNull($tableMap->hasRelation('Fixture\\Car\\Model\\Validator'), 'Table map relation');
            $this->assertNotNull($tableMap->getRelation('Fixture\\Car\\Model\\Wheel'), 'Table map relation');
            $this->assertNotNull($tableMap->getRelation('Fixture\\Car\\Model\\Validator'), 'Table map relation');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(false, 'Missing relation');
        }

        $error = false;
        try {
            $tableMap->getRelation('notfound');
        } catch (\InvalidArgumentException $e) {
            $error = true;
        }

        $this->assertTrue($error, 'Wrong relation name');
        $this->assertFalse($tableMap->hasRelation('notfound'), 'Wrong relation name');
        $this->assertCount(2, $tableMap->getRelations(), 'Wrong relation count');

        // Assert primary keys
        $this->assertCount(1, $tableMap->getPrimaryKeys(), 'Wrong primary key count');
    }
}
