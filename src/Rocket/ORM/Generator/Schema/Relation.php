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

use Rocket\ORM\Model\Map\TableMap;

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
     * @var int
     */
    public $type;


    /**
     * @param string $with
     * @param array  $data
     */
    public function __construct($with, array $data)
    {
        if (false !== strpos('\\', $with)) {
            $with = str_replace('\\', '\\\\', $with);
        }

        $this->with      = $with;
        $this->phpName   = $data['phpName'];
        $this->local     = $data['local'];
        $this->foreign   = $data['foreign'];
        $this->onDelete  = $data['onDelete'];
        $this->onUpdate =  $data['onUpdate'];

        if (isset($data['type'])) {
            $this->type = $data['type'];
        }
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

    /**
     * @return string
     */
    public function typeAsString()
    {
        $reflection = new \ReflectionClass('\\Rocket\\ORM\\Model\\Map\\TableMap');
        foreach ($reflection->getConstants() as $name => $value) {
            if ($this->type == $value) {
                return $name;
            }
        }

        throw new \LogicException('Unknown value "' . $this->type . '" for constant TableMap::RELATION_TYPE_*');
    }
}
