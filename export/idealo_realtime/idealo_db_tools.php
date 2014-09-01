<?php

class idealo_db_tools_realtime{

    public $hanExists = false;
    
    public $tableProductItemCodesExists = false;
    
    public $codeMpnExists = false;
    
    public $googleExportConditionExists = false;
    
    
    public function checkAll(){
        $this->hanExists = $this->checkFieldInTableExists('products', 'products_manufacturers_model');
        
        $this->checkTableProductItemCodesExists();
        if($this->tableProductItemCodesExists){
            $this->codeMpnExists = $this->checkFieldInTableExists('products_item_codes', 'code_upc');
            $this->googleExportConditionExists = $this->checkFieldInTableExists('products_item_codes', 'google_export_condition');
        }
        
    }
    
    public function checkTableProductItemCodesExists(){
        $value_query = xtc_db_query("show tables like 'products_item_codes';");
        $value = xtc_db_fetch_array($value_query);

        if(is_array($value)){            
            $this->tableProductItemCodesExists = true;
        }
    }
    
    public function checkFieldInTableExists($table, $field){
        $value_query = xtc_db_query("show columns from " . $table . " like '" . $field . "';");
        $value = xtc_db_fetch_array($value_query);
        
        if($value !== false){
            return true;
        }

        return false;
    }
    
    
    public function getValueTableProductsItemCodes($id, $column){
        $value = xtc_db_query("SELECT `" . $column . "`
	  	 								FROM `products_item_codes`
	  	 								WHERE `products_id` = '" . $id . "';");
        
        $value = xtc_db_fetch_array($value);
        return $value[$column];
    }
    
    public function getHAN($id){
        $han = xtc_db_query("SELECT `products_manufacturers_model`
	  	 								FROM `products`
	  	 								WHERE `products_id` = '" . $id . "';");
    
        $han = xtc_db_fetch_array($han);
         
        return $han['products_manufacturers_model'];
    }
    
}
?>