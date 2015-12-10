<?php

class shoppingCartModules {
  
    public static $modules = array();
    public static $function_call = '';
    
    function __construct()
    {
        $module_type = 'shopping_cart';
        $module_directory = DIR_FS_CATALOG . 'includes/modules/'. $module_type .'/';
        self::$modules = array();
        if (defined('MODULE_'. strtoupper($module_type) .'_INSTALLED') && xtc_not_null(constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'))) {
          $modules = explode(';', constant('MODULE_'. strtoupper($module_type) .'_INSTALLED'));
          foreach($modules as $file) {
            $class = substr($file, 0, strpos($file, '.'));
            $module_status = (defined('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS') && strtolower(constant('MODULE_'. strtoupper($module_type) .'_'. strtoupper($class) .'_STATUS')) == 'true') ? true : false;
            if (is_file($module_directory . $file) && $module_status) {
              include_once($module_directory . $file);
              $GLOBALS[$class] = new $class();
              self::$modules[] = $class;
            }
          }
          unset($modules);
        }
        //echo '<pre>'. print_r(self::$modules,1) . '<pre>'; EXIT;
    }
    
    public static function call_module_method()
    {
        $arg_list = func_get_args();
        $function_call = self::$function_call;
        if (is_array(self::$modules)) {
            reset(self::$modules);
            foreach(self::$modules as $class) {
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    $arg_list[0] = call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list); //Call the $GLOBALS[$class]->$function_call() method with $arg_list
                }
            }
        }
        return $arg_list[0]; //Returns only first parameter
    }
    
    //----- SHOPPING CART METHODS -----//
    public static function restore_contents_products_db($sql_data_array,$products_id,$table_basket,$qty,$type)
    {
        self::$function_call = 'restore_contents_products_db';
        return self::call_module_method($sql_data_array,$products_id,$table_basket,$qty,$type); //Return parameter must be in first place
    }

    public static function restore_contents_attributes_db($sql_data_array,$products_id,$value,$type)
    {
        self::$function_call = 'restore_contents_attributes_db';
        return self::call_module_method($sql_data_array,$products_id,$value,$type);
    }
    
    public static function restore_contents_products_session($products,$table_basket,$type)
    {
        self::$function_call = 'restore_contents_products_session';
        return self::call_module_method($products,$table_basket,$type);
    }
    
    public static function restore_contents_attributes_session($products,$table_basket_attributes,$type)
    {
        self::$function_call = 'restore_contents_attributes_session';
        return self::call_module_method($products,$table_basket_attributes,$type);
    }
    
    public static function add_cart_products_session($products_id,$type)
    {
        self::$function_call = 'add_cart_products_session';
        self::call_module_method($products_id,$type);
    }
    
    public static function add_cart_products_db($sql_data_array)
    {
        self::$function_call = 'add_cart_products_db';
        return self::call_module_method($products_id,$type);
    }
    
    public static function add_cart_attributes_session($value,$type)
    {
        self::$function_call = 'add_cart_attributes_session';
        self::call_module_method($value,$type);
    }
    
    public static function add_cart_attributes_db($sql_data_array)
    {
        self::$function_call = 'add_cart_attributes_db';
        return self::call_module_method($sql_data_array);
    }
    
    public static function remove_custom_inputs_session($products_id)
    {
        self::$function_call = 'remove_custom_inputs_session';
        self::call_module_method($products_id);
    }
    
    public static function calculate_product_price($products_price, $product, $contents)
    {
        self::$function_call = 'calculate_product_price';
        return self::call_module_method($products_price, $product, $contents);
    }
    
    public static function calculate_option_price($price, $option, $value, $products_id, $qty)
    {
        self::$function_call = 'calculate_option_price';
        return self::call_module_method($price, $option, $value, $products_id, $qty);
    }
    
    public static function get_uprid_value($value,$option)
    {
        self::$function_call = 'get_uprid_value';
        return self::call_module_method($value,$option);
    }
    
    
}