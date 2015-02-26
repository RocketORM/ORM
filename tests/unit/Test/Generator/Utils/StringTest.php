<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Generator\Utils;

use Rocket\ORM\Generator\Utils\String;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Utils\String
 */
class StringTest extends RocketTestCase
{
    /**
     * @test
     *
     * @dataProvider getData
     *
     * @param string $expectedUpper
     * @param string $expectedLower
     * @param string $testValue
     */
    public function camelize($expectedUpper, $expectedLower, $testValue)
    {
        $this->assertEquals($expectedUpper, String::camelize($testValue));
        $this->assertEquals($expectedLower, String::camelize($testValue, false));
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            ['FooBar', 'fooBar', 'foo_bar'],
            ['FooBarFoo', 'fooBarFoo', 'foo_barFoo'],
            ['FooBar', 'fooBar', 'foo.bar'],
            ['FooBarFoo', 'fooBarFoo', 'foo.barFoo'],
            ['FooBar', 'fooBar', 'foo\bar'],
            ['FooBarFoo', 'fooBarFoo', 'foo\barFoo'],
            ['FooBar', 'fooBar', 'foo bar'],
            ['FooBarFoo', 'fooBarFoo', 'foo barFoo']
        ];
    }
}
