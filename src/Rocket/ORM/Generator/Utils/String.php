<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Utils;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class String
{
    /**
     * @param string $string The string
     * @param bool   $upper  True if the first letter is uppercase, false otherwise
     *
     * @return string
     */
    public static function camelize($string, $upper = true)
    {
        $camelize = strtr(ucwords(strtr($string, array('_' => ' ', '.' => ' ', '\\' => ' '))), array(' ' => ''));
        if ($upper) {
            return $camelize;
        }

        return lcfirst($camelize);
    }
}
