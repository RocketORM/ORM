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
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;
use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Loader\SchemaLoader
 * @covers \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
 */
class SchemaLoaderTest extends RocketTestCase
{
    use SchemaTestHelper;

    /**
     * @var string
     */
    protected static $schemaDirPath;

    /**
     * @var string
     */
    protected static $validSchema;


    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$schemaDirPath = self::$rootDir . '/resources/schemas';
        self::$validSchema   = Yaml::parse(self::$schemaDirPath . '/car_schema.yml');
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\SchemaNotFoundException
     * @expectedExceptionMessageRegExp /Schema not found in path "(.+)Schema\/Loader"/
     *
     * @covers \Rocket\ORM\Generator\Schema\Loader\Exception\SchemaNotFoundException
     */
    public function schemaNotFoundException()
    {
        (new SchemaLoader(__DIR__, [], new SchemaTransformer()))->load();
    }

    /**
     * @test
     */
    public function load()
    {
        // Format directory path
        $validSchema = self::$validSchema;
        $validSchema['directory'] = '../Model/';
        $schemaLoader = new InlineSchemaLoader([$validSchema]);
        $schemas = $schemaLoader->load();

        $this->assertCount(1, $schemas, 'Schemas count');
        $this->assertTrue('/../Model' == $schemas[0]->relativeDirectory, 'Model output directory format');

        // Good load
        $schemaLoader = new SchemaLoader(self::$schemaDirPath, [], new SchemaTransformer());
        $schemas = $schemaLoader->load();

        $finder = new Finder();
        $finder->files()
            ->in(self::$schemaDirPath)
        ;

        $this->assertCount($finder->count(), $schemas, 'Schemas count');
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Houston, we have a problem
     */
    public function validateSchemaTransformerException()
    {
        $schemaTransformer = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer')
            ->setMethods(['transform'])
            ->getMock()
        ;

        /** @var SchemaTransformerInterface|\PHPUnit_Framework_MockObject_MockObject $schemaTransformer */
        $schemaTransformer
            ->expects($this->once())
            ->method('transform')
            ->willThrowException(new InvalidConfigurationException('Houston, we have a problem'))
        ;

        (new InlineSchemaLoader([self::$validSchema], $schemaTransformer))->load();
    }

    /**
     * @test
     *
     * @expectedException \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Houston, we have a problem
     */
    public function validateSchemaTransformerRelationException()
    {
        $schemaTransformer = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer')
            ->setMethods(['transformRelations'])
            ->getMock()
        ;

        /** @var SchemaTransformerInterface|\PHPUnit_Framework_MockObject_MockObject $schemaTransformer */
        $schemaTransformer
            ->expects($this->once())
            ->method('transformRelations')
            ->willThrowException(new InvalidConfigurationException('Houston, we have a problem'))
        ;

        (new InlineSchemaLoader([self::$validSchema], $schemaTransformer))->load();
    }
}
