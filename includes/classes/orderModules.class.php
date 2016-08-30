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
    
    //----- ORDER CUSTOM METHODS -----//
    function add_products($products,$orders_products)
    {
        $this->function_call = 'add_products';
        return $this->call_module_method($products,$orders_products); //Return parameter must be in first place
    }
    
    function add_attributes($products_attributes,$attributes)
    {
        $this->function_call = 'add_attributes';
        return $this->call_module_method($products_attributes,$attributes);
    }
    
    function order_data($order_data,$order_data_values,$oID,$order_lang_id)
    {
        $this->function_call = 'order_data';
        return $this->call_module_method($order_data,$order_data_values,$oID,$order_lang_id);
    } 
    
    function order_data_attributes($attributes_array,$attributes_data_values,$order_data_values,$oID,$order_lang_id)
    {
        $this->function_call = 'order_data_attributes';
        return $this->call_module_method($attributes_array,$attributes_data_values,$order_data_values,$oID,$order_lang_id);
    }    
    
    function cart_products($products,$products_id)
    {
        $this->function_call = 'cart_products';
        return $this->call_module_method($products,$products_id);
    }

    function cart_attributes($products_attributes,$attributes,$products_id,$value,$products)
    {
        $this->function_call = 'cart_attributes';
        return $this->call_module_method($products_attributes,$attributes,$products_id,$value,$products);
    }
    
}