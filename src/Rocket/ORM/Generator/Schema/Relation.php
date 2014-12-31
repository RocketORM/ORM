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

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class Relation
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var string
     */
    public $with;

    /**
     * @var string
     */
    public $phpName;

    /**
     * @var string
     */
    public $local;

    /**
     * @var string
     */
    public $foreign;

    /**
     * @var string
     */
    public $onDelete = 'RESTRICT';

    /**
     * @var string
     */
    public $onUpdate = 'RESTRICT';


    /**
     * @param string $with
     * @param array  $data
     */
    public function __construct($with, array $data)
    {
        $this->with      = $with;
        $this->phpName   = $data['phpName'];
        $this->local     = $data['local'];
        $this->foreign   = $data['foreign'];
        $this->onDelete  = $data['onDelete'];
        $this->onUpdate =  $data['onUpdate'];
    }


    /**
     * @return Table
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param Table $table
     */
    public function setTable(Table $table)
    {
        $this->table = $table;
    }
}
