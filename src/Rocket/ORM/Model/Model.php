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
     * @var bool
     */
    protected $_isModified = false;

    /**
     * @var bool
     */
    protected $_isDeleted = false;

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
        if ($this->_isDeleted) {
            throw new \LogicException('Cannot save a deleted object');
        }

        if (null == $con) {
            $con = Rocket::getConnection($this->getTableMap()->getConnectionName(), Rocket::CONNECTION_MODE_WRITE);
        }

        try {
            if ($this->preSave($con)) {
                if ($this->_isNew && $this->saveRelations($con)) {
                    $this->doInsert($con);
                    $this->postSave($con);

                    $this->_isNew = false;
                    $this->_isModified = false;
                } elseif ($this->saveRelations($con) && $this->_isModified) {
                    $this->doUpdate($con);
                    $this->postSave($con);

                    $this->_isModified = false;
                }
            }
        } catch (\Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }

            throw $e;
        }

        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function delete(\PDO $con = null)
    {
        if ($this->_isNew) {
            throw new \LogicException('Cannot delete a new object');
        }

        if ($this->_isDeleted) {
            throw new \LogicException('Cannot delete an already deleted object');
        }

        if (null == $con) {
            $con = Rocket::getConnection($this->getTableMap()->getConnectionName(), Rocket::CONNECTION_MODE_WRITE);
        }
        try {
            if ($this->preDelete($con)) {
                $this->doDelete($con);
            }

            $this->postDelete($con);
        } catch (\Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }

            throw $e;
        }

        $this->_isDeleted = true;

        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function preSave(\PDO $con)
    {
        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function postSave(\PDO $con)
    {
        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function preDelete(\PDO $con)
    {
        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return bool
     */
    protected function postDelete(\PDO $con)
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
     * @param \PDO $con
     *
     * @return bool
     */
    protected function saveRelations(\PDO $con)
    {
        return true;
    }

    /**
     * @param \PDO $con
     *
     * @return void
     */
    protected abstract function doInsert(\PDO $con);

    /**
     * @param \PDO $con
     *
     * @return void
     */
    protected abstract function doUpdate(\PDO $con);

    /**
     * @param \PDO $con
     *
     * @return void
     */
    protected abstract function doDelete(\PDO $con);
}
