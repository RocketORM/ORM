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

use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ThrowableSchemaTransformer implements SchemaTransformerInterface
{
    /**
     * Transform schema data as array into Schema model
     *
     * @param array  $schemaData The schema data
     * @param string $path       The absolute path to the schema file
     *
     * @return Schema
     */
    public function transform(array $schemaData, $path)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }

    /**
     * @param array $rawTables
     *
     * @return void
     */
    public function transformTables(array $rawTables)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }

    /**
     * @param array $rawColumns
     *
     * @return void
     */
    public function transformColumns(array $rawColumns)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }

    /**
     * @param array $columns
     *
     * @return void
     */
    public function transformPrimaryKeys(array $columns)
    {
        throw new InvalidConfigurationException('Houston, we have a problem');
    }
}
