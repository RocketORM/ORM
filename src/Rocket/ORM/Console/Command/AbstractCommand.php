<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Console\Command;

use Rocket\ORM\Connection\ConnectionInterface;
use Rocket\ORM\Generator\Config\ConfigLoader;
use Rocket\ORM\Rocket;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class AbstractCommand extends Command
{
    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $configLoader = new ConfigLoader($input->getOption('rocket-config'));
        Rocket::setConfiguration($configLoader->all());
    }

    /**
     * @param InputInterface|null $input
     * @param string|null         $name
     *
     * @return ConnectionInterface
     */
    protected function getConnection(InputInterface $input = null, $name = null)
    {
        if (null == $name) {
            if (null != $input) {
                $name = $input->getOption('connection', 'default');
            }
            else {
                $name = 'default';
            }
        }

        return Rocket::getConnection($name);
    }
}
