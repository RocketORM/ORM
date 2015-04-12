<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Transformer;

use Rocket\ORM\Generator\Schema\Table;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
interface SchemaRelationTransformerInterface
{
    /**
     * @param Table $table
     * @param array $schemas
     *
     * @return void
     */
    public function transform(Table $table, array $schemas);

    /**
     * @param Table $table
     *
     * @return void
     */
    public function transformRelatedRelations(Table $table);
}
