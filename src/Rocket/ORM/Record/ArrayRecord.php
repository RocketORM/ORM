<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ArrayRecord extends \ArrayObject
{
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @param array  $values
     * @param string $modelNamespace
     */
    public function __construct($values, $modelNamespace)
    {
        $this->modelNamespace = $modelNamespace;

        parent::__construct($values, \ArrayObject::STD_PROP_LIST);
    }

    /**
     * @return RecordInterface
     */
    public function hydrate()
    {
        /** @var RecordInterface $model */
        $model = new $this->modelNamespace;
        $model->hydrate($this->getArrayCopy());

        return $model;
    }
}
