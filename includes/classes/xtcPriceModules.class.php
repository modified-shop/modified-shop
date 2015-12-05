<?php

class priceModules {
    var $modules;
    
    function __construct()
    {
        $module_directory = DIR_FS_CATALOG . 'includes/modules/xtcPrice/';
        $this->modules = array();
        if (defined('MODULE_XTCPRICE_INSTALLED') && xtc_not_null(MODULE_XTCPRICE_INSTALLED)) {
          $modules = explode(';', MODULE_XTCPRICE_INSTALLED);
          foreach($modules as $file) {
            if (is_file($module_directory . $file)) {
              include_once($module_directory . $file);
              $class = substr($file, 0, strpos($file, '.'));
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
    
    //----- PRICE FUNCTIONS -----//
    function GetOptionPrice($dataArr,$attribute_data,$pID, $option, $value)
    {
        $this->function_call = 'GetOptionPrice';
        return $this->call_module_method($dataArr,$attribute_data,$pID, $option, $value); //Return parameter must be in first place
    }
    
}