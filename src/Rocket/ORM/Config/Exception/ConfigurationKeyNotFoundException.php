<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Config\Exception;

use Rocket\ORM\Exception\RocketException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConfigurationKeyNotFoundException extends RocketException
{
    /**
     * @param string $key
     * @param int    $statusCode
     */
    public function __construct($key, $statusCode = 0)
    {
        parent::__construct('The rocket configuration key "' . $key . '" is not found', $statusCode);
    }
}
