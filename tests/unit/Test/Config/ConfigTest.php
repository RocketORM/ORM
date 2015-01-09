<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Test\Config;

use Rocket\ORM\Config\ConfigLoader;
use Rocket\ORM\Config\Exception\ConfigurationFileNotFoundException;
use Rocket\ORM\Test\RocketTestCase;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConfigTest extends RocketTestCase
{
    /**
     * @test
     */
    public function loadingValidation()
    {
        // Good load
        try {
            $loader = new ConfigLoader($this->rootDir . '/resources/config/rocket.yml');
            $config = $loader->all();

            $this->assertTrue(is_array($config));
        } catch (\Exception $e) {
            $this->assertTrue(false, 'Cannot load configuration : ' . $e->getMessage());
        }

        // Wrong file
        $error = null;
        try {
            new ConfigLoader($this->rootDir . '/resources/config/not_found');
        } catch (ConfigurationFileNotFoundException $e) {
            $error = $e->getMessage();
        }

        $this->assertTrue(0 === strpos($error, 'The rocket configuration file is not found in the selected folder'));

        // No specified path, and file not found
        chdir($this->rootDir);
        $error = null;
        try {
            new ConfigLoader();
        } catch (ConfigurationFileNotFoundException $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals(
            'The rocket configuration file is not found. Please create a new one into your root folder or in a folder named "/config" or "/configs".',
            $error
        );

        // No specified path, and file found
        chdir($this->rootDir . '/resources');
        $error = null;
        try {
            new ConfigLoader();
        } catch (ConfigurationFileNotFoundException $e) {
            $error = $e->getMessage();
        }

        $this->assertNull($error);
    }
}
