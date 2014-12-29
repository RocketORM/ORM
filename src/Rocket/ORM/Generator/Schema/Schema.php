<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema;

use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class Schema implements SchemaInterface
{
    /**
     * @var array
     */
    protected $root;

    /**
     * @var array
     */
    protected $tables;


    /**
     * @param array                      $schema
     * @param string                     $path
     * @param SchemaTransformerInterface $transformer
     */
    public function __construct(array $schema, $path, SchemaTransformerInterface $transformer)
    {
        $this->root   = $transformer->transformRoot($schema, $path);
        $this->tables = $transformer->transformTables($schema['tables']);
    }

    /**
     * @return array
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }
}
