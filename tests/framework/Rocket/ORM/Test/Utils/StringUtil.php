<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Utils;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class StringUtil
{
    /**
     * @param int  $length
     * @param bool $uppercase
     * @param bool $useSpecialChars
     *
     * @return string
     */
    public static function generateRandomString($length = 10, $uppercase = false, $useSpecialChars = false)
    {
        if (0 > $length) {
            $length = -$length;
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($useSpecialChars) {
            $characters .= '-_.+=';
        }

        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        if ($uppercase) {
            return mb_strtoupper($randomString);
        }

        return $randomString;
    }
}
