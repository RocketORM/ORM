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
     * @var string The column to count
     */
    protected $count;

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
     * @param string $column
     *
     * @return $this|Query
     */
    public function count($column = '*')
    {
        $this->count = $column;

        return $this;
    }

    /**
     * @param \PDO $con
     *
     * @return RocketObject|bool
     */
    public function findOne(\PDO $con = null)
    {
        $this->limit(1);

        return $this->find($con);
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
        $hasLink = false;
        $relationTable = $relation;

        // Remove the link alias if exists : "Alias.Relation", removing "Alias."
        $pos = strpos($relation, '.');
        if (false !== $pos) {
            $relationTable = substr($relation, $pos + 1);
            $hasLink = true;
        }

        $tableMap = $this->getTableMap();
        if (!$tableMap->hasRelation($relationTable)) {
            if (!$hasLink) {
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

        $tableMap = Rocket::getTableMap($this->joins[$params[0]]['relation']['table_map_namespace']);
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
     * @param array  $row
     * @param string $alias
     * @param string $columnName
     * @param mixed  $value
     * @param string $from
     */
    protected function hydrateRelationValue(&$row, $alias, $columnName, $value, $from)
    {
        if (null == $from) {
            $row[$this->joins[$alias]['relation']['phpName']][$columnName] = $value;
        }
        else {
            $relationName = $this->joins[$from]['relation']['phpName'];
            if (!isset($row[$relationName])) {
                $row[$relationName] = [];
            }

            $this->hydrateRelationValue($row[$relationName], $alias, $columnName, $value, $this->with[$from]['from']);
        }
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
        $i = 0;

        // FIXME handle the case when a clause need to be encapsulated by parentheses
        foreach ($this->clauses as $clauseParams) {
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

            ++$i;
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
     * Clear all values
     */
    protected function clear()
    {
        unset($this->clauses, $this->joins, $this->with, $this->offset);
        $this->clauses = [];
        $this->joins   = [];
        $this->with    = [];
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
}
