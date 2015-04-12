<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Generator\Schema\Transformer;

use Rocket\ORM\Generator\Schema\Table;
use Rocket\ORM\Generator\Schema\Transformer\SchemaRelationTransformerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ThrowableSchemaRelationTransformer implements SchemaRelationTransformerInterface
{
    /**
     * @param Table $table
     * @param array $schemas
     *
     * @return void
     */
    public function transform(Table $table, array $schemas)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }

    /**
     * @param Table $table
     *
     * @return void
     */
    public function transformRelatedRelations(Table $table)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }

}
