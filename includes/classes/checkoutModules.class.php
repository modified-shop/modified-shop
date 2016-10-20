<?php

class checkoutModules {
    var $modules;
    
    function __construct()
    {
        $module_type = 'checkout';
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

    //----- ORDER CUSTOM METHODS -----//
    function payment_modules($modules)
    {
        $this->function_call = 'payment_modules';
        return $this->call_module_method($modules);
    }
    
    function unallowed_payment_modules($unallowed_modules)
    {
        $this->function_call = 'unallowed_payment_modules';
        return $this->call_module_method($unallowed_modules);
    }

    function shipping_modules($modules)
    {
        $this->function_call = 'shipping_modules';
        return $this->call_module_method($modules);
    }
    
    function unallowed_shipping_modules($unallowed_modules)
    {
        $this->function_call = 'unallowed_shipping_modules';
        return $this->call_module_method($unallowed_modules);
    }
    

    
}