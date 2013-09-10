<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Get path to file from include_path
 *
 * @param string $file_path
 * @param string $new_file_path
 * @return boolean
 * @staticvar array|null
 */

//  $file_path, &$new_file_path

function smarty_core_get_include_path(&$params, &$smarty)
{
    static $_path_array = null;

    if(!isset($_path_array)) {
        $_ini_include_path = ini_get('include_path');

        if(strstr($_ini_include_path,';')) {
            // windows pathnames
            $_path_array = explode(';',$_ini_include_path);
        } else {
            $_path_array = explode(':',$_ini_include_path);
        }
    }
    $_ini_openbase_dir = ini_get('open_basedir');
    
    if(strstr($_ini_openbase_dir,';')) {
        // windows pathnames
        $_openbase_path_array = explode(';',$_ini_openbase_dir);
    } else {
        $_openbase_path_array = explode(':',$_ini_openbase_dir);
    }
    foreach ($_path_array as $_include_path) {
        if (in_array($_include_path, $_openbase_path_array)) {
            if (@is_readable($_include_path . DIRECTORY_SEPARATOR . $params['file_path'])) {
                   $params['new_file_path'] = $_include_path . DIRECTORY_SEPARATOR . $params['file_path'];
                return true;
            }
        }
    }
    return false;
}

/* vim: set expandtab: */

?>
