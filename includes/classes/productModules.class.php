<?php

class productModules {
    var $modules;
    
    function __construct()
    {
        $module_directory = DIR_FS_CATALOG . 'includes/extra/classes/product/';
        $this->modules = array();
        if (defined('MODULE_PRODUCT_INSTALLED') && xtc_not_null(MODULE_PRODUCT_INSTALLED)) {
          $modules = explode(';', MODULE_PRODUCT_INSTALLED);
          //echo '<pre>'.print_r($modules,true).'</pre>';
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
        $arg_list = func_get_args(); //Liefert die der aufrufenden Funktion übergebenen Argumente als Array. 
        $function_call = $this->function_call;
        //echo '<pre>C1'.print_r($arg_list,true).'</pre>';
        //echo '<pre>'.$function_call.'</pre>';
        if (is_array($this->modules)) {
            reset($this->modules);
            foreach($this->modules as $class) {
                //echo  '<pre>CL: '.$class.'</pre>';
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    $arg_list[0] = call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list); //Call the $GLOBALS[$class]->$function_call() method with $arg_list
                }
            }
        }
        //echo '<pre>'.print_r($arg_list[0],true).'</pre>';EXIT;
        return $arg_list[0]; //Returns only first parameter
    }
    
    //----- PRODUCT FUNCTIONS -----//
    function buildDataArray($productData,$array,$image)
    {
        $this->function_call = 'buildDataArray';
        return $this->call_module_method($productData,$array,$image); //Return parameter must be in first place
    }

    function productImage($returnName, $name, $type ,$path)
    {
        $this->function_call = 'productImage';
        return $this->call_module_method($returnName, $name, $type ,$path);
    }
    
}