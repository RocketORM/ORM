<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model\Query;

use Rocket\ORM\Model\Map\TableMapInterface;
use Rocket\ORM\Model\Object\RocketObject;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Query implements QueryInterface
{
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT  = 'LEFT';

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var TableMapInterface
     */
    protected $tableMap;

    /**
     * @var array
     */
    protected $clauses = [];

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var array All related tables that will inserted in the SELECT statement
     */
    protected $with = [];


    /**
     * @param string $alias
     * @param string $modelNamespace
     */
    public function __construct($alias, $modelNamespace)
    {
        if (null == $alias) {
            throw new \LogicException('The "' . get_called_class() . '" alias can be null');
        }

        $this->alias          = $alias;
        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @param string     $clause
     * @param null|mixed $value  Can't be an array
     *
     * @return $this
     */
    public function where($clause, $value = null)
    {
        $this->doWhere($clause, $value, 'AND');

        return $this;
    }

    /**
     * @param string     $clause
     * @param null|mixed $value
     *
     * @return $this|Query
     *
     * @throws \Exception
     */
    public function orWhere($clause, $value = null)
    {
        $this->doWhere($clause, $value, 'OR');

        return $this;
    }

    /**
     * @param string $clause
     * @param mixed  $value
     * @param string $operator
     */
    protected function doWhere($clause, $value, $operator)
    {
        $this->clauses[] = [
            'clause'   => $clause,
            'value'    => $value,
            'operator' => $operator
        ];
    }

    /**
     * @param int      $limit
     * @param null|int $offset
     *
     * @return $this|Query
     */
    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        if (null != $offset) {
            $this->offset = $offset;
        }

        return $this;
    }

    /**
     * @param \PDO $con
     *
     * @return RocketObject|null
     */
    public function findOne(\PDO $con = null)
    {
        $this->limit(1);

        $objects = $this->find($con);

        if (isset($objects[0])) {
            return $objects[0];
        }

        return null;
    }

    /**
     * @param string      $relation
     * @param null|string $alias
     *
     * @return $this|Query
     *
     * @throws \Exception
     */
    public function innerJoinWith($relation, $alias = null)
    {
        if (null == $alias) {
            $alias = $relation;
        }

        return $this->join($relation, $alias, self::JOIN_TYPE_INNER, true);
    }

    /**
     * @param string      $relation
     * @param null|string $alias
     *
     * @return $this|Query
     *
     * @throws \Exception
     */
    public function leftJoinWith($relation, $alias = null)
    {
        if (null == $alias) {
            $alias = $relation;
        }

        return $this->join($relation, $alias, self::JOIN_TYPE_LEFT, true);
    }

    /**
     * @param string $relation
     * @param string $alias
     * @param string $joinType
     * @param bool   $with
     *
     * @return $this|Query
     *
     * @throws \Exception
     */
    protected function join($relation, $alias, $joinType = self::JOIN_TYPE_INNER, $with = false)
    {
        $relationTable = $relation;
        $from = null;

        // Remove the link alias if exists : "Alias.Relation", removing "Alias."
        $pos = strpos($relation, '.');
        if (false !== $pos) {
            $relationTable = substr($relation, $pos + 1);
            $from = substr($relation, 0, $pos);
        }

        $tableMap = $this->getTableMap();
        $hasRelation = $tableMap->hasRelation($relationTable);

        if (!$hasRelation || $hasRelation && null !== $from && $this->alias !== $from) {
            if (null == $from) {
                throw new \Exception('No relation with ' . $relation . ' for model ' . $this->modelNamespace);
            }

            return $this->joinDeep($relation, $alias, $joinType, $with);
        }

        if ($with) {
            $this->with($alias);
        }

        $this->joins[$alias] = [
            'from'     => $this->alias,
            'relation' => $tableMap->getRelation($relationTable),
            'type'     => $joinType
        ];

        return $this;
    }

    /**
     * @param string $relation
     * @param string $alias
     * @param string $joinType
     * @param string $with
     *
     * @return $this|Query
     *
     * @throws \Exception
     */
    protected function joinDeep($relation, $alias, $joinType, $with)
    {
        $params = explode('.', $relation);
        if (!isset($this->joins[$params[0]])) {
            throw new \Exception('No alias found for relation ' . $params[0] . ' for model ' . $this->modelNamespace);
        }

        $tableMap = Rocket::getTableMap($this->joins[$params[0]]['relation']['namespace']);
        if ($with) {
            $this->with($alias, $params[0]);
        }

        $this->joins[$alias] = [
            'from' => $params[0],
            'relation' => $tableMap->getRelation($params[1]),
            'type' => $joinType
        ];

        return $this;
    }

    /**
     * @param string      $alias
     * @param null|string $table
     */
    protected function with($alias, $table = null)
    {
        $this->with[$alias] = [
            'alias' => $alias,
            'from'  => $table
        ];
    }

    /**
     * @return string
     */
    protected function buildRelationWith()
    {
        $query = '';
        foreach ($this->with as $with) {
            /** @var TableMapInterface $relationTableMap */
            $relationTableMap = Rocket::getTableMap($this->joins[$with['alias']]['relation']['namespace']);
            foreach ($relationTableMap->getColumns() as $column) {
                $query .= ', ' . $with['alias'] . '.' . $column['name'] . ' AS "' . $with['alias'] . '.' . $column['name'] . '"';
            }

            unset($relationTableMap);
        }

        return $query;
    }

    /**
     * @return string
     */
    protected function buildRelationClauses()
    {
        $query = '';
        foreach ($this->joins as $alias => $join) {
            /** @var TableMapInterface $relationTableMap */
            $tableMap = Rocket::getTableMap($join['relation']['namespace']);
            $query .= sprintf(' %s JOIN `%s`.`%s` %s ON %s.%s = %s.%s',
                $join['type'],
                $tableMap->getDatabase(),
                $tableMap->getTableName(),
                $alias,
                $join['from'],
                $join['relation']['local'],
                $alias,
                $join['relation']['foreign']
            );

            unset($tableMap);
        }

        return $query;
    }

    /**
     * @return string
     */
    protected function buildClauses()
    {
        $query = ' WHERE ';

        // FIXME handle the case when a clause need to be encapsulated by parentheses
        foreach ($this->clauses as $i => $clauseParams) {
            if (0 == $i) {
                if (null != $clauseParams['value']) {
                    // foo = :param_0
                    $query .= sprintf('%s :param_%d', trim(substr($clauseParams['clause'], 0, -1)), $i);
                } else {
                    $query .= $clauseParams['clause'];
                }
            } else {
                if (null != $clauseParams['value']) {
                    // AND foo = :param_1
                    $query .= sprintf(' %s %s :param_%d', $clauseParams['operator'], trim(substr($clauseParams['clause'], 0, -1)), $i);
                } else {
                    $query .= $clauseParams['operator'] . ' ' . $clauseParams['clause'];
                }
            }
        }

        return $query;
    }

    /**
     * @return string
     */
    protected function buildLimit()
    {
        $query = '';
        if (null != $this->limit) {
            $query .= ' LIMIT ' . $this->limit;

            if (null != $this->offset) {
                $query .= ',' . $this->offset;
            }
        }

        return $query;
    }

    /**
     * @param \PDOStatement $stmt
     *
     * @return array|RocketObject[]
     */
    protected function hydrate(\PDOStatement $stmt)
    {
        // FIXME do sub query when limit == 1 and there is many_to_* relation

        $hasRelation = 0 < sizeof($this->with);
        if (!$hasRelation) {
            $objects = $this->hydrateWithoutRelation($stmt);
        } else {
            $objects = $this->hydrateWithRelations($stmt);
        }

        $stmt->closeCursor();

        return $objects;
    }

    /**
     * @param \PDOStatement $stmt
     *
     * @return array|RocketObject[]
     */
    protected function hydrateWithoutRelation(\PDOStatement $stmt)
    {
        $objects = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $objects[] = new RocketObject($row, $this->modelNamespace);
        }

        return $objects;
    }

    /**
     * @return string
     */
    public function getSqlQuery()
    {
        $query = $this->buildQuery();
        if (0 < sizeof($this->clauses)) {
            foreach ($this->clauses as $i => $clauseParams) {
                if (is_string($clauseParams['value'])) {
                    $query = str_replace(':param_' . $i, "'" . $clauseParams['value'] . "'", $query);
                } else {
                    $query = str_replace(':param_' . $i, $clauseParams['value'], $query);
                }
            }
        }

        return $query;
    }

    /**
     * @param \PDOStatement $stmt
     *
     * @return array|RocketObject[]
     */
    protected function hydrateWithRelations(\PDOStatement $stmt)
    {
        // The main objects array, returning at the end
        $objects = [];

        // An array of objects indexed by the query alias, to know if the object has been already
        // instantiated and avoid multiple instantiations
        $objectsByAlias = [];

        // An array of objects indexed by query alias by row (cleared after each loop),
        // to know where the relation object should be placed
        $objectsByAliasByRow = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
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

            foreach ($data as $alias => $item) {
                // The main object
                if ($this->alias === $alias) {
                    $objectHash = $this->getTableMap()->getPrimaryKeysHash($row);

                    if (!isset($objectsByAlias[$this->alias][$objectHash])) {
                        $object = new RocketObject($item, $this->modelNamespace);

                        // Saving object for relations
                        $objectsByAlias[$alias][$objectHash] = $object;
                        $objectsByAliasByRow[$alias] = $object;
                        $objects[] = $object;
                    } else {
                        $objectsByAliasByRow[$alias] = $objectsByAlias[$alias][$objectHash];
                    }
                } else {
                    // Relations
                    $hash = Rocket::getTableMap($this->joins[$alias]['relation']['namespace'])->getPrimaryKeysHash($item);
                    $relationFrom = $this->joins[$alias]['from'];

                    if (isset($objectsByAlias[$relationFrom]['childs'][$hash])) {
                        continue;
                    }

                    if (!isset($objectsByAlias[$alias][$hash])) {
                        $objectsByAlias[$alias][$hash] = new RocketObject($item, $this->joins[$alias]['relation']['namespace']);
                    }

                    $objectsByAliasByRow[$alias] = $objectsByAlias[$alias][$hash];
                    $relationPhpName = $this->joins[$alias]['relation']['phpName'];

                    if (!isset($objectsByAliasByRow[$relationFrom])) {
                        throw new \LogicException(
                            'The parent object for the relation "' . $relationPhpName . '"'
                            . ' (from: "' . $relationFrom . '") does not exist'
                        );
                    }

                    if ($this->joins[$alias]['relation']['is_many']) {
                        // If many, create the array if doesn't exist
                        if (!isset($objectsByAliasByRow[$relationFrom][$relationPhpName])) {
                            $objectsByAliasByRow[$relationFrom][$relationPhpName] = [];
                        }

                        $objectsByAliasByRow[$relationFrom][$relationPhpName][] = $objectsByAlias[$alias][$hash];
                    } else {
                        $objectsByAliasByRow[$relationFrom][$relationPhpName] = $objectsByAlias[$alias][$hash];
                    }

                    // Avoid duplicate relation objects
                    $objectsByAlias[$relationFrom]['childs'][$hash] = $objectsByAlias[$alias][$hash];
                }
            }
        }

        $objectsByAlias = null;
        unset($objectsByAlias);

        return $objects;
    }

    /**
     * Clear all values
     */
    protected function clear()
    {
        unset($this->clauses, $this->joins, $this->with, $this->limit, $this->offset);

        $this->clauses = [];
        $this->joins   = [];
        $this->with    = [];
        $this->limit   = null;
        $this->offset  = null;
    }

    /**
     * @return TableMapInterface
     */
    protected function getTableMap()
    {
        if (!isset($this->tableMap)) {
            $this->tableMap = Rocket::getTableMap($this->modelNamespace);
        }

        return $this->tableMap;
    }

    /**
     * @param \PDO $con
     *
     * @return mixed
     */
    public abstract function find(\PDO $con = null);

    /**
     * @return string
     */
    protected abstract function buildQuery();
}
