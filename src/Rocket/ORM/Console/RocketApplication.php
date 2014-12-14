<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Console;

use Rocket\ORM\Rocket;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketApplication extends Application
{
    protected $config;

    /**
     * @param string $name
     * @param string $version
     */
    public function __construct($name = 'Rocket', $version = Rocket::VERSION)
    {
        parent::__construct($name, $version);
    }

    /**
     * @param Finder $finder
     *
     * @return array
     */
    public function resolveCommands(Finder $finder)
    {
        $commands = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $reflectionClass = new \ReflectionClass('\\Rocket\\ORM\\Command\\' . $file->getBasename('.php'));
            if (!$reflectionClass->isSubclassOf('\\Symfony\\Component\\Console\\Command\\Command') || $reflectionClass->isAbstract()) {
                continue;
            }

            $className = $reflectionClass->getName();
            $commands[] = new $className;
        }

        return $commands;
    }

    /**
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption('--rocket-config', '-rc', InputOption::VALUE_OPTIONAL, 'The Rocket configuration file path', getenv('ROCKET_CONFIG') ?: null));
        $definition->addOption(new InputOption('--connection', '-con', InputOption::VALUE_OPTIONAL, 'The default database connection to use'));

        return $definition;
    }
}
