<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Generator\Schema\Loader;

use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class InlineSchemaLoader extends SchemaLoader
{
    /**
     * @var array
     */
    protected $schemas;


    /**
     * @param array                      $schemas
     * @param SchemaTransformerInterface $schemaTransformer
     */
    public function __construct(array $schemas, SchemaTransformerInterface $schemaTransformer = null)
    {
        $this->schemas = $schemas;

        if (null == $schemaTransformer) {
            $schemaTransformer = new SchemaTransformer();
        }

        parent::__construct(null, [], $schemaTransformer);
    }

    /**
     * @return array|Schema[]
     *
     * @throws \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     */
    public function load()
    {
        $schemas = [];
        foreach ($this->schemas as $i => $schema) {
            if (is_int($i)) {
                $schemas['inline_' . $i] = $schema;
            } else {
                $schemas[$i] = $schema;
            }
        }

        return $this->validate($schemas);
    }
}
