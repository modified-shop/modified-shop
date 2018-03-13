<?php

namespace EasyCredit\Utility;

/**
 * Class Inflector
 *
 * @package EasyCredit\Utility
 */
class Inflector
{

    /**
     * @param string $string
     *
     * @return string
     */
    public static function classify($string)
    {
        $string = str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $string)));

        return preg_replace_callback('#[^a-z0-9]+#i', array(__CLASS__, 'classifyCallback'), $string);
    }

    /**
     * @param array $r
     *
     * @return string
     */
    public static function classifyCallback($r)
    {
        return strtoupper($r[1]);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toCamelcase($string)
    {
        return lcfirst(self::classify($string));
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toUnderscore($string)
    {
        return strtolower(preg_replace('#(?<=\\w)([A-Z])#', '_$1', $string));
    }
}
