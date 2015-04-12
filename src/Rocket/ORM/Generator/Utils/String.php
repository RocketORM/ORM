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

    /**
     * Pluralizes English noun.
     *
     * @param  string  $word english noun to pluralize
     *
     * @return string
     *
     * @throws \LogicException
     *
     * @see https://github.com/whiteoctober/RestBundle/blob/master/Pluralization/Pluralization.php
     * @codeCoverageIgnore
     */
    public static function pluralize($word)
    {
        static $plurals = [
            '/(quiz)$/i'                => '\1zes',
            '/^(ox)$/i'                 => '\1en',
            '/([m|l])ouse$/i'           => '\1ice',
            '/(matr|vert|ind)ix|ex$/i'  => '\1ices',
            '/(x|ch|ss|sh)$/i'          => '\1es',
            '/([^aeiouy]|qu)ies$/i'     => '\1y',
            '/([^aeiouy]|qu)y$/i'       => '\1ies',
            '/(hive)$/i'                => '\1s',
            '/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',
            '/sis$/i'                   => 'ses',
            '/([ti])um$/i'              => '\1a',
            '/(buffal|tomat)o$/i'       => '\1oes',
            '/(bu)s$/i'                 => '\1ses',
            '/(alias|status)/i'         => '\1es',
            '/(octop|vir)us$/i'         => '\1i',
            '/(ax|test)is$/i'           => '\1es',
            '/s$/i'                     => 's',
            '/$/'                       => 's'
        ];

        static $uncountables = [
            'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'
        ];

        static $irregulars = [
            'person'  => 'people',
            'man'     => 'men',
            'child'   => 'children',
            'sex'     => 'sexes',
            'move'    => 'moves'
        ];

        $lowerCasedWord = strtolower($word);
        foreach ($uncountables as $uncountable) {
            if ($uncountable == substr($lowerCasedWord, (-1 * strlen($uncountable)))) {
                return $word;
            }
        }

        foreach ($irregulars as $plural => $singular) {
            if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
                return preg_replace('/(' . $plural . ')$/i', substr($arr[0], 0, 1) . substr($singular, 1), $word);
            }
        }

        foreach ($plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        throw new \LogicException('Unknown plural for word "' . $word . '"');
    }
}
