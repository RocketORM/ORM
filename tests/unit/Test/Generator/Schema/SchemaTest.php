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

use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Table;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Schema
 */
class SchemaTest extends RocketTestCase
{
    /**
     * @test
     */
    public function getTables()
    {
        $tables = $this->createSchema()->getTables();

        $this->assertInternalType('array', $tables);
        $this->assertCount(2, $tables);

        $this->assertEquals('foo_table', $tables[0]->name);
        $this->assertEquals('bar_table', $tables[1]->name);
    }

    /**
     * @test
     *
     * @dataProvider findTablesProvider
     *
     * @param Table  $expectedTable
     * @param string $tablePattern
     */
    public function findTables(Table $expectedTable, $tablePattern)
    {
        $schema = $this->createSchema();

        $foundTables = $schema->findTables($tablePattern);
        $this->assertInternalType('array', $foundTables);
        $this->assertCount(1, $foundTables);
        $this->assertEquals($expectedTable, $foundTables[0]);
    }

    /**
     * @return array
     */
    public function findTablesProvider()
    {
        list($foo, $bar) = $this->createSchema()->getTables();

        return [
            [$foo, 'Foo\\Bar\\Foo'],
            [$bar, 'Foo\\Bar\\Bar'],
            [$foo, 'foo_table'],
            [$bar, 'bar_table'],
            [$foo, 'foo_bar.foo_table'],
            [$bar, 'foo_bar.bar_table']
        ];
    }

    /**
     * @param array $data
     *
     * @return Schema
     */
    public function createSchema(array $data = [])
    {
        return new Schema(array_merge([
            'connection' => 'foo',
            'namespace'  => 'Foo\Bar',
            'directory'  => 'bar',
            'database'   => 'foo_bar',
            'tables'     => [
                'foo_table' => [
                    'phpName'   => 'Foo',
                    'type'      => 'InnoDB',
                    'columns'   => [],
                    'relations' => []
                ],
                'bar_table' => [
                    'phpName'   => 'Bar',
                    'type'      => 'InnoDB',
                    'columns'   => [],
                    'relations' => []
                ]
            ]
        ], $data));
    }
}
