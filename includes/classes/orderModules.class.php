<?php

class orderModules {
    var $modules;
    
    function __construct()
    {
        $module_type = 'order';
        $module_directory = DIR_FS_CATALOG . 'includes/modules/'. $module_type .'/';
        $this->modules = array();
        if (defined('MODULE_'. strtoupper($module_type) .'_INSTALLED') && xtc_not_null(constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'))) {
          $modules = explode(';', constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'));
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
    
    //----- ORDER CUSTOM METHODS -----//
    function add_attributes($products_attributes,$attributes)
    {
        $this->function_call = 'add_attributes';
        return $this->call_module_method($products_attributes,$attributes); //Return parameter must be in first place
    }

    function cart_attributes($products_attributes,$attributes,$products_id,$value)
    {
        $this->function_call = 'cart_attributes';
        return $this->call_module_method($products_attributes,$attributes,$products_id,$value);
    }
    
}