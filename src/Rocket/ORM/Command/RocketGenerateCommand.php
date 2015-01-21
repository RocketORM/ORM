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
use Rocket\ORM\Generator\Model\Object\ObjectGenerator;
use Rocket\ORM\Generator\Model\TableMap\TableMapGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketGenerateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('rocket:generate')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schemas = $this->getSchemas($this->getSchemaPath());
        $tableMapGenerator = new TableMapGenerator();
        $objectGenerator = new ObjectGenerator();
        $databaseGenerator = new DatabaseGenerator($this->getSqlOutputPath());

        foreach ($schemas as $schema) {
            $tableMapGenerator->generate($schema);
            $objectGenerator->generate($schema);
            $databaseGenerator->generate($schema);
        }
    }
}
