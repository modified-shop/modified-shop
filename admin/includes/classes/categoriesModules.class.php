<?php

class categoriesModules {
    var $modules;
    function __construct()
    {
        $module_directory = DIR_FS_ADMIN.'includes/extra/classes/categories/';
        $this->modules = array();
        if (defined('MODULE_CATEGORIES_INSTALLED') && xtc_not_null(MODULE_CATEGORIES_INSTALLED)) {
          $modules = explode(';', MODULE_CATEGORIES_INSTALLED);
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
        //echo '<pre>'.print_r($arg_list,true).'</pre>';
        //echo '<pre>'.$function_call.'</pre>';
        if (is_array($this->modules)) {
            reset($this->modules);
            foreach($this->modules as $class) {
                //echo  '<pre>'.$class.'</pre>';
                if (is_callable(array($GLOBALS[$class], $function_call))) {
                    $arg_list[0] = call_user_func_array(array($GLOBALS[$class], $function_call), $arg_list); //Call the $GLOBALS[$class]->$function_call() method with $arg_list
                }
            }
        }
        //echo '<pre>'.print_r($arg_list[0],true).'</pre>';EXIT;
        return $arg_list[0];  //Returns only first parameter
    }

    //----- CATEGORIES FUNCTIONS -----//
    function insert_category_before($sql_data_array,$categories_data)
    {
        $this->function_call = 'insert_category_before';
        return $this->call_module_method($sql_data_array,$categories_data); //Return parameter must be in first place
    }

    function insert_category_after($categories_data,$categories_id)
    {
        $this->function_call = 'insert_category_after';
        $this->call_module_method($categories_data,$categories_id);
    }

    function insert_category_desc($sql_data_array,$categories_data,$categories_id,$language_id)
    {
        $this->function_call = 'insert_category_desc';
        return $this->call_module_method($sql_data_array,$categories_data,$categories_id,$language_id); //Return parameter must be in first place
    }

    function copy_category($sql_data_array,$src_category_id,$dest_category_id,$ctype)
    {
        $this->function_call = 'copy_category';
        return $this->call_module_method($sql_data_array,$src_category_id,$dest_category_id,$ctype); //Return parameter must be in first place
    }

    function copy_category_desc($sql_data_array,$src_category_id,$dest_category_id,$ctype,$new_cat_id)
    {
        $this->function_call = 'copy_category_desc';
        return $this->call_module_method($sql_data_array,$src_category_id,$dest_category_id,$ctype,$new_cat_id); //Return parameter must be in first place
    }

    function remove_category($category_id)
    {
        $this->function_call = 'remove_category';
        $this->call_module_method($category_id);
    }
    
    function delete_category_image($category_image)
    {
        $this->function_call = 'delete_category_image';
        $this->call_module_method($category_image);
    }
    
    function copy_category_image($src_pic, $dest_pic)
    {
        $this->function_call = 'copy_category_image';
        $this->call_module_method($src_pic, $dest_pic);
    }
    
    function categories_image_process($categories_image_name, $categories_image_name_process)
    {
        $this->function_call = 'categories_image_process';
        $this->call_module_method($categories_image_name, $categories_image_name_process);
    }

    //----- PRODUCTS FUNCTIONS -----//
    function insert_product_before($sql_data_array,$products_data)
    {
        $this->function_call = 'insert_product_before';
        return $this->call_module_method($sql_data_array,$products_data); //Return parameter must be in first place
    }

    function insert_product_after($products_data,$products_id)
    {
        $this->function_call = 'insert_product_after';
        $this->call_module_method($products_data,$products_id);
    }

    function insert_product_desc($sql_data_array,$products_data,$products_id,$language_id)
    {
        $this->function_call = 'insert_product_desc';
        return $this->call_module_method($sql_data_array,$products_data,$products_id,$language_id); //Return parameter must be in first place
    }

    function remove_product($products_id)
    {
        $this->function_call = 'remove_product';
        $this->call_module_method($products_id);
    }

    function delete_product($product_id,$product_categories)
    {
        $this->function_call = 'delete_product';
        $this->call_module_method($product_id,$product_categories);
    }

    function duplicate_product_before($sql_data_array,$src_products_id,$dest_categories_id)
    {
        $this->function_call = 'duplicate_product_before';
        return $this->call_module_method($sql_data_array,$products_data,$products_id,$language_id); //Return parameter must be in first place
    }

    function duplicate_product_after($sql_data_array,$src_products_id,$dest_categories_id,$dup_products_id)
    {
        $this->function_call = 'duplicate_product_after';
        return $this->call_module_method($sql_data_array,$src_products_id,$dest_categories_id,$dup_products_id); //Return parameter must be in first place
    }

    function duplicate_product_desc($sql_data_array,$src_products_id,$dest_categories_id,$dup_products_id)
    {
        $this->function_call = 'duplicate_product_desc';
        return $this->call_module_method($sql_data_array,$src_products_id,$dest_categories_id,$dup_products_id); //Return parameter must be in first place
    }
    
    function image_name($image_name,$products_id, $counter, $suffix, $pname_arr, $srcID)
    {
        $this->function_call = 'image_name';
        return $this->call_module_method($image_name,$products_id, $counter, $suffix, $pname_arr, $srcID); //Return parameter must be in first place
    }
}