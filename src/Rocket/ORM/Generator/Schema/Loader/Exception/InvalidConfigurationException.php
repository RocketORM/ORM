<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Loader\Exception;

use Rocket\ORM\Exception\RocketException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException as ConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class InvalidConfigurationException extends RocketException
{
    /**
     * @param string                 $path
     * @param ConfigurationException $e
     */
    public function __construct($path, ConfigurationException $e)
    {
        parent::__construct($e->getMessage() . ' (schema : "' . $path . '")', $e->getCode(), $e->getPrevious());
    }
}
