<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2013,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/






include_once(DIR_FS_CATALOG . 'export/idealo_realtime/idealo_shipping.php');
include_once(DIR_FS_CATALOG . 'export/idealo_realtime/idealo_payment.php'); 
include_once(DIR_FS_CATALOG . 'export/idealo_realtime/idealo_universal.php'); 
include_once(DIR_FS_CATALOG . 'export/idealo_realtime/db_connection.php'); 
include_once(DIR_FS_CATALOG . 'export/idealo_realtime/idealo_definition.php');
include_once(DIR_FS_CATALOG . 'export/idealo_realtime/idealo_db_tools.php');

define('IDEALO_CAMPAIGN', '&refID=94511215');

class tools extends idealo_universal{
	
	public $shipping = array();
	
	public $payment = array();
	private $link= ' ';

	private $shippingcomment = '';

	private $campaignSet = false;
	
	private $shop_url = '';
	
	private $image_url ='';
	
	private $warehouse = false;
	
	private $variant_export = false;
	
	private $geo_zone;
	
	public $xml;
	
	public $textdecode = '0'; 
		
	private $hanExists = false;
	
	private $tableProductItemCodesExists = false;
	
	private $codeMpnExists = false;
	
	private $googleExportConditionExists = false;
	
	public function __construct(){

	}

