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

use Rocket\ORM\Generator\Schema\Relation;
use Rocket\ORM\Generator\Schema\Table;
use Rocket\ORM\Model\Map\TableMap;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Relation
 */
class RelationTest extends RocketTestCase
{
    /**
     * @test
     */
    public function setAndGetLocalTable()
    {
        $relation = $this->createRelation();
        $table = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $relation->setLocalTable($table);

        $this->assertEquals($table, $relation->getLocalTable());
    }

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
        $this->assertEquals($expectedValue, $this->createRelation([
            'type' => $columnType
        ])->getTypeConstantName());
    }

    /**
     * @return array
     */
    public function getTypeConstantNameProvider()
    {
        return [
            ['RELATION_TYPE_MANY_TO_ONE', TableMap::RELATION_TYPE_MANY_TO_ONE],
            ['RELATION_TYPE_ONE_TO_ONE', TableMap::RELATION_TYPE_ONE_TO_ONE],
            ['RELATION_TYPE_MANY_TO_MANY', TableMap::RELATION_TYPE_MANY_TO_MANY],
            ['RELATION_TYPE_ONE_TO_MANY', TableMap::RELATION_TYPE_ONE_TO_MANY]
        ];
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown value "foo" for constant TableMap::RELATION_TYPE_* for relation "fooBar"
     */
    public function getTypeConstantNameUnknownException()
    {
        $relation = $this->createRelation([
            'type' => 'foo',
        ]);

        $relation->getTypeConstantName();
    }

    /**
     * @test
     */
    public function isForeignKey()
    {
        $this->assertTrue($this->createRelation()->isForeignKey());
        $this->assertFalse($this->createRelation([], false)->isForeignKey());
    }

    /**
     * @test
     *
     * @dataProvider getPhpNameProvider
     *
     * @param string $expectedName
     * @param string $relationPhpName
     */
    public function getPhpName($expectedName, $relationPhpName)
    {
        $this->assertEquals($expectedName, $this->createRelation([
            'phpName' => $relationPhpName
        ])->getPhpName(false));

        $this->assertEquals(ucfirst($expectedName), $this->createRelation([
            'phpName' => $relationPhpName
        ])->getPhpName());
    }

    /**
     * @return array
     */
    public function getPhpNameProvider()
    {
        return [
            ['foo', 'Foo'],
            ['foo_bar', 'Foo_bar'],
            ['fooBar', 'FooBar']
        ];
    }

    /**
     * @test
     *
     * @dataProvider isManyProvider
     *
     * @param bool $isMany
     * @param int  $relationType
     */
    public function isMany($isMany, $relationType)
    {
        $this->assertEquals($isMany, $this->createRelation([
            'type' => $relationType
        ])->isMany());
    }

    /**
     * @return array
     */
    public function isManyProvider()
    {
        return [
            [true, TableMap::RELATION_TYPE_MANY_TO_MANY],
            [true, TableMap::RELATION_TYPE_MANY_TO_ONE],
            [false, TableMap::RELATION_TYPE_ONE_TO_MANY],
            [false, TableMap::RELATION_TYPE_ONE_TO_ONE],
            [false, 'foo']
        ];
    }

    /**
     * @test
     */
    public function setAndGetRelatedTable()
    {
        $relation = $this->createRelation();
        $table = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getColumn'])
            ->getMock()
        ;

        $foreignColumn = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Column')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $table
            ->expects($this->once())
            ->method('getColumn')
            ->willReturn($foreignColumn)
        ;

        $relation->setRelatedTable($table);

        $this->assertEquals($table, $relation->getRelatedTable());
        $this->assertEquals($foreignColumn, $relation->foreignColumn);
    }

    /**
     * @test
     *
     * @depends setAndGetLocalTable
     * @depends setAndGetRelatedTable
     */
    public function getRelatedRelation()
    {
        $relation = $this->createRelation();

        /** @var Table|\PHPUnit_Framework_MockObject_MockObject $localTable */
        $localTable = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getNamespace'])
            ->getMock()
        ;

        $localTable
            ->expects($this->exactly(2))
            ->method('getNamespace')
            ->willReturn('Foo\Bar')
        ;

        /** @var Table|\PHPUnit_Framework_MockObject_MockObject $relatedTable */
        $relatedTable = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getNamespace'])
            ->getMock()
        ;

        $relatedRelation = $this->createRelation([
            'local'   => $relation->foreign,
            'foreign' => $relation->local
        ]);

        $relatedRelation->setRelatedTable($localTable);
        $relatedTable->addRelation($relatedRelation);

        $relation->setRelatedTable($relatedTable);
        $relation->setLocalTable($localTable);

        $this->assertEquals($relatedRelation, $relation->getRelatedRelation());
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot retrieve the related relation for between "local_table" and "related_table" (relation name : "fooBar")
     */
    public function getRelatedRelationRelatedRelationNotFoundException()
    {
        $relation = $this->createRelation();

        /** @var Table|\PHPUnit_Framework_MockObject_MockObject $localTable */
        $localTable = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getNamespace'])
            ->getMock()
        ;

        $localTable
            ->expects($this->once())
            ->method('getNamespace')
            ->willReturn('Foo\Bar')
        ;

        $localTable->name = 'local_table';

        /** @var Table|\PHPUnit_Framework_MockObject_MockObject $relatedTable */
        $relatedTable = $this->getMockBuilder('\Rocket\ORM\Generator\Schema\Table')
            ->disableOriginalConstructor()
            ->setMethods(['getNamespace'])
            ->getMock()
        ;

        $relatedTable->name = 'related_table';

        $relation->setRelatedTable($relatedTable);
        $relation->setLocalTable($localTable);

        $relation->getRelatedRelation();
    }

    /**
     * @param array $data
     * @param bool  $isForeignKey
     *
     * @return Relation
     */
    protected function createRelation(array $data = [], $isForeignKey = true)
    {
        return new Relation('foo', array_merge([
            'phpName'  => 'fooBar',
            'local'    => 'local_foo',
            'foreign'  => 'foreign_bar',
            'onDelete' => 'CASCADE',
            'onUpdate' => 'RESTRICT',
            'type'     => TableMap::RELATION_TYPE_ONE_TO_ONE
        ], $data), $isForeignKey);
    }
}
