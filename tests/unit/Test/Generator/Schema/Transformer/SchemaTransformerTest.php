<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Generator\Schema\Transformer;

use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer
 */
class SchemaTransformerTest extends RocketTestCase
{
    /**
     * @var array
     */
    protected static $validSchema;


    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$validSchema = Yaml::parse(self::$rootDir . '/resources/schemas' . '/car_schema.yml');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The schema model ("\Rocket\ORM\Rocket") model must extend "\Rocket\ORM\Generator\Schema\Schema"
     */
    public function wrongSchemaModelClassException()
    {
        new SchemaTransformer([
            'schema' => ['class' => '\Rocket\ORM\Rocket']
        ], [
            'schema' => ['class' => '\Rocket\ORM\Generator\Schema\Schema']
        ]);
    }

    /**
     * @test
     */
    public function constructor()
    {
        // Custom schema model class
        $this->assertNotNull(
            new SchemaTransformer([
                'schema' => ['class' => '\Rocket\ORM\Test\Generator\Schema\Schema']
            ], [
                'schema' => ['class' => '\Rocket\ORM\Generator\Schema\Schema']
            ]),
            'Custom schema model class'
        );
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Unrecognized option "primaryKey_notfound" under "schema.tables.car.columns.id" (schema : "inline_0")
     */
    public function unrecognizedConfigurationOptionException()
    {
        // Wrong column name
        $wrongSchema = self::$validSchema;

        $idColumn = $wrongSchema['tables']['car']['columns']['id'];
        $idColumn['primaryKey_notfound'] = $idColumn['primaryKey'];
        unset($idColumn['primaryKey']);
        $wrongSchema['tables']['car']['columns']['id'] = $idColumn;

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid default value "notfound" for enum column "door_count" on table "car" (schema : "inline_0")
     */
    public function invalidDefaultConfigurationValueException()
    {
        // Wrong enum default value
        $wrongSchema = self::$validSchema;

        $wrongSchema['tables']['car']['columns']['door_count']['default'] = 'notfound';

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /Invalid size value "\d+" for column "price" on table "car", the size should be greater than the decimal value "\d+" \(schema : "inline_0"\)/
     */
    public function invalidSizeConfigurationValueException()
    {
        // Wrong enum default value
        $wrongSchema = self::$validSchema;

        $wrongSchema['tables']['car']['columns']['price']['size'] = $wrongSchema['tables']['car']['columns']['price']['decimal'];

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The default value "not_a_boolean" for boolean column "is_valid" on table "certificate" should be a boolean (schema : "inline_0")
     */
    public function invalidDefaultBooleanConfigurationValueException()
    {
        // Wrong default value for boolean
        $wrongSchema = self::$validSchema;

        $wrongSchema['tables']['certificate']['columns']['is_valid']['default'] = 'not_a_boolean';

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     */
    public function formatDirectory()
    {
        // Add prefix
        $validSchemaWithoutSlashPrefix = self::$validSchema;
        $validSchemaWithoutSlashPrefix['directory'] = substr($validSchemaWithoutSlashPrefix['directory'], 1);

        $schemas = (new InlineSchemaLoader([$validSchemaWithoutSlashPrefix]))->load();
        $this->assertEquals('/../fixtures/Fixture/Car/Model', $schemas[0]->relativeDirectory);

        // Delete suffix
        $validSchemaWithSlashSuffix = self::$validSchema;
        $validSchemaWithSlashSuffix['directory'] = $validSchemaWithSlashSuffix['directory'] . '/';

        $schemas = (new InlineSchemaLoader([$validSchemaWithSlashSuffix]))->load();
        $this->assertEquals('/../fixtures/Fixture/Car/Model', $schemas[0]->relativeDirectory);
    }
}
