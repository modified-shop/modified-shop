<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware  
   http://www.modified-shop.org     

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(currencies.php,v 1.15 2003/03/17); www.oscommerce.com
   (c) 2003 nextcommerce (currencies.php,v 1.9 2003/08/17); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtcPrice.php 1316 2005-10-21)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class priceModules {
    
    var $modules;
    var $function_call;
    
    function __construct()
    {
        $module_type = 'xtcPrice';
        $module_directory = DIR_FS_CATALOG . 'includes/modules/'. $module_type .'/';
        $this->modules = array();
        if (defined('MODULE_'. strtoupper($module_type) .'_INSTALLED') && xtc_not_null(constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'))) {
          $modules = explode(';', constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'));
          foreach($modules as $file) {
            $class = substr($file, 0, strpos($file, '.'));
            $module_status = (defined('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS') && strtolower(constant('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS')) == 'true') ? true : false;
            if (is_file($module_directory . $file) && $module_status) {
              if (file_exists(DIR_FS_CATALOG . 'lang/' . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file)) {
                include_once(DIR_FS_CATALOG . 'lang/' . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file);
              }
              include_once($module_directory . $file);
              $GLOBALS[$class] = new $class();
              $this->modules[] = $class;
            }
          }
          unset($modules);
        }
    }
    
    function call_module_method()
    {
        $arg_list = func_get_args(); 
        $function_call = $this->function_call;
        if (is_array($this->modules)) {
            foreach($this->modules as $class) {
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    $arg_list[0] = call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list);
                }
            }
        }
        return $arg_list[0];
    }
    
    function secure_call_module_method()
    {
        $arg_list = func_get_args();
        $function_call = $this->function_call;
        if (is_array($this->modules)) {
            foreach($this->modules as $class) {
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list);
                }
            }
        }
    }
    
    //----- PRICE FUNCTIONS -----//
    function construct($currency, $cGroup)
    {
        $this->function_call = 'construct';
        return $this->call_module_method($currency, $cGroup);
    }
    
    function CheckSpecial($product, $pID)
    {
        $this->function_call = 'CheckSpecial';
        return $this->call_module_method($product, $pID);
    }
    
    function CheckSpecialPrice($special_price, $pID)
    {
        $this->function_call = 'CheckSpecialPrice';
        return $this->call_module_method($special_price, $pID);
    }

    function CheckSpecialProductPrice($products_price, $pID, $add_tax)
    {
        $this->function_call = 'CheckSpecialProductPrice';
        return $this->call_module_method($products_price, $pID, $add_tax);
    }
    
    function GetOptionPrice($dataArr,$attribute_data,$pID, $option, $value, $qty)
    {
        $this->function_call = 'GetOptionPrice';
        return $this->call_module_method($dataArr,$attribute_data,$pID, $option, $value, $qty);
    }
    
    function FormatSpecial($return, $pID, $sPrice, $pPrice, $format, $vpeStatus)
    {
        $this->function_call = 'FormatSpecial';
        return $this->call_module_method($return, $pID, $sPrice, $pPrice, $format, $vpeStatus);
    }
    
    function checkAttributes($pID)
    {
        $this->function_call = 'checkAttributes';
        return $this->call_module_method($pID);
    }
    
    function getPprice($pData,$pID)
    {
        $this->function_call = 'getPprice';
        return $this->call_module_method($pData, $pID);
    }

    function xtcGetGraduatedPrice($pID, $qty, $graduated)
    {
        $this->function_call = 'xtcGetGraduatedPrice';
        return $this->call_module_method($pID, $qty, $graduated);
    }
    
    function CheckExtension($price, $pID)
    {
        $this->function_call = 'CheckExtension';
        return $this->call_module_method($price, $pID);
    }

    function FormatExtension($return, $pID, $ePrice, $pPrice, $format, $vpeStatus)
    {
        $this->function_call = 'FormatExtension';
        return $this->call_module_method($return, $pID, $ePrice, $pPrice, $format, $vpeStatus);
    }

}