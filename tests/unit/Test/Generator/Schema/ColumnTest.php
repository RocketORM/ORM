<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Generator\Schema;

use Rocket\ORM\Generator\Schema\Column;
use Rocket\ORM\Record\Map\TableMap;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Column
 */
class ColumnTest extends RocketTestCase
{
    /**
     * @test
     *
     * @dataProvider getTypeConstantNameProvider
     *
     * @param string $expectedValue
     * @param string $columnType
     */
    public function getTypeConstantName($expectedValue, $columnType)
    {
        $this->assertEquals($expectedValue, $this->createColumn([
            'type' => $columnType
        ])->getTypeConstantName());
    }

    /**
     * @return array
     */
    public function getTypeConstantNameProvider()
    {
        return [
            ['COLUMN_TYPE_STRING', TableMap::COLUMN_TYPE_STRING],
            ['COLUMN_TYPE_ENUM', TableMap::COLUMN_TYPE_ENUM],
            ['COLUMN_TYPE_BOOLEAN', TableMap::COLUMN_TYPE_BOOLEAN],
            ['COLUMN_TYPE_DATE', TableMap::COLUMN_TYPE_DATE],
            ['COLUMN_TYPE_DATETIME', TableMap::COLUMN_TYPE_DATETIME],
            ['COLUMN_TYPE_DOUBLE', TableMap::COLUMN_TYPE_DOUBLE],
            ['COLUMN_TYPE_FLOAT', TableMap::COLUMN_TYPE_FLOAT],
            ['COLUMN_TYPE_INTEGER', TableMap::COLUMN_TYPE_INTEGER],
            ['COLUMN_TYPE_TEXT', TableMap::COLUMN_TYPE_TEXT]
        ];
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown value "bar" for constant TableMap::COLUMN_TYPE_* for column "foo"
     */
    public function getTypeConstantNameWrongException()
    {
        $column = $this->createColumn([
            'type' => 'bar',
        ]);

        $column->getTypeConstantName();
    }

    /**
     * @test
     */
    public function getDefault()
    {
        $column = $this->createColumn([
            'size'       => 1,
            'type'       => TableMap::COLUMN_TYPE_ENUM,
            'primaryKey' => false,
            'default'    => 'bar',
            'values'     => [
                'foo',
                'bar'
            ]
        ]);

        $this->assertEquals(1, $column->getDefault());
        $this->assertEquals('bar', $column->getDefault(true));
        $this->assertEquals('default', $this->createColumn()->getDefault());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage The default value for column "foo" is not found
     */
    public function getDefaultUnknownDefaultValueException()
    {
        $column = $this->createColumn([
            'size'       => 1,
            'type'       => TableMap::COLUMN_TYPE_ENUM,
            'primaryKey' => false,
            'default'    => 'foobar',
            'values'     => [
                'foo',
                'bar'
            ]
        ]);

        $column->getDefault();
    }

    /**
     * @test
     *
     * @dataProvider getTypeAsPhpDocProvider
     *
     * @param string $expectedType
     * @param string $columnType
     */
    public function getTypeAsPhpDoc($expectedType, $columnType)
    {
        $this->assertEquals($expectedType, $this->createColumn([
            'type' => $columnType
        ])->getTypeAsPhpDoc());
    }

    /**
     * @return array
     */
    public function getTypeAsPhpDocProvider()
    {
        return [
            ['string', TableMap::COLUMN_TYPE_STRING],
            ['int', TableMap::COLUMN_TYPE_ENUM],
            ['bool', TableMap::COLUMN_TYPE_BOOLEAN],
            ['\DateTime', TableMap::COLUMN_TYPE_DATE],
            ['\DateTime', TableMap::COLUMN_TYPE_DATETIME],
            ['double', TableMap::COLUMN_TYPE_DOUBLE],
            ['float', TableMap::COLUMN_TYPE_FLOAT],
            ['int', TableMap::COLUMN_TYPE_INTEGER],
            ['string', TableMap::COLUMN_TYPE_TEXT],
            ['mixed', 'unknown']
        ];
    }

    /**
     * @test
     *
     * @depends getTypeAsPhpDoc
     */
    public function getAttributePhpDoc()
    {
        // Without description
        $this->assertEquals('/**' . PHP_EOL .
'     * @var string' . PHP_EOL .
'     */', $this->createColumn([
            'description' => null
        ])->getAttributePhpDoc());

        // With description
        $this->assertEquals('/**' . PHP_EOL .
'     * description' . PHP_EOL .
'     * ' . PHP_EOL .
'     * @var string' . PHP_EOL .
'     */', $this->createColumn()->getAttributePhpDoc());
    }

    /**
     * @test
     *
     * @dataProvider getPhpNameProvider
     *
     * @param string $expectedName
     * @param string $columnPhpName
     */
    public function getPhpName($expectedName, $columnPhpName)
    {
        $this->assertEquals($expectedName, $this->createColumn([
            'phpName' => $columnPhpName
        ])->getPhpName());

        $this->assertEquals(ucfirst($expectedName), $this->createColumn([
            'phpName' => $columnPhpName
        ])->getPhpName(true));
    }

    /**
     * @return array
     */
    public function getPhpNameProvider()
    {
        return [
            ['foo', 'foo'],
            ['foo_bar', 'foo_bar'],
            ['fooBar', 'fooBar']
        ];
    }

    /**
     * @test
     *
     * @dataProvider getTypeAsStringProvider
     *
     * @param string $expectedType
     * @param string $columnType
     */
    public function getTypeAsString($expectedType, $columnType)
    {
        $this->assertEquals($expectedType, $this->createColumn([
            'type' => $columnType
        ])->getTypeAsString());
    }

    /**
     * @return array
     */
    public function getTypeAsStringProvider()
    {
        return [
            ['string', TableMap::COLUMN_TYPE_STRING],
            ['text', TableMap::COLUMN_TYPE_TEXT],
            ['integer', TableMap::COLUMN_TYPE_INTEGER],
            ['enum', TableMap::COLUMN_TYPE_ENUM],
            ['float', TableMap::COLUMN_TYPE_FLOAT],
            ['double', TableMap::COLUMN_TYPE_DOUBLE],
            ['boolean', TableMap::COLUMN_TYPE_BOOLEAN],
            ['date', TableMap::COLUMN_TYPE_DATE],
            ['datetime', TableMap::COLUMN_TYPE_DATETIME]
        ];
    }

    /**
     * @test
     */
    public function setAndGetTable()
    {
        $column = $this->createColumn();
        $table = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $column->setTable($table);

        $this->assertEquals($table, $column->getTable());
    }

    /**
     * @param array $data
     *
     * @return Column
     */
    protected function createColumn(array $data = [])
    {
        return new Column('foo', array_merge([
            'phpName'       => 'phpName',
            'size'          => 255,
            'decimal'       => 0,
            'type'          => TableMap::COLUMN_TYPE_STRING,
            'default'       => 'default',
            'required'      => true,
            'primaryKey'    => true,
            'autoIncrement' => false,
            'values'        => null,
            'description'   => 'description'
        ], $data));
    }
}