	public function AllNeeded(){
        $db_tolls = new idealo_db_tools_realtime();
        $db_tolls->checkAll();
        
        $this->hanExists = $db_tolls->hanExists;
        
        $this->tableProductItemCodesExists = $db_tolls->tableProductItemCodesExists;
        
        $this->codeMpnExists = $db_tolls->codeMpnExists;
        
        $this->googleExportConditionExists = false;
	    
		$this->getShipping();

		$this->getPayment();
		
		$this->getUrls();
		
		$this->shippingcomment();
				
		$this->checkCampaign();
		
		$this->getMinorderValues();
		
		if($this->getConfigurationValue('MODULE_IDEALO_REALTIME_WAREHOUSE') == '1'){
		    $this->warehouse = true;
		}
		
		if($this->getConfigurationValue('MODULE_IDEALO_REALTIME_VARIANT') == '1'){
		    $this->variant_export = true;
		}

		$this->textdecode = $this->getConfigurationValue('MODULE_IDEALO_REALTIME_CODE');
		
		$this->getCountryZone();
	}
	

	
	private function getCountryZone(){
	    $country_query = xtc_db_query("SELECT `configuration_value`
	 											FROM `" . TABLE_CONFIGURATION . "`
	 											WHERE `configuration_key` = 'STORE_COUNTRY'
	 											LIMIT 1;");
	    $country_zone = xtc_db_fetch_array($country_query);
	    $country_zone = $country_zone['configuration_value'];
	    $zone_query = xtc_db_query("SELECT `geo_zone_id`
	 											FROM `zones_to_geo_zones`
	 											WHERE `zone_country_id` = " . $country_zone . "
	 											LIMIT 1;" );
	    $zone = xtc_db_fetch_array($zone_query);
	    $this->geo_zone = $zone['geo_zone_id'];
	}
	
	public function getMinorderValues(){
		$idealo_Minorder_query = xtc_db_query("select `idealoMinorder` from `idealo_realtime_setting` LIMIT 1");
		$idealo_Minorder_db = xtc_db_fetch_array($idealo_Minorder_query);
		$this->minOrder = $idealo_Minorder_db['idealoMinorder'];
		
		$idealo_idealoMinorderprice_query = xtc_db_query("select `idealoMinorderprice` from `idealo_realtime_setting` LIMIT 1");
		$idealo_idealoMinorderprice_db = xtc_db_fetch_array($idealo_idealoMinorderprice_query);
		$this->minOrderPrice = $idealo_idealoMinorderprice_db['idealoMinorderprice'];
		
		$idealo_idealoMinorderBorder_query = xtc_db_query("select `idealoMinorderBorder` from `idealo_realtime_setting` LIMIT 1");
		$idealo_idealoMinorderBorder_db = xtc_db_fetch_array($idealo_idealoMinorderBorder_query);
		$this->idealoMinorderBorder = $idealo_idealoMinorderBorder_db['idealoMinorderBorder'];
	}

	
	public function getValueIdealoSetting($value){
		$value_query = xtc_db_query("SELECT `" . $value . "` FROM `idealo_realtime_setting`;");
     	$value_db = xtc_db_fetch_array($value_query);
     	return $value_db[$value];
	}

	
	 public function getPayment(){
	 	$idealo_payment = new idealo_payment();
	 	$this->payment = $idealo_payment->payment;
	 	
	 	foreach($this->payment as $pay){
			$payment = array();
			$active = 'idealo_' . $pay['db'] . '_active';
			$payment['active'] = $this->getValueIdealoSetting($active);
			
			$countries = 'idealo_' . $pay['db'] . '_countries';
			$payment['country'] = $this->getValueIdealoSetting($countries);
			
			$fix = 'idealo_' . $pay['db'] . '_fix';
			$payment['fix'] = $this->getValueIdealoSetting($fix);
			
			$percent = 'idealo_' . $pay['db'] . '_percent';
			$payment['percent'] = $this->getValueIdealoSetting($percent);
			
			$shipping = 'idealo_' . $pay['db'] . '_shipping';
			$payment['shipping'] = $this->getValueIdealoSetting($shipping);
			
			$payment['title'] = $pay['title'];
			$payment['db'] = $pay['db'];
			
			$this->payment[$pay['db']] = $payment;	
		}
	 }

	
	public function getShipping(){
		$idealo_shipping = new idealo_shipping();
		$this->shipping = $idealo_shipping->shipping;
		
		foreach ( $this->shipping as $ship ){
			$shipping = array();
     		
     		$active = 'idealo_' . $ship['country'] . '_active';
			$shipping['active'] = $this->getValueIdealoSetting($active);
			
			$costs = 'idealo_' . $ship['country'] . '_costs';
     		$shipping['costs'] = $this->getValueIdealoSetting($costs);
     
     		$free = 'idealo_' . $ship['country'] . '_free';
     		$shipping['free'] = $this->getValueIdealoSetting($free);
			
			$type = 'idealo_' . $ship['country'] . '_type';
     		$shipping['type'] = $this->getValueIdealoSetting($type);
     		
			$shipping['country'] = $ship['country'];
			
			$this->shipping[$ship['country']] = $shipping;
		}
	}


	
	 public function getValue($value){
	 	$result = xtc_db_query ("	SELECT `configuration_value`
									FROM `configuration`
									WHERE `configuration_key` LIKE 'MODULE_IDEALO_REALTIME_" . $value . "';");
		$result = xtc_db_fetch_array($result);
		return $result['configuration_value'];
	 }

 
	public function getLogin(){
		$result = array();
   		$result['user'] = $this->getValue('FILE');
		$result['webservice'] = $this->getValue('URL');
    	$result['password'] = $this->getValue('PASSWORD');
    	$result['idealo_shop_id'] = $this->getValue('SHOP_ID');
    	$result['certificate'] = $this->getValue('CERTIFICATE');
    	$result['pagesize'] = $this->getValue('PAGESIZE');

    	if($result['pagesize'] == ''){
    		$result['pagesize'] = 50;
    	}
    	
    	$result['testmode'] = $this->getValue('TESTMODE');
    	
    	if($result['testmode'] == 'yes'){
    		$result['testmode'] = '1';
    	}else{
    		$result['testmode'] = '0';
    	}
    	
		$result['status'] = $this->getValue('STATUS');
		return $result;
	}


	
	public function newTimestamp(){
		$id = xtc_db_query("SELECT `id` FROM `" . IDEALO_REALTIME_CRON_TABLE . "` LIMIT 1");
 		$id = xtc_db_fetch_array($id);
		xtc_db_query("UPDATE `" . IDEALO_REALTIME_CRON_TABLE . "` SET `create_at` = current_timestamp, `to_execute` = ADDTIME(current_timestamp, '0:30:0') WHERE `id` = " . $id['id'] .";");
	}

	
	
	 public function cleanTableIdealoRealtimeFailedRequest(){
	 	$db_connection = new Idealo_DB_Connection();
	 	
	 	if($db_connection->tableExists('idealo_realtime_failed_request')){
	 		xtc_db_query("TRUNCATE `idealo_realtime_failed_request`");
	 	}
	 }


	
	 public function cleanTableIdealoRealtimeUpdate(){
	 	$db_connection = new Idealo_DB_Connection();
	 	if($db_connection->tableExists('idealo_realtime_update')){
	 		xtc_db_query("TRUNCATE `idealo_realtime_update`");
	 	}
	 }


	
	 public function getUrls(){
	 	$dir = dirname(__FILE__);
	 	$dir = substr($dir, 0, -15);
	 	$url = fopen($dir . "link.ido", "r"); 	
     	$urls =  fgets($url);
     	$urls = explode('|', $urls);
     	$this->shop_url = $urls[0];
     	$this->image_url = $urls[1];
   	 }
	
	
	public function checkCampaign(){
		$campaign_query = xtc_db_query("	SELECT `configuration_value` 
											FROM `" . TABLE_CONFIGURATION . "` 
											WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_CAMPAIGN' 
											LIMIT 1");
		$campaign_db = xtc_db_fetch_array($campaign_query);
				
		if($campaign_db['configuration_value'] != 'no'){
			$this->campaignSet = true;
		}
	}
	
	
	
	public function getShippingcomment(){
		return $this->shippingcomment;
	}
	
	
	
	 public function shippingcomment(){
	 	$shipping_input_query = xtc_db_query("SELECT `configuration_value` 
	 											FROM `" . TABLE_CONFIGURATION . "` 
	 											WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT' 
	 											LIMIT 1");
		$shipping_comment_db = xtc_db_fetch_array($shipping_input_query);
		$this->shippingcomment = $shipping_comment_db['configuration_value'];
	}

	
	
	public function getXMLBegin($testMode){
	    $xml = '<?xml version="1.0" encoding="UTF-8"?>' .
	                     '<offers>'; 
	    if($testMode == '1'){
	        $xml .= '<testMode>true</testMode>';
	    }
	    
	    return $xml;
	}
	
	
	public function getXMLEnd(){
	    return '</offers>';
	}
	
	
	public function getArticle($id){
		$language = 'de';
		$language_id = xtc_db_query("SELECT `languages_id`
								  FROM `languages`
								  WHERE `code` LIKE '" . $language . "';");
		$language_id = xtc_db_fetch_array($language_id);
	
		$export_query = xtc_db_query( " SELECT
				                             p.products_id,
				                             pd.products_name,
				                             pd.products_description,
				                             pd.products_short_description,
				                             p.products_model,
				                             p.products_ean,
				                             p.products_image,
				                             p.products_price,
				                             p.products_status,
				                             p.products_shippingtime,
				                             p.products_tax_class_id,
				                             p.products_weight,
				                             m.manufacturers_name,
				                             p.products_vpe_value,
				                             p.products_vpe_status,
				                             p.products_vpe,
		                                     p.products_quantity
				                         FROM
				                             " . TABLE_PRODUCTS . " p LEFT JOIN
				                             " . TABLE_MANUFACTURERS . " m
				                           ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
				                             " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                           ON p.products_id = pd.products_id AND
				                            pd.language_id = '" . $language_id [ 'languages_id' ] . "' LEFT JOIN
				                             " . TABLE_SPECIALS . " s
				                           ON p.products_id = s.products_id
				                         WHERE
				                         	p.products_id = " . $id . "	  
				                         ORDER BY
				                            p.products_date_added DESC,
				                            pd.products_name" );
	                            
		return xtc_db_fetch_array($export_query);
	}


	
	 public function cleanTestFile(){
	 	$path = DIR_FS_CATALOG.IDEALO_REALTIME;
    	$path = substr($path, 0 , -16);
    	
	 	$fp = fopen($path . 'idealo_realtime_test.csv', "w");
	 	@chmod($path . 'idealo_realtime_test.csv', 0666);
        fputs($fp, 'empty');
        fclose($fp);
        
        $fp = fopen($path . 'idealo_realtime_test.html', "w");
        fputs($fp, 'no errors');
        fclose($fp);
	 }

	 public function getSelectedOptionArray(){
	     $selectionInDB = xtc_db_query("SELECT `idealoExportAttributes` FROM `idealo_realtime_setting` LIMIT 1");
	     $selectionInDB = xtc_db_fetch_array($selectionInDB);
	     return explode(',', $selectionInDB['idealoExportAttributes']);
	 }

	 public function getExtra($oldValue, $prefix, $value){
	     switch($prefix){
	         case '+':
	             return $oldValue + $value;
	         case '-':
	             return $oldValue - $value;
	         case '*':
	             return $oldValue * $value;
	         case '/':
	             return $oldValue / $value;
	         case '=':
	             return $value;
	     }
	 }
	 
	 
	 
	 private function getVariant($id){
	     $variant = array();
	     $this->result = array();
	      
	     $selectionInDB = $this->getSelectedOptionArray();
	     $count = count($this->attributes);
	      
	     $attributes = xtc_db_query("SELECT `options_id`, `options_values_id`, `options_values_price`, `price_prefix`, `attributes_model`, `attributes_stock`, `options_values_weight`, `weight_prefix`
								   			 FROM `products_attributes`
								   			 WHERE `products_id` = '". $id ."'
								   			 ORDER BY `options_id` ASC;");
	     $result_array = array();
	     $product_attributes = array();
	 
	     while($att = xtc_db_fetch_array($attributes)){
	         if(in_array($att['options_id'], $selectionInDB)){
	             $result_array[] = $att;
	             $product_attributes [] = $att['options_id'];
	         }
	     }
	 
	     $product_attributes = array_unique($product_attributes);
	     $same = array();
	     foreach($product_attributes as $att){
	         foreach($result_array as $re){
	             if($re['options_id'] == $att){
	                 $same[$att][] = $re;
	             }
	         }
	     }
	     	
	     $same2 = array();
	 
	     foreach($same as $sa){
	         $same2 [] = $sa;
	     }
	 
	     $this->getProductOption($same2);
	     return $this->result;
	 }
	 
	 
	 private $attributes = array();
	 
	 private function getProductOption($array, $re_array = array()){
	     $result = array();
	     $keys = array_keys($array);
	     if(count($array) > 1){
	         $array0 = $array[$keys[0]];
	         unset($array[$keys[0]]);
	         	
	         for($i = 0; $i < count($array0); $i++){
	             $result_array = $re_array;
	             $result_array [] = $array0[$i];
	             $this->getProductOption($array, $result_array);
	         }
	     }else{
	         for($i = 0; $i < count($array[$keys[0]]); $i++){
	             $result_array = $re_array;
	             $result_array [] = $array[$keys[0]][$i];
	             $this->result [] = $result_array;
	         }
	     }
	 }
	 
	 
	 
	 public function getAttributeOption($id){
	     $language = 'de';
	     
	     $language_id = xtc_db_query("SELECT `languages_id`
								  FROM `languages`
								  WHERE `code` LIKE '" . $language . "';");
	     $language_id = xtc_db_fetch_array ( $language_id );
	     
	     $option = xtc_db_query("SELECT `products_options_name`
	  	 								FROM `products_options`
	  	 								WHERE `products_options_id` = '" . $id . "' AND `language_id` = '" . $language_id['languages_id'] . "';");
	     $result = xtc_db_fetch_array($option);
	     return $result['products_options_name'];
	 }

	 
	 public function getAttributeValue($id){
	     $language = 'de';
	     
	     $language_id = xtc_db_query("SELECT `languages_id`
								  FROM `languages`
								  WHERE `code` LIKE '" . $language . "';");
	     $language_id = xtc_db_fetch_array ( $language_id );
	 
	     $optionValue = xtc_db_query("SELECT `products_options_values_name`
	  	 								FROM `products_options_values`
	  	 								WHERE `products_options_values_id` = '" . $id . "' AND `language_id` = '" . $language_id['languages_id'] . "';");
	     $result = xtc_db_fetch_array($optionValue);
	     return $result['products_options_values_name'];
	 }
	 
	 
	 private function productOption($id){
	     $selectionInDB = $this->getSelectedOptionArray();
	 
	     $attributes = xtc_db_query("SELECT `options_id`
								   			 FROM `products_attributes`
								   			 WHERE `products_id` = '". $id ."';");
	 
	     $attributeArray = array();
	     while($att = xtc_db_fetch_array($attributes)){
	         $attributeArray[] = $att;
	     }
	 
	     if(empty($attributeArray)){
	         return false;
	     }
	 
	     $attArray = array();
	 
	     foreach($attributeArray as $att){
	         if(in_array($att['options_id'], $selectionInDB)){
	             $attArray[] = $att;
	         }
	     }
	 
	 
	     if(!empty($attArray)){
	         return true;
	     }else{
	         return false;
	     }
	 }
	 
	 
	public function getXML($id){
	    if($id != ''){
            $xml = '';
            
		    if($this->variant_export === false){
		        $options = false;
		    }else{
		        $options = $this->productOption($id);
		    }

		    if($options !== false){
		        
		        $optionNumber = 0;
		    
		        $option = $this->getVariant($id);
		        
		        foreach($option as $op){
		            if($optionNumber >= 20){
		                break;
		            }
		            	
		            $optionNumber++;
		            $exrtaPrice = '0';
		            $extraWeight = '0';
		            $extraName = '';
		            $stock = 0;

		            foreach($op as $o){
		                $extraName .= $this->getAttributeOption($o['options_id']). ': ' . $this->getAttributeValue($o['options_values_id']) . '; ';
		                $exrtaPrice = $this->getExtra( $exrtaPrice, $o['price_prefix'], $o['options_values_price']);
		                $extraWeight = $this->getExtra( $extraWeight, $o['weight_prefix'], $o['options_values_weight']);
		                $stock = $this->getExtra( $stock, '+', $o['attributes_stock']);
		            }

		            $xml .= $this->getXMLValues('1',
		                    $id,
		                    substr($extraName, 0, -2),
		                    $exrtaPrice,
		                    $extraWeight,
		                    $optionNumber,
		                    $stock
		            );
		        }
		    }else{
		        $xml = $this->getXMLValues('0', $id);
		    }

		    return $xml;
		}else{
			return '';
		}
    }
	 
    public function getTax($tax,$id){
        $value = xtc_db_query(" SELECT `tax_rate`
								  FROM `tax_rates`
								  WHERE `tax_class_id` = " . $tax . "
								  		AND `tax_zone_id` = " . $this->geo_zone . ";");
        $value = xtc_db_fetch_array($value);
         
        return $value['tax_rate'];
    }
    
    
    public function getXMLValues($variant, $id, $extraName = '', $exrtaPrice = 0, $extraWeight = 0, $optionNumer = '', $stock = ''){
        $xml = '';
        $db_tolls = new idealo_db_tools_realtime();
        $db_tolls->checkAll();
        
        $products = $this->getArticle($id);

        $products_price = $products['products_price'] + $exrtaPrice;
		$products_price = $this->getPrice($products['products_tax_class_id'], $products['products_price'], $id);	
		$tax = $this->getTax($products['products_tax_class_id'], $id);
		$exrtaPrice = $exrtaPrice * (1 + $tax / 100);
		$products_price = $products_price + $exrtaPrice;
			
		if((float)$products['products_discount_allowed'] > 0.00){
		    $products_price = $products_price * (1 - ($products['products_discount_allowed'] / 100));
		}
        $categorie_query = xtc_db_query("	SELECT
	                                            categories_id
	                                            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
	                                            WHERE products_id = '" . $products['products_id'] . "'
	                                            ORDER BY categories_id DESC;" );
        
        $categories = '0';
        
        while($categorie_data = xtc_db_fetch_array($categorie_query)){
            if($categorie_data['categories_id'] != '0'){   
                $categories = $categorie_data['categories_id'];
            }
        }
        
        $cat = $this->buildCAT($categories, $id);
        
        $export = true;
        
        if($this->warehouse === true){
            if ($variant == 1){
                if ($stock == 0){
                    $export = false;
                }
            }elseif((float) $products [ 'products_quantity' ] <= (float) 0){
                $export = false;
            }
        }
        
        if( $products['products_status'] == 1
           && $products_price > 0.00
           && $this->filter($id, $products['manufacturers_name']) === true
           && $this->filterCat($cat) === true
           && $export
        ){
            
            $language = 'de';
            $language_id = xtc_db_query("SELECT `languages_id`
										  FROM `languages`
										  WHERE `code` LIKE '" . $language . "';");
            
            $language_id = xtc_db_fetch_array($language_id);
            
            if($optionNumer != ''){
                $sku = $id . '-' .  $optionNumer;
            }else{
                $sku = $id;
            }
            
            $url = $this->shop_url . DIR_WS_CATALOG . 'product_info.php?' . xtc_product_link($products['products_id'], $products['products_name']);
            
            if($this->campaignSet === true){
                $url .= IDEALO_CAMPAIGN;
            }
            
            $price = number_format($products_price, 2, '.', '');
            
            if($products['products_image'] != ''){
                $image = $this->shop_url . $this->image_url . $products['products_image'];
            }else{
                $image = '';
            }           
            
            $xml .= '<offer>' .
                        '<command>InsertOrReplace</command>' .
                        '<sku><![CDATA[' . $sku . ']]></sku>' .
                        '<title><![CDATA[' . $this->cleanText($products['products_name'] . ' ' . $extraName , 200, $this->textdecode) . ']]></title>' .
                        '<url><![CDATA[' . $this->cleanText($url, 300, $this->textdecode) . ']]></url>' .
                        '<price>' . $price . '</price>' . 
                        '<category><![CDATA[' . $cat . ']]></category>';

            if($image != ''){
                $xml .= '<image><![CDATA[' . $image . ']]></image>';        
            }
                        
            if($products['manufacturers_name'] != ''){
                $xml .= '<brand><![CDATA[' . $this->cleanText($products['manufacturers_name'], 100, $this->textdecode) . ']]></brand>';
            }
            
            if($products['products_description'] != ''){
                $products['products_description'] = $this->prepareText($products['products_description']);
                $xml .= '<description><![CDATA[' . $this->cleanText($products['products_description'], 1000, $this->textdecode) . ']]></description>';
            }

            $shippingTime = $this->getShippingTime($products['products_shippingtime'], $language_id['languages_id']);
            if($shippingTime != ''){
                $xml .= '<delivery><![CDATA[' . $this->cleanText($shippingTime, 100, $this->textdecode) . ']]></delivery>';
            }
            if($this->checkEan($products['products_ean'])){
                $xml .= '<ean><![CDATA[' . $products['products_ean'] . ']]></ean>';
            }
        
            if($products['products_vpe_status'] == '1'  && (float)$products['products_vpe_value'] > 0){
                $vpe = $this->getVPE($products['products_vpe'], $language_id['languages_id']);
                $xml .= '<basePrice measure="' . $vpe['measure'] . '" unit="' . $this->cleanText($vpe['unit'], 100, $this->textdecode) . '">0.99</basePrice>';
            }

            foreach($this->shipping as $ship){
                if($ship['active'] == '1'){
                    $costs = $this->getShippingCosts($price, $products['products_weight'], $ship);

                    if($this->minOrderPrice != ''){
                        if($this->checkMinExtraPrice($price)){
                            $costs = $costs + $this->minOrderPrice;
                        }    
                    }
                }

                foreach($this->payment as $payment){
                    if($payment['active'] == '1'){
                        $payment_coutry = 'DE';
                        if($payment['country'] == '1'){
                            $payment_coutry = 'DE';
                        }
                        
                        if($payment['country'] == '2'){
                            $payment_coutry = 'AT';
                        }
                        
                        if($payment['country'] == '3'){
                            $payment_coutry = 'DE/AT';
                        }

                        if(strpos($payment_coutry, $ship['country']) !== false){
                            $xml .= '<shipping context="' . $ship['country'] . '" type="' . $payment['db'] . '">' . $this->getPaymentCosts($payment, $ship['title'], $price, $costs) . '</shipping>';
                        }   	
                    }
                }	
            }
        
            $portocoment = $this->shippingcomment;
            
            if($this->checkMinOrder($price)){
                $portocoment = IDEALO_REALTIME_MIN_ORDER .  number_format($this->minOrder, 2, '.', '') . ' EUR';
            }
             
            if($this->minOrderPrice != ''){
                if($this->checkMinExtraPrice($price)){
                    $portocoment = number_format($this->minOrderPrice, 2, '.', '') .
                    IDEALO_REALTIME_MIN_ORDER_EXTRA_PRICE .
                    number_format($this->idealoMinorderBorder, 2, '.', '') .
                    IDEALO_REALTIME_SUM;
                }
            }
            $xml .= '<shippingComment><![CDATA[' . $this->cleanText($portocoment, 100, $this->textdecode). ']]></shippingComment>';
            
            if($extraName != ''){
                $attribute_array = explode(';', $extraName);
                if(count($attribute_array) > 0){
                    $xml .= '<attributes>';
                    foreach($attribute_array as $attr_array){
                        $attr_array = explode(':', $attr_array);
                        $xml .= '<attribute name="' . $this->cleanText($attr_array[0], 50, $this->textdecode) . '">' .
                                    '<value>' . $this->cleanText($attr_array[1], 50, $this->textdecode) . '</value>' .
                                '</attribute>';
                    }
                    $xml .= '</attributes>>';
                }
            }    

            if($this->hanExists || $this->codeMpnExists){
                $hanInDb = '';
                if($this->hanExists){
                    $hanInDb = $db_tolls->getHAN($id);
                }
                
                if($this->codeMpnExists){
                    $hanInDb = $db_tolls->getValueTableProductsItemCodes($id, 'code_mpn');
                }
                
                $xml .= '<han><![CDATA[' . $hanInDb . ']]></han>';
            }

            $xml .= '</offer>';
            
        }elseif($id != ''){
            if($optionNumer != ''){   
                $sku = $id . '-' .  $optionNumer;
            }else{    
                $sku = $id;
            }
            
            $sxml .= $this->deleteProductAtIdealo($sku);
        }
        
        return $xml;
    }
	 
    
    public function deleteProductAtIdealo($sku){
        return '<offer>' .
                   '<command>DELETE</command>' .
                   '<sku><![CDATA[' . $sku . ']]></sku>' .
                '</offer>';
    } 
    
    public function XMLAddChildAtOffers($offer){
        $offers = $this->xml->getElementsByTagName('offers');
        $offers->item(0)->appendChild($offer);
    }
    
    public function getHAN($id){
        $han = xtc_db_query("SELECT `products_manufacturers_model`
	  	 								FROM `products`
	  	 								WHERE `products_id` = '" . $id . "';");
    
        $han = xtc_db_fetch_array($han);
         
        return $han['products_manufacturers_model'];
    }
    
   
	  
    public function getPaymentCosts($payment, $country, $price, $shipping){														 
		$costs = $shipping;
		if($payment['fix'] != ''){
			$costs = $costs + $payment['fix'];
		}
		
		if($payment['percent'] != ''){
			if($payment['shipping'] == '1'){
				$costs = $costs + (($price + $costs) * $payment['percent'] / 100) ;
			}else{
				$costs = $costs + ($price * $payment['percent'] / 100);
			}
		}
		
		return number_format((float)$costs, 2, '.', '');
    }
    
    
	 
	 public function getShippingCosts ( $price, $weight, $ship ){
	 	if ( $ship [ 'free' ] != '' ){
	 		
	 		if ( ( float ) $price >= ( float ) $ship [ 'free' ] ){
	 			
	 			return 0;
	 			
	 		}
	 		
	 	}
	 	if ( $ship [ 'type' ] == '3' ){
	 		
	 		return $ship [ 'costs' ];
	 		
	 	}

	 	$costs = explode ( ';', $ship [ 'costs' ] );
	 	$value = '';
	 	
	 	if ( $ship [ 'type' ] == '1' ){
	 		
	 		$value = $weight;
	 		
	 	}else{
	 		
	 		$value = $price;
	 		
	 	}	 	
	 	
	 	for ( $i = 0; $i < count ( $costs ); $i++ ){
	 		
	 		$co = explode ( ':', $costs [ $i ] );
	 		
	 		if ( ( count ( $costs) - 1 ) == $i ){
	 			
	 			return $co [1];
	 			
	 		}
	 		
	 		if ( ( float ) $value <= ( float ) $co [0] ){
	 			
	 			return $co [1];
	 			
	 		}
	 		
	 	} 
	 	
	 }
	 
	 
	  public function getPrice($tax, $price, $id){
	  	$value = xtc_db_query(" SELECT `tax_rate`
								  FROM `tax_rates`
								  WHERE `tax_class_id` = " . $tax . " 
								  		AND `tax_zone_id` = 5;");
	 	$value = xtc_db_fetch_array($value);
	 	$value = $value['tax_rate'];
	 	
	 	$special = xtc_db_query("	SELECT	`specials_new_products_price`
	                                FROM `specials`
	                                WHERE `products_id` = " . $id . " 
	                                	  AND `status` = 1;");
                                            
        $special = xtc_db_fetch_array($special);
            
        if(!empty($special)){
           	$price = $special['specials_new_products_price'];
        }
	 	
	 	$price = $price * (1 + $value / 100);
	 	return(float)$price; 
	  }
	 
	 
	  public function getShippingTime($id, $la_id){
	  	$value = xtc_db_query(" SELECT `shipping_status_name`
								  FROM `shipping_status`
								  WHERE `shipping_status_id` = " . $id . " 
								  		AND `language_id` = " . $la_id . ";");
	 	
	 	$value = xtc_db_fetch_array($value);
	 	return $value['shipping_status_name'];
	  }	
	
	
	 public function getVPE($product_vpe, $language = '1'){
	 	$vpe = xtc_db_query(" SELECT `products_vpe_name` 
	 							FROM `products_vpe` 
	 							WHERE `products_vpe_id` = " . $product_vpe . " 
	 								  AND `language_id` = " . $language . ";");	
	 								  
	 	$vpe = xtc_db_fetch_array($vpe);
	 	$vpe = explode(' ', $vpe['products_vpe_name']);
	 	
	 	if(count($vpe) == 1){
	 		$vpe['measure'] = '1';	 		
	 		$vpe['unit'] = utf8_encode($vpe['0']);
	 	}else{
	 		$vpe['measure'] =  $vpe['0'];	 		
	 		$vpe['unit'] = utf8_encode($vpe['1']);
	 	}
	 	
		return $vpe;
	 }
	
	
   private function buildCAT($catID, $product_id){
		if(isset($this->CAT[$catID])){
		 return $this->CAT[$catID];
		}else{
            if($catID == '0'){
            	$new_cat = xtc_db_query(" SELECT MAX(`categories_id`)
            							  FROM `products_to_categories`
            							  WHERE `products_id` = '" . $product_id . "';");
            	$new_cat = xtc_db_fetch_array($new_cat);
            
            	$catID = $new_cat['MAX(`categories_id`)'];
            }
            
            $language = 'de';		
            $language_id = xtc_db_query("SELECT `languages_id`
            							  FROM `languages`
            							  WHERE `code` LIKE '" . $language . "';");
            $language_id = xtc_db_fetch_array($language_id);
            $cat = array();
            $tmpID=$catID;
            
            while($this->getParent($catID) != 0 || $catID != 0){
                $cat_select = xtc_db_query(" 	SELECT `categories_name` 
                								FROM `".TABLE_CATEGORIES_DESCRIPTION."` 
                								WHERE `categories_id` = '" . $catID . "' 
                									  AND `language_id` = '" . $language_id['languages_id'] . "'");
                $cat_data = xtc_db_fetch_array($cat_select);
            	$catID = $this->getParent($catID);
            	$cat[] = $this->cleanText($cat_data['categories_name'], 100, $this->textdecode);
            }
            
            $catStr = '';
            for($i = count($cat); $i > 0;$i--){
                $catStr .= $cat[$i - 1] . ' -> ';
            }
		   
            $this->CAT[$tmpID] = substr($catStr, 0, -4);
            return $this->CAT [ $tmpID ];
		}
    }
    
    
    
    private function getParent($catID){
        if(isset($this->PARENT[$catID])){
            return $this->PARENT[$catID];
        }else{
            $parent_query = xtc_db_query(" SELECT `parent_id` 
       									FROM `" . TABLE_CATEGORIES . "` 
       									WHERE `categories_id` = '" . $catID . "'");
            $parent_data = xtc_db_fetch_array($parent_query);
            $this->PARENT [ $catID ] = $parent_data [ 'parent_id' ];
            return $parent_data['parent_id'];
        }
    }
    
    
    public function checkActive() {
    	$check_query = xtc_db_query(" SELECT `configuration_value` 
    									FROM `" . TABLE_CONFIGURATION . "` 
    									WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_STATUS'");
    	$check = xtc_db_fetch_array($check_query);
    	return $check['configuration_value'];
    }
    
    
     public function getEmail(){
     	$email_query = xtc_db_query("	SELECT
     										`customers_email_address`
     								  	FROM
     								  		`customers`,
     								  		`customers_status`,
     								  		`languages`
     								  	WHERE
     								  		`customers_status` = `customers_status_id`
     								  			AND
											`customers_status_name` LIKE 'Admin'
												AND
											`languages_id` = `languages_id`
												AND
											`code` LIKE 'de'
										LIMIT 1;
									");
    	
    	$email = xtc_db_fetch_array($email_query);
    	return $email['customers_email_address'];
     }
     
     
     
	private function isIn($value, $array){
		$array = explode(';', $array);
		foreach($array as $a){
			if($a == $value){ 
				return true;
			}
		}
		
		return false;
	}


	
	private function filter($id, $brand){
		if(IDEALO_REALTIME_BRAND_FILTER_VALUE != ''){
			$isIn = $this->isIn ( $brand, IDEALO_REALTIME_BRAND_FILTER_VALUE );
			if(IDEALO_REALTIME_BRAND_EXPORT == 'export'){
				if($isIn === true){
					return true;
				}else{
					return false;
				}
			}
					
			if(IDEALO_REALTIME_BRAND_EXPORT == 'filter'){
				if($isIn === true){
					return false;
				}
			}
		}
		
		if(IDEALO_REALTIME_ARTICLE_FILTER_VALUE != ''){
			$isIn = $this->isIn($id, IDEALO_REALTIME_ARTICLE_FILTER_VALUE);
			if(IDEALO_REALTIME_ARTICLE_EXPORT == 'export'){
				if($isIn === true){
					return true;
				}else{
					return false;
				}
			}
					
			if(IDEALO_REALTIME_ARTICLE_EXPORT == 'filter'){
				if($isIn === true){
					return false;
				}
			}
		}
		
		return true;
	}


	
	 public function filterCat($cat){
	 	if(IDEALO_REALTIME_CAT_FILTER_VALUE != ''){
	 		$cat_filter = explode(';', IDEALO_REALTIME_CAT_FILTER_VALUE);
	 		foreach($cat_filter as $ca){
	 			if(strpos($cat, $ca) !== false){
	 				if(IDEALO_REALTIME_CAT_EXPORT == 'export'){
	 					return true;
	 				}else{
	 					return false;
	 				}
	 				if(IDEALO_REALTIME_CAT_EXPORT == 'filter'){
	 					return false;
	 				}
	 			}
	 		}	
		}
		
		if(IDEALO_REALTIME_CAT_FILTER_VALUE != '' && IDEALO_REALTIME_CAT_EXPORT == 'export'){
			return false;
		}
		
		return true;
	 }
     
	 public function getConfigurationValue($key){
	     $value_query = xtc_db_query("SELECT `configuration_value`
	 								 FROM `" . TABLE_CONFIGURATION . "`
	 								 WHERE `configuration_key` = '" . $key . "'
	 								 LIMIT 1;");
	     $value = xtc_db_fetch_array($value_query);
	     return $value['configuration_value'];
	 }
    
}
