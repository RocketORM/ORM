<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Generator\Schema\Configuration;

use Rocket\ORM\Generator\Schema\Configuration\SchemaConfiguration;
use Rocket\ORM\Test\RocketTestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Generator\Schema\Configuration\SchemaConfiguration
 */
class SchemaConfigurationTest extends RocketTestCase
{
    /**
     * @test
     */
    public function classInstanceOf()
    {
        $this->assertTrue(new SchemaConfiguration() instanceof ConfigurationInterface);
    }

    /**
     * @test
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = (new SchemaConfiguration())->getConfigTreeBuilder();

        $this->assertNotNull($treeBuilder);
        $this->assertTrue($treeBuilder instanceof TreeBuilder);
    }
}
