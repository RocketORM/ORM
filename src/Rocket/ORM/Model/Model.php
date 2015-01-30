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
        if (null == $con) {
            $con = Rocket::getConnection($this->getTableMap()->getConnectionName(), Rocket::CONNECTION_MODE_WRITE);
        }

        try {
            if ($this->preSave($con)) {
                if ($this->_isNew) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
            }

            $this->postSave($con);
        } catch (\Exception $e) {
            if ($con->inTransaction()) {
                $con->rollBack();
            }

            throw $e;
        }

        $this->_isNew = false;

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
}
