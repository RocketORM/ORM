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
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer;

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
     * @param array $schemas
     */
    public function __construct(array $schemas)
    {
        $this->schemas = $schemas;

        parent::__construct(null, [], new SchemaTransformer());
    }

    /**
     * @return array
     *
     * @throws \Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException
     */
    public function load()
    {
        $schemas = [];
        foreach ($this->schemas as $i => $schema) {
            $schemas['inline_' . $i] = $schema;
        }

        return $this->validate($schemas);
    }
}
