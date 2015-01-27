<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Command;

use Rocket\ORM\Console\Command\AbstractCommand;
use Rocket\ORM\Console\RocketApplication;
use Rocket\ORM\Generator\Database\Table\DatabaseTableGenerator;
use Rocket\ORM\Generator\Schema\Schema;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketDatabaseInsertCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('database:insert')
            ->setDescription('Insert all tables in databases. Databases should be created first.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemas = $this->getSchemas($this->getSchemaPath());

        // Generating SQL
        $app = new RocketApplication();
        $command = $this->getRocketBuildSqlCommand();
        $app->add($command);
        $params = [
            'command' => $command->getName(),
        ];

        if (OutputInterface::VERBOSITY_DEBUG > $output->getVerbosity()) {
            $params['-q'] = true;
        }

        $app->setAutoExit(false);
        $app->run(new ArrayInput($params), new ConsoleOutput());
        $this->debug($output, '');

        $tableGenerator = new DatabaseTableGenerator($this->getSqlOutputPath());

        /** @var Schema $schema */
        foreach ($schemas as $schema) {
            $this->verbose($output, 'Inserting tables for database "' . $schema->database . '"... ', false);
            try {
                $tableGenerator->generate($schema);
            } catch (\Exception $e) {
                $this->verbose($output, '<error>FAIL</error>');

                throw $e;
            }

            $this->verbose($output, '<info>OK</info>');
        }

        $output->writeln('<info>All tables has been inserted</info>');

        return 0;
    }

    /**
     * @return AbstractCommand
     */
    protected function getRocketBuildSqlCommand()
    {
        return new RocketGenerateSqlCommand();
    }
}
