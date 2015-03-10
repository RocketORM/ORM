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
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Rocket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketDatabaseCreateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('database:create')
            ->setDescription('Create the database, but not the tables')
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
        $connections = [];
        $pendingDatabaseCount = 0;

        /** @var Schema $schema */
        foreach ($schemas as $schema) {
            if (!Rocket::getConnection($schema->connection)->isDatabaseCreated($schema->database)) {
                $connections[$schema->connection][] = $schema->database;
                ++$pendingDatabaseCount;
            }
        }

        $schemaCount = sizeof($schemas);
        if ($schemaCount == $pendingDatabaseCount) {
            $output->write(sprintf(
                '%d database%s will be created... ',
                $pendingDatabaseCount,
                1 < $pendingDatabaseCount ? 's' : ''
            ));
        } elseif (0 == $pendingDatabaseCount) {
            $output->writeln('<info>All databases are already created.</info>');

            return 0;
        } else {
            $existDatabaseCount = $schemaCount - $pendingDatabaseCount;
            $output->write(sprintf(
                '%d database%s already exist, %d will be created... ',
                $existDatabaseCount,
                1 < $existDatabaseCount ? 's' : '',
                $pendingDatabaseCount
            ));
        }

        try {
            foreach ($connections as $connectionName => $databases) {
                $connection = Rocket::getConnection($connectionName);
                foreach ($databases as $databaseName) {
                    if (!$connection->createDatabase($databaseName)) {
                        throw new \RuntimeException('Cannot create the database "' . $databaseName . '"');
                    }
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<fg=red;options=bold>FAIL</fg=red;options=bold>');

            throw $e;
        }

        $output->writeln('<info>Success</info>');

        return 0;
    }
}
