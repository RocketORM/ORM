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

use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Transformer\SchemaRelationTransformer
 */
class SchemaRelationTransformerTest extends RocketTestCase
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
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid relation "car_company.wheel_notfound" (schema : "inline_0")
     */
    public function invalidRelationNameConfigurationValue()
    {
        // Wrong relation name
        $wrongSchema = self::$validSchema;

        $relations = $wrongSchema['tables']['car']['relations'];
        $relations['car_company.wheel_notfound'] = $relations['car_company.wheel'];
        unset($relations['car_company.wheel']);
        $wrongSchema['tables']['car']['relations'] = $relations;

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid local column value "notfound" for relation "car_company.wheel" (schema : "inline_0")
     */
    public function invalidRelationLocalColumnNameConfigurationValue()
    {
        // Wrong local column
        $wrongSchema = self::$validSchema;

        $wrongSchema['tables']['car']['relations']['car_company.wheel']['local'] = 'notfound';

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid foreign column value "notfound" for relation "car_company.wheel" (schema : "inline_0")
     */
    public function invalidRelationForeignColumnNameConfigurationValue()
    {
        // Wrong local column
        $wrongSchema = self::$validSchema;

        $wrongSchema['tables']['car']['relations']['car_company.wheel']['foreign'] = 'notfound';

        (new InlineSchemaLoader([$wrongSchema]))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Too much table for the relation "wheel", prefix it with the database or use the object namespace (schema : "inline_1")
     */
    public function invalidRelationConfiguration()
    {
        // Too much table for the same relation name
        $wrongSchema = self::$validSchema;
        $wrongSchema2 = self::$validSchema;

        $wrongSchema2['database'] = $wrongSchema['database'] . '2';
        $wrongSchema2['namespace'] = $wrongSchema['namespace'] . '2';

        $relations = $wrongSchema2['tables']['car']['relations'];
        $relations['wheel'] = $relations['car_company.wheel'];
        unset($relations['car_company.wheel']);
        $wrongSchema2['tables']['car']['relations'] = $relations;

        (new InlineSchemaLoader([$wrongSchema, $wrongSchema2]))->load();
    }
}
