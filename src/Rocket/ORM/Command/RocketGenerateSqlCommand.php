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
use Rocket\ORM\Generator\Database\DatabaseGenerator;
use Rocket\ORM\Generator\Schema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketGenerateSqlCommand extends AbstractCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('rocket:generate:sql')
            ->setDescription('Generate SQL file for all schemas')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemas = $this->getSchemas($this->getSchemaPath());
        $output->write('Generating ' . sizeof($schemas) . ' SQL schemas files... ');
        $this->verbose($output, '');

        try {
            $databaseGenerator = new DatabaseGenerator($this->getSqlOutputPath());
            /** @var Schema $schema */
            foreach ($schemas as $schema) {
                $this->verbose($output, sprintf(' > "%s.%s", with %d table%s... ',
                    $schema->connection,
                    $schema->database,
                    sizeof($schema->getTables()),
                    1 < sizeof($schema->getTables()) ? 's' : ''
                ), false);

                $databaseGenerator->generate($schema);

                $this->verbose($output, '<info>OK</info>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>FAIL</error>');

            throw $e;
        }

        $output->writeln('<info>Success</info>');
        $output->writeln('Generated files are located in <options=underscore>"' . $this->getSqlOutputPath() . '"</options=underscore>');

        return 0;
    }
}
