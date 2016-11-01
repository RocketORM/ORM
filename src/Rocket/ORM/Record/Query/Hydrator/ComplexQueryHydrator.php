<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Record\Query\Hydrator;

use Rocket\ORM\Record\ArrayRecord;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ComplexQueryHydrator implements QueryHydratorInterface
{
    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array|\Rocket\ORM\Record\ArrayRecord[]
     */
    protected $objects;

    /**
     * @var array|\Rocket\ORM\Record\ArrayRecord[]
     */
    protected $objectsByAlias;

    /**
     * @var array|ArrayRecord[]
     */
    protected $objectsByAliasByRow;


    /**
     * @param string $modelNamespace
     * @param string $alias
     * @param array  $joins
     */
    public function __construct($modelNamespace, $alias, array $joins)
    {
        $this->modelNamespace = $modelNamespace;
        $this->alias = $alias;
        $this-> joins = $joins;

        // The main objects array, returning at the end
        $this->objects = [];

        // An array of objects indexed by the query alias, to know if the object has been already
        // instantiated and avoid multiple instantiations
        $this->objectsByAlias = [];

        // An array of objects indexed by query alias by row (cleared after each loop),
        // to know where the relation object should be placed
        $this->objectsByAliasByRow = [];
    }

    /**
     * @inheritdoc
     */
    public function hydrate(\PDOStatement $stmt)
    {
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data = $this->getRowDataByAlias($row);

            foreach ($data as $alias => $item) {
                // The main object
                if ($this->alias === $alias) {
                    $this->hydrateObject($item);
                } else {
                    $this->hydrateRelation($data, $item, $alias);
                }
            }
        }

        unset($this->objectsByAlias);
        $this->objectsByAlias = [];

        return $this->objects;
    }

    /**
     * Hydrate a main object (called in "<code>FROM</code>" SQL clause)
     *
     * @param array $item
     */
    protected function hydrateObject(array $item)
    {
        $objectHash = Rocket::getTableMap($this->modelNamespace)->getPrimaryKeysHash($item);

        if (!isset($this->objectsByAlias[$this->alias][$objectHash])) {
            $object = new ArrayRecord($item, $this->modelNamespace);

            // Saving object for relations
            $this->objectsByAlias[$this->alias][$objectHash] = ['object' => $object];
            $this->objectsByAliasByRow[$this->alias] = $object;
            $this->objects[] = $object;
        } else {
            $this->objectsByAliasByRow[$this->alias] = $this->objectsByAlias[$this->alias][$objectHash]['object'];
        }
    }

    /**
     * Hydrate a relation object (called by "<code>JOIN</code>" SQL clause)
     *
     * @param array  $data
     * @param array  $item
     * @param string $alias
     */
    protected function hydrateRelation(array $data, array $item, $alias)
    {
        $hash = Rocket::getTableMap($this->joins[$alias]['relation']['namespace'])->getPrimaryKeysHash($item);
        $relationFrom = $this->joins[$alias]['from'];
        $relationPhpName = $this->joins[$alias]['relation']['phpName'];

        if (isset($this->joins[$relationFrom])) {
            $parentHash = Rocket::getTableMap($this->joins[$relationFrom]['relation']['namespace'])->getPrimaryKeysHash($data[$relationFrom]);
        } else {
            // Parent is the main object
            $parentHash = Rocket::getTableMap($this->modelNamespace)->getPrimaryKeysHash($data[$this->alias]);
        }

        // Item for the current row is empty
        if (null == $hash) {
            $this->hydrateNullRelation($alias, $relationFrom, $relationPhpName);

            return;
        }

        // Relation has already been added
        if (isset($this->objectsByAlias[$relationFrom][$parentHash]['childs'][$hash])) {
            return;
        }

        // Object does not exist : create it, otherwise use the object reference
        if (!isset($this->objectsByAlias[$alias][$hash])) {
            $this->objectsByAlias[$alias][$hash] = [
                'object' => new ArrayRecord($item, $this->joins[$alias]['relation']['namespace'])
            ];
        }

        $this->objectsByAliasByRow[$alias] = $this->objectsByAlias[$alias][$hash]['object'];

        // If the parent has not been processed yet
        if (!isset($this->objectsByAliasByRow[$relationFrom])) {
            // @codeCoverageIgnoreStart
            // Should never append, because Rocket does not allow to SELECT a relation before his parent table
            throw new \LogicException(
                'The parent object for the relation "' . $relationPhpName . '"'
                . ' (from: "' . $relationFrom . '") does not exist'
            );
            // @codeCoverageIgnoreEnd
        }

        // If many, put the object into another array, otherwise just set the $relationPhpName array key with the object
        if ($this->joins[$alias]['relation']['is_many']) {
            // Create the array if doesn't exist
            if (!isset($this->objectsByAliasByRow[$relationFrom][$relationPhpName])) {
                $this->objectsByAliasByRow[$relationFrom][$relationPhpName] = [];
            }

            $this->objectsByAliasByRow[$relationFrom][$relationPhpName][] = $this->objectsByAlias[$alias][$hash]['object'];
        } else {
            $this->objectsByAliasByRow[$relationFrom][$relationPhpName] = $this->objectsByAlias[$alias][$hash]['object'];
        }

        // Avoid duplicate relation objects
        $this->objectsByAlias[$relationFrom][$parentHash]['childs'][$hash] = $this->objectsByAlias[$alias][$hash]['object'];
    }

    /**
     * Hydrate non required relation where the row is NULL, e.g. LEFT JOIN relation
     *
     * @param string $alias
     * @param string $relationFrom
     * @param string $relationPhpName
     */
    protected function hydrateNullRelation($alias, $relationFrom, $relationPhpName)
    {
        if (!$this->joins[$alias]['relation']['is_many']) {
            $this->objectsByAliasByRow[$relationFrom][$relationPhpName] = null;
        } else {
            $this->objectsByAliasByRow[$relationFrom][$relationPhpName] = [];
        }

        // Do not forget to unset the current row
        unset($this->objectsByAliasByRow[$alias]);
    }

    /**
     * Get an array of row data indexed by the object alias, example :
     *
     * <code>
     * ['a.foo' => 1, 'b.bar' => 2] // become :
     *
     * [
     *   'a' => [
     *     'foo' => 1
     *   ], [
     *   'b' => [
     *     'bar' => 2
     *   ]
     * ]
     * </code>
     *
     * @param array $row
     *
     * @return array
     */
    protected function getRowDataByAlias(array $row)
    {
        $data = [];

        // Create an array of columns indexed by the query alias
        foreach ($row as $columnName => $value) {
            if (false !== strpos($columnName, '.')) {
                $params = explode('.', $columnName);
                $data[$params[0]][$params[1]] = $value;
            }
            else {
                $data[$this->alias][$columnName] = $value;
            }
        }

        return $data;
    }
}
