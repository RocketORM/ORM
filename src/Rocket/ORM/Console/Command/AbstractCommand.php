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
use \Rocket\ORM\Config\ConfigLoader;
use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
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

    /**
     * @param string|array $path
     * @param string|array $exclude
     *
     * @return array
     */
    protected function getSchemas($path, $exclude = [])
    {
        $schemaLoader = new SchemaLoader($path, $exclude, Rocket::getConfiguration('generator.loader'));

        return $schemaLoader->load();
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     * @param bool            $newLine
     * @param int             $verbosity
     */
    private function write(OutputInterface $output, $message, $newLine = true, $verbosity = OutputInterface::VERBOSITY_VERBOSE)
    {
        if ($verbosity <= $output->getVerbosity()) {
            $output->write($message);
            if ($newLine) {
                $output->writeln('');
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     * @param bool            $newLine
     */
    protected function debug(OutputInterface $output, $message, $newLine = true)
    {
        $this->write($output, $message, $newLine, OutputInterface::VERBOSITY_DEBUG);
    }

    /**
     * @param OutputInterface $output
     * @param string          $message
     * @param bool            $newLine
     */
    protected function verbose(OutputInterface $output, $message, $newLine = true)
    {
        $this->write($output, $message, $newLine, OutputInterface::VERBOSITY_VERBOSE);
    }

    /**
     * @return string
     */
    protected function getSchemaPath()
    {
        return __DIR__ . '/../../../../../fixtures/schemas';
    }

    /**
     * @return string
     */
    protected function getSqlOutputPath()
    {
        return __DIR__ . '/../../../../../fixtures/sql';
    }
}
