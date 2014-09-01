<?php
/**
 * HTTP Context translater. Making sure data is set to the correct type.
 *
 * @package       Klarna Standard Mobile Kassa API
 * @version     1.0
 * @since         1.0 - 5 maj 2011
 * @link        http://integration.klarna.com/
 * @copyright    Copyright (c) 2011 Klarna AB (http://klarna.com)
 */
class KlarnaHTTPContext {
    public static function toString($sName, $mDefaultReturnValue = null) {
        $aArgs  = array_merge($_GET, $_POST, $_REQUEST);

        if(array_key_exists($sName, $aArgs))
        {
            return (string) $aArgs[$sName];
        }
        else {
            return $mDefaultReturnValue;
        }
    }

    public static function toBoolean($sName, $mDefaultReturnValue = null) {
        $aArgs  = array_merge($_GET, $_POST, $_REQUEST);

        if(array_key_exists($sName, $aArgs))
        {
            return ($aArgs[$sName] == '1' || 'true' ? true : false);
        }
        else {
            return $mDefaultReturnValue;
        }
    }

    public static function toInteger($sName, $mDefaultReturnValue = null) {
        $aArgs  = array_merge($_GET, $_POST, $_REQUEST);

        if(array_key_exists($sName, $aArgs))
        {
            return (integer) $aArgs[$sName];
        }
        else {
            return $mDefaultReturnValue;
        }
    }

    public static function toFloat($sName, $mDefaultReturnValue = null) {
        $aArgs  = array_merge($_GET, $_POST, $_REQUEST);

        if(array_key_exists($sName, $aArgs))
        {
            return floatval($aArgs[$sName]);
        }
        else {
            return $mDefaultReturnValue;
        }
    }

    public static function toArray($sName, $mDefaultReturnValue = null) {
        $aArgs  = array_merge($_GET, $_POST, $_REQUEST);

        if(array_key_exists($sName, $aArgs))
        {
            return (array) $aArgs[$sName];
        }
        else {
            return $mDefaultReturnValue;
        }
    }

    public static function toFile($sName, $mDefaultReturnValue = null) {
        if(array_key_exists($sName, $_FILES)) {
            return $_FILES[$sName];
        }
        else {
            return $mDefaultReturnValue;
        }
    }
}
