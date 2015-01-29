<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Model;

use Rocket\ORM\Model\Map\TableMapInterface;
use Rocket\ORM\Rocket;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Model implements ModelInterface
{
    /**
     * @var bool
     */
    protected $_isNew = true;

    /**
     * @var array
     */
    protected $_modifiedColumns = [];

    /**
     * @var TableMapInterface
     */
    protected $tableMap;


    /**
     * @param \PDO $con
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function save(\PDO $con = null)
    {
        if (!$this->_isNew) {
            throw new \Exception('Cannot save a non new object');
        }

        if (0 === sizeof($this->_modifiedColumns)) {
            throw new \Exception('Cannot save an empty object');
        }

        if (null == $con) {
            $con = Rocket::getConnection(null, Rocket::CONNECTION_MODE_WRITE);
        }

        // $con->beginTransaction();
        try {
            if ($this->preSave($con)) {
                $this->doInsert($con);
            }

            $this->postSave($con);
        } catch (\Exception $e) {
            // $con->rollBack();

            throw $e;
        }

        // $con->commit();

        $this->_isNew = false;
        $this->clearModifiedColumns();

        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function preSave(\PDO $con = null)
    {
        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function postSave(\PDO $con = null)
    {
        return true;
    }

    /**
     * @return TableMapInterface
     */
    protected function getTableMap()
    {
        if (!isset($this->tableMap)) {
            $this->tableMap = Rocket::getTableMap(get_called_class());
        }

        return $this->tableMap;
    }

    /**
     * @return array
     */
    protected function getModifiedColumns()
    {
        return array_keys($this->_modifiedColumns);
    }

    /**
     * Delete all modified column flags
     */
    protected function clearModifiedColumns()
    {
        $this->_modifiedColumns = [];
    }

    /**
     * @param \PDO $con
     *
     * @return void
     */
    protected abstract function doInsert(\PDO $con);
}
