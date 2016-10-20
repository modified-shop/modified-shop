<?php

class mainModules {
    var $modules;
    
    function __construct()
    {
        $module_type = 'main';
        $module_directory = DIR_FS_CATALOG . 'includes/modules/'. $module_type .'/';
        $this->modules = array();
        if (defined('MODULE_'. strtoupper($module_type) .'_INSTALLED') && xtc_not_null(constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'))) {
          $modules = explode(';', constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'));
          foreach($modules as $file) {
            $class = substr($file, 0, strpos($file, '.'));
            $module_status = (defined('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS') && strtolower(constant('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS')) == 'true') ? true : false;
            if (is_file($module_directory . $file) && $module_status) {
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
            reset($this->modules);
            foreach($this->modules as $class) {
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    $arg_list[0] = call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list); //Call the $GLOBALS[$class]->$function_call() method with $arg_list
                }
            }
        }
        return $arg_list[0]; //Returns only first parameter
    }
    
    function secure_call_module_method() //change no parameter
    {
        $arg_list = func_get_args();
        $function_call = $this->function_call;
        if (is_array($this->modules)) {
            reset($this->modules);
            foreach($this->modules as $class) {
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list); //Call the $GLOBALS[$class]->$function_call() method with $arg_list
                }
            }
        }
    }

    //----- MAIN CUSTOM METHODS -----//
    function getTaxInfo($tax_info,$tax_rate)
    {
        $this->function_call = 'getTaxInfo';
        return $this->call_module_method($tax_info,$tax_rate); //Return parameter must be in first place
    }

    function getShippingNotice($contentLink, $coID, $text, $ssl, $class_more)
    {
        $this->function_call = 'getShippingNotice';
        return $this->call_module_method($contentLink, $coID, $text, $ssl, $class_more);
    }
    
    function getContentData($content_data_array, $coID, $lang_id, $customers_status, $get_inactive, $add_select)
    {
        $this->function_call = 'getContentData';
        return $this->call_module_method($content_data_array, $coID, $lang_id, $customers_status, $get_inactive, $add_select);
    }
    
    function getVPEtext($vpeText, $products, $price, $vpe_name)
    {
        $this->function_call = 'getVPEtext';
        return $this->call_module_method($vpeText, $products, $price, $vpe_name);
    }
    
    function getProductPopupLink($productPopupLink, $pID, $text, $class, $add_params)
    {
        $this->function_call = 'getProductPopupLink';
        return $this->call_module_method($productPopupLink, $pID, $text, $class, $add_params);
    }
    
    function getAttributes($paramsArr,$paramsArrOrigin)
    {
        $this->function_call = 'getAttributes';
        return $this->call_module_method($paramsArr,$paramsArrOrigin);
    }
    
    function getAttributesSelect($attributes,$paramsArr,$paramsArrOrigin)
    {
        $this->function_call = 'getAttributesSelect';
        return $this->call_module_method($attributes,$paramsArr,$paramsArrOrigin);
    }
    
}