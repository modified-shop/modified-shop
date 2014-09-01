<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/






include_once ( DIR_FS_CATALOG . 'export/idealo/idealo_csv_shipping.php' );
include_once ( DIR_FS_CATALOG . 'export/idealo/idealo_csv_payment.php' ); 
include_once ( DIR_FS_CATALOG . 'export/idealo/idealo_csv_universal.php' ); 
include_once ( DIR_FS_CATALOG . 'export/idealo/idealo_csv_definition.php' ); 


define ( 'IDEALO_CAMPAIGN', '94511215' );

class idealo_csv_tools extends idealo_csv_universal{
		
	public $shipping = array();
	
	public $payment = array();

	private $shippingcomment = '';

	private $campaignSet = false;
	
	private $shop_url = '';
	
	private $image_url ='';
	
	private $geo_zone = '';

	public function __construct(){

	}
	

	public function AllNeeded(){
		
		$this->getShipping();

		$this->getPayment();
		
		$this->getUrls();
		
		$this->shippingcomment();
				
		$this->checkCampaign();
		
		$this->getCountryZone ();
		
		$this->getMinorderValues();
		
	}
	
	
	public function getMinorderValues(){

		$idealo_Minorder_query = xtc_db_query("select `idealoMinorder` from `idealo_csv_setting` LIMIT 1");
		$idealo_Minorder_db = xtc_db_fetch_array($idealo_Minorder_query);
		$this->minOrder = $idealo_Minorder_db['idealoMinorder'];

		$idealo_idealoMinorderprice_query = xtc_db_query("select `idealoMinorderprice` from `idealo_csv_setting` LIMIT 1");
		$idealo_idealoMinorderprice_db = xtc_db_fetch_array($idealo_idealoMinorderprice_query);
		$this->minOrderPrice = $idealo_idealoMinorderprice_db['idealoMinorderprice'];
		
		$idealo_idealoMinorderBorder_query = xtc_db_query("select `idealoMinorderBorder` from `idealo_csv_setting` LIMIT 1");
		$idealo_idealoMinorderBorder_db = xtc_db_fetch_array($idealo_idealoMinorderBorder_query);
		$this->minorderBorder = $idealo_idealoMinorderBorder_db['idealoMinorderBorder'];
		
	}
	
	
	 private function getCountryZone (){
	 	
	 	$coutry_query = xtc_db_query ( "SELECT `configuration_value` 
	 											FROM `" . TABLE_CONFIGURATION . "` 
	 											WHERE `configuration_key` = 'STORE_COUNTRY' 
	 											LIMIT 1;" );
	 													
     	$coutry_zone = xtc_db_fetch_array ( $coutry_query );
     	
     	$coutry_zone = $coutry_zone [ 'configuration_value' ];
     	
     	$zone_query = xtc_db_query ( "SELECT `geo_zone_id` 
	 											FROM `zones_to_geo_zones` 
	 											WHERE `zone_country_id` = " . $coutry_zone . " 
	 											LIMIT 1;" );
     	
     	$zone = xtc_db_fetch_array ( $zone_query );
     	     	
     	$this->geo_zone = $zone [ 'geo_zone_id' ];
	 	
	 }
	
	

	
	public function getValueIdealoSetting( $value ){
		
		$value_query = xtc_db_query ( "SELECT `" . $value . "` FROM `idealo_csv_setting`;" );
     	$value_db = xtc_db_fetch_array ( $value_query );
     	
     	return $value_db [ $value ];
     	
	}

	
	 public function getPayment(){
	 	
	 	$idealo_payment = new idealo_csv_payment();
	 	$this->payment = $idealo_payment->payment;
	 	
	 	foreach ( $this->payment as $pay ){
			
			$payment = array();
			
			$active = 'idealo_' . $pay [ 'db' ] . '_active';
			$payment [ 'active' ] = $this->getValueIdealoSetting ( $active );
			
			$countries = 'idealo_' . $pay [ 'db' ] . '_countries';
			$payment [ 'country' ] = $this->getValueIdealoSetting ( $countries );
			
			$fix = 'idealo_' . $pay [ 'db' ] . '_fix';
			$payment [ 'fix' ] = str_replace ( ",", ".", $this->getValueIdealoSetting ( $fix ) );
			
			$percent = 'idealo_' . $pay [ 'db' ] . '_percent';
			$payment [ 'percent' ] = str_replace ( ",", ".", $this->getValueIdealoSetting ( $percent ) );
			
			$shipping = 'idealo_' . $pay [ 'db' ] . '_shipping';
			$payment [ 'shipping' ] = $this->getValueIdealoSetting ( $shipping );
			
			$max = 'idealo_' . $pay [ 'db' ] . '_max';
			$payment [ 'max' ] = str_replace ( ",", ".", $this->getValueIdealoSetting ( $max ) );

			$payment [ 'title' ] = $pay [ 'title' ];
			$payment [ 'db' ] = $pay [ 'db' ];
			
			$this->payment [ $pay [ 'db' ] ] = $payment;	
			
		}
				
	 }

	
	public function getShipping(){
		
		$idealo_shipping = new idealo_csv_shipping();
		$this->shipping = $idealo_shipping->shipping;
		
		foreach ( $this->shipping as $ship ){
			
			$shipping = array();
     		
     		$active = 'idealo_' . $ship [ 'country' ] . '_active';
			$shipping [ 'active' ] = $this->getValueIdealoSetting( $active );
			
			$costs = 'idealo_' . $ship [ 'country' ] . '_costs';
     		$shipping [ 'costs' ] = str_replace ( ",", ".", $this->getValueIdealoSetting( $costs ) );
     
     		$free = 'idealo_' . $ship [ 'country' ] . '_free';
     		$shipping [ 'free' ] = str_replace ( ",", ".", $this->getValueIdealoSetting( $free ) );
			
			$type = 'idealo_' . $ship [ 'country' ] . '_type';
     		$shipping [ 'type' ] = $this->getValueIdealoSetting( $type );
     		
			$shipping [ 'country' ] = $ship [ 'country' ];
			
			$this->shipping	[ $ship [ 'country' ] ] = $shipping;
			
		}
		
	}


	
	 public function getValue ( $value ){
	 	
	 	$result = xtc_db_query ( "	SELECT `configuration_value`
									FROM `configuration`
									WHERE `configuration_key` LIKE 'MODULE_IDEALO_CSV_" . $value . "';" );
		$result = xtc_db_fetch_array ( $result );
		
		return $result [ 'configuration_value' ];
		
	 }

 
	
	 public function getUrls(){
	 	
	 	$dir = dirname ( __FILE__ );

	 	$dir = substr ( $dir, 0, -6 );
	 		 	
	 	$url = fopen ( $dir . "link.ido", "r" ); 	
     	$urls =  fgets ( $url );
     	
     	$urls = explode ( '|', $urls );
     	$this->shop_url = $urls [0];
     	$this->image_url = $urls [1];
     	
   	 }

	
	
	public function checkCampaign(){
		
		$campaign_query = xtc_db_query ( "	SELECT `configuration_value` 
											FROM `" . TABLE_CONFIGURATION . "` 
											WHERE `configuration_key` = 'MODULE_IDEALO_CSV_CAMPAIGN' 
											LIMIT 1" );
		$campaign_db = xtc_db_fetch_array ( $campaign_query );
				
		if ( $campaign_db [ 'configuration_value' ] != '0' ){
			
			$this->campaignSet = true;
			
		}
		
	}
	
	
	
	public function getShippingcomment(){
		
		return $this->shippingcomment;
		
	}
	
	
	
	 public function shippingcomment(){
	 	
	 	$shipping_input_query = xtc_db_query ( "SELECT `configuration_value` 
	 											FROM `" . TABLE_CONFIGURATION . "` 
	 											WHERE `configuration_key` = 'MODULE_IDEALO_CSV_SHIPPINGCOMMENT' 
	 											LIMIT 1" );
		$shipping_comment_db = xtc_db_fetch_array ( $shipping_input_query );
				
		$this->shippingcomment = $shipping_comment_db [ 'configuration_value' ];
		
	}

	
	
	 public function openCSVFile( $schema ){
      $fp = fopen ( DIR_FS_DOCUMENT_ROOT . 'export/' . IDEALO_FILENAME, "w+" );
      fputs ( $fp, $schema );
      fclose ( $fp ); 		

	  if( isset ( $_POST [ 'export' ] ) && $_POST [ 'export' ] == 'yes' ) {
            $extension = substr ( IDEALO_FILENAME, -3 );
            $fp = fopen ( DIR_FS_DOCUMENT_ROOT . 'export/' . IDEALO_FILENAME, "rb" );
            $buffer = fread ( $fp, filesize ( DIR_FS_DOCUMENT_ROOT . 'export/' . IDEALO_FILENAME ) );
            fclose ( $fp );

            header ( 'Content-type: application/x-octet-stream' );
            header ( 'Content-disposition: attachment; filename=' . IDEALO_FILENAME );
            echo $buffer;
            
            exit;
                 
	  }
	 	
	 	
	 }
	
	
	
	 public function getArtikelID(){
	 	
	 	$artikel_array  = array();
	 		 	
	 	 $artikel = xtc_db_query("SELECT `products_id` FROM `products`;");
	 	 
	 	 while($products = xtc_db_fetch_array($artikel)){
	 	 	
	 	 	$artikel_array [] = $products [ 'products_id' ];
	 	 	
	 	 }
	 		 	 
	 	 return $artikel_array;
	 	 
	 }
	
	
	
	
	 public function exportArticle(){
	 	
	 	$artikel = $this->getArtikelID();    	
	    	
    	foreach ( $artikel as $art ){
    		
    		$options = $this->productOption($art);

			if($options !== false){
				
				$optionNumber = 0;
				
				$option = $this->getVariant($art);
				
				foreach ( $option as $op ){
					
					$optionNumber++;
					
					$exrtaPrice = '0';
					$extraWeight = '0';
					$extraName = '';
									
					foreach ( $op as $o ){
						
						$extraName .= $this->getAttributeOption($o['options_id']). ': ' . $this->getAttributeValue($o['options_values_id']) . '; ';
						$exrtaPrice = $this->getExtra( $exrtaPrice, $o['price_prefix'], $o['options_values_price']);
						$extraWeight = $this->getExtra( $extraWeight, $o['weight_prefix'], $o['options_values_weight']); 
						
					}

					$schema .= $this->getCSVValues ( '1', $art, IDEALO_CSV_SEPARATOR,  IDEALO_CSV_QUOTECHAR, substr ( $extraName, 0, -2 ), $exrtaPrice, $extraWeight, $optionNumber );
					
				}
				
			}else{
    			$schema .= $this->getCSVValues ( '0', $art, IDEALO_CSV_SEPARATOR,  IDEALO_CSV_QUOTECHAR );				
				
			}

    	}
	 	
	 	return $schema;
	 	
	 }	  
	 
	 public function getExtra ( $oldValue, $prefix, $value ){
	 	
	 	switch ($prefix) {
		    case '+':
		       	return $oldValue + $value;
		    case '-':
		       	return $oldValue - $value;
		    case '*':
		    	return $oldValue * $value;
		    case '/':
		    	return $oldValue / $value;  
		}
	 	
	 }
	  
	   
	  public function getAttributeOption($id){
	  	
	  	 $option = xtc_db_query("SELECT `products_options_name`
	  	 								FROM `products_options`
	  	 								WHERE `products_options_id` = '" . $id . "' AND `language_id` = '" . $_SESSION [ 'languages_id' ] . "';");
	 	 
		 $result = xtc_db_fetch_array($option);
		 
		 return $result['products_options_name'];
	  	
	  }
	  
	 
	  public function getAttributeValue($id){
	  	
	  	 $optionValue = xtc_db_query("SELECT `products_options_values_name`
	  	 								FROM `products_options_values`
	  	 								WHERE `products_options_values_id` = '" . $id . "' AND `language_id` = '" . $_SESSION [ 'languages_id' ] . "';");
	 	 
		 $result = xtc_db_fetch_array($optionValue);
		 
		 return $result['products_options_values_name'];
	  	
	  }
	  
	 
	
	public function createHeader(){
		
		$schema =  IDEALO_CSV_QUOTECHAR . ARTICLE_ID . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
					   IDEALO_CSV_QUOTECHAR . BRAND . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . PRODUCT_NAME . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . CATEGORIE . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . DESCRIPTION_SHORT . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . DESCRIPTION_SHORT_LONG . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . IMAGE . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . DEEPLINK . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . PRICE . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . NETTO_PRICE . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . EAN . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . DELIVERY . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . BASEPRICE . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR .
	        		   IDEALO_CSV_QUOTECHAR . WEIGHT . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR . 
	        		   IDEALO_CSV_QUOTECHAR . CSV_SHIPPINGCOMMENT . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR;

	        foreach ( $this->shipping as $shipping ){

	        	if ( $shipping [ 'active' ] == '1' ){

	        		foreach ( $this->payment as $payment ){

	        			if ( $payment [ 'active' ] == '1' && $this->paymentAllowed ( $shipping [ 'country' ], $payment [ 'country' ] ) === true ){

	        				$schema .=  IDEALO_CSV_QUOTECHAR . $payment [ 'title' ] . '_' . $shipping [ 'country' ] . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR;
	        				
	        			}
	        		
	        		}
	        		
	        	}
	        	
	        }
	        
		if ( $this->minOrderPrice != '' ){
      		
			$schema .= IDEALO_CSV_QUOTECHAR . str_replace(array("<b>","</b>"), "", IDEALO_CSV_MIN_EXTRA_COSTS) . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR;
      		
      	}
      	
      		$schema .=  IDEALO_CSV_QUOTECHAR . IDEALO_EXTRA_ATTRIBUTES . IDEALO_CSV_QUOTECHAR . IDEALO_CSV_SEPARATOR;
      		
      	
	        $schema .= "\n";
	        
	        setlocale ( LC_ALL, 'de_DE' ); 
			$date = date ( "d.m.y H:i:s" );   
			
			$schema .= 'Datei zuletzt erstellt am ' . $date . ' Uhr';
			$schema .= "\n";

	        $schema .= VERSION_TEXT_01 . VERSION_TEXT_02 . VERSION_TEXT_03 . VERSION_TEXT_04 . "\n";
	        
	        return $schema;
		
	}


	
	public function paymentAllowed ( $country, $payment_shipping ){

		$payment_coutry = 'DE';
		
		if ( $payment_shipping == '2' ){
			
			$payment_coutry = 'AT';
			
		}
		
		if ( $payment_shipping == '3' ){
			
			$payment_coutry = 'DE/AT';
			
		}
		
		if ( strpos ( $payment_coutry, $country ) !== false ){
		
			return true;
			
		}	
		
		return false;
		
	}


	
	private function isIn( $value, $array ){
		
		$array = explode ( ';', $array );
		
		foreach ( $array as $a ){
			
			if ( $a == $value ){
				 
				return true;
				
			}
			
		}
		
		return false;
		
	}


	
	private function filter( $id, $brand ){
		
		if ( IDEALO_CSV_BRAND_FILTER_VALUE != '' ){
			
			$isIn = $this->isIn ( $brand, IDEALO_CSV_BRAND_FILTER_VALUE );
			
			if ( IDEALO_CSV_BRAND_EXPORT == 'export' ){
				
				if ( $isIn === true ){
					
					return true;
					
				}else{
					
					return false;
					
				}
				
			}
					
			if ( IDEALO_CSV_BRAND_EXPORT == 'filter' ){
				
				if ( $isIn === true ){
					
					return false;
					
				}
				
			}
			
		}
		
		if ( IDEALO_CSV_ARTICLE_FILTER_VALUE != '' ){

			$isIn = $this->isIn ( $id, IDEALO_CSV_ARTICLE_FILTER_VALUE );
			if ( IDEALO_CSV_ARTICLE_EXPORT == 'export' ){

				if ( $isIn === true ){
					
					return true;
					
				}else{
					
					return false;
					
				}
				
			}
					
			if ( IDEALO_CSV_ARTICLE_EXPORT == 'filter' ){
				
				if ( $isIn === true ){
					
					return false;
					
				}
				
			}
			
		}
		
		return true;
		
	}


	
	 public function filterCat( $cat ){
	 	
	 	if ( IDEALO_CSV_CAT_FILTER_VALUE != '' ){
	 		
	 		$cat_filter = explode ( ';', IDEALO_CSV_CAT_FILTER_VALUE );
	 		
	 		foreach ( $cat_filter as $ca ){
	 			
	 			if ( strpos ( $cat, $ca ) !== false ){
	 				
	 				if ( IDEALO_CSV_CAT_EXPORT == 'export' ){
	 					
	 					return true;
	 						
	 				}else{
	 					
	 					return false;
	 					
	 				}
	 				
	 			}
	 			
	 		}	
	 					
		}
		
		if ( IDEALO_CSV_CAT_FILTER_VALUE != '' && IDEALO_CSV_CAT_EXPORT == 'export' ){
			
			return false;
				
		}
		
		return true;
			
	 }

	 
	
	public function getArticle ( $id ){
		
		 $export_query = xtc_db_query( " SELECT
				                             p.products_id,
				                             pd.products_name,
				                             pd.products_description,pd.products_short_description,
				                             p.products_model,p.products_ean,
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
				                             p.products_discount_allowed		
				                         FROM
				                             " . TABLE_PRODUCTS . " p LEFT JOIN
				                             " . TABLE_MANUFACTURERS . " m
				                           ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
				                             " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                           ON p.products_id = pd.products_id AND
				                            pd.language_id = '" . $_SESSION [ 'languages_id' ] . "' LEFT JOIN
				                             " . TABLE_SPECIALS . " s
				                           ON p.products_id = s.products_id
				                         WHERE
				                         	p.products_id = " . $id . "	  
				                         ORDER BY
				                            p.products_date_added DESC,
				                            pd.products_name" );
	                            
		return xtc_db_fetch_array ( $export_query );
	                            
	}

	
 
    public static function addQueryParams($url, $params) {

        $urlParts = parse_url($url);
        if(isset($urlParts['query']) === false || $urlParts['query'] == '') {
            $urlParts['query'] = http_build_query($params);
        }
        else {
            $urlParts['query'] .= '&'.http_build_query($params);
        }
        $newUrl = '';
        if(isset($urlParts['scheme']) === true) {
            $newUrl .= $urlParts['scheme'].'://';
        }

        if(isset($urlParts['user']) === true) {
            $newUrl .= $urlParts['user'];
            if(isset($urlParts['pass']) === true) {
                $newUrl .= ':'.$urlParts['pass'];
            }

            $newUrl .= '@';
        }

        if(isset($urlParts['host']) === true) {
            $newUrl .= $urlParts['host'];
        }

        if(isset($urlParts['port']) === true) {
            $newUrl .= ':'.$urlParts['port'];
        }

        if(isset($urlParts['path']) === true) {
            $newUrl .= $urlParts['path'];
        }

        if(isset($urlParts['query']) === true) {
            $newUrl .= '?'.$urlParts['query'];
        }

        if(isset($urlParts['fragment']) === true) {
            $newUrl .= '#'.$urlParts['fragment'];
        }

        return $newUrl;
    }


	 private function productOption($id){
	 	$attributes = xtc_db_query("SELECT `products_id`
								   			 FROM `products_attributes`
								   			 WHERE `products_id` = '". $id ."';");
		$att = xtc_db_fetch_array($attributes);
	
		if(!empty($att)){
			return true;
		}else{
			return false;	
		}
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


	
	 private function getVariant($id){
	 	$variant = array();
	 	$this->result = array();
	 	$count = count($this->attributes);
	 	
	 	$attributes = xtc_db_query("SELECT `options_id`, `options_values_id`, `options_values_price`, `price_prefix`, `attributes_model`, `attributes_stock`, `options_values_weight`, `weight_prefix`
								   			 FROM `products_attributes`
								   			 WHERE `products_id` = '". $id ."'
								   			 ORDER BY `options_id` ASC;");
		$result_array = array();
		$product_attributes = array();
		
		while($att = xtc_db_fetch_array($attributes)){
			$result_array [] = $att;
			$product_attributes [] = $att['options_id'];
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


	public function getCSVValues ( $variant, $id, $separator,  $quotarchar, $extraName = '', $exrtaPrice = 0, $extraWeight = 0, $optionNumer = '' ){
		
		$products = $this->getArticle ( $id );
		
		$schema = '';
		
		$products_price = $products [ 'products_price' ] + $exrtaPrice;
				
		$products_price = $this->getPrice ( $products [ 'products_tax_class_id' ], $products [ 'products_price' ], $id );	
		
		$tax = $this->getTax($products [ 'products_tax_class_id' ],$id);
		
		if ( ( float ) $exrtaPrice > 0 ){
						
			$exrtaPrice = $exrtaPrice * ( 1 + $tax / 100 );
			
			$products_price = $products_price + $exrtaPrice;
			
		}
		
		if ( ( float ) $products [ 'products_discount_allowed' ] > 0.00 ) {
			
			$products_price = $products_price * ( 1 - ( $products [ 'products_discount_allowed' ] / 100 ) );
			
		}
        $categorie_query = xtc_db_query("	SELECT
                                            categories_id
                                            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                            WHERE products_id = '" . $products [ 'products_id' ] . "'
                                            ORDER BY categories_id DESC;" );

         while ( $categorie_data = xtc_db_fetch_array ( $categorie_query ) ) {
         	
                $categories = $categorie_data [ 'categories_id' ];
                
         }
         
		$cat = $this->buildCAT ( $categories );
		
			
		if( $products [ 'products_status' ] == 1 && 
			$products_price > 0.00 && 
			$this->filter ( $id, $products[ 'manufacturers_name' ] ) === true && 
			$this->filterCat ( $cat ) === true){
			
			if ($optionNumer != '' ){
				
				$schema .= $quotarchar . $id . '-' .  $optionNumer . $quotarchar . $separator;
				
			}else{	
			
				$schema .= $quotarchar . $id . $quotarchar . $separator;

			}

      		$products_description = $this->cleanText ( $products [ 'products_description' ], 1000 );

            $products_short_description = $this->cleanText ( $products[ 'products_short_description' ] , 255 );

			

			if ( $products [ 'products_image' ] != '' ){
				
			   	$image = $this->getImages( $products [ 'products_id' ], $products [ 'products_image' ] );
			   	
			}else{
				
			    $image = '';
			    
			}
			
			$price = number_format ( $products_price, 2, '.', '' );

			$language = xtc_db_query("SELECT `code`
									  FROM `languages`
									  WHERE `languages_id` = " . $_SESSION [ 'languages_id' ] . ";" );
	 			
			$language = xtc_db_fetch_array ( $language );
			
			$url = $this->shop_url . DIR_WS_CATALOG . 'product_info.php?' . xtc_product_link ( $products [ 'products_id' ], $products [ 'products_name' ] );

			if ( $this->campaignSet === true ){
				
				$url = $this->addQueryParams ( $url, array ( 'refID' => IDEALO_CAMPAIGN ) ) ;
				
			}

			$netto = (float)$price / ( 1 + $tax / 100 );

			$schema .=	$quotarchar . $this->cleanText ( $products[ 'manufacturers_name' ], 100 ) . $quotarchar . $separator .
						$quotarchar . $this->cleanText ( $products [ 'products_name' ], 200 ) . ' ' . $extraName . $quotarchar . $separator .
						$quotarchar . $this->cleanText ( $cat, 100 ) . $quotarchar . $separator .
						$quotarchar . $products_short_description . $quotarchar . $separator .
						$quotarchar . $products_description . $quotarchar . $separator .
						$quotarchar . $image . $quotarchar . $separator .
						$quotarchar . $url . $quotarchar . $separator .
						$quotarchar . $price . $quotarchar . $separator .
						$quotarchar . number_format ( $netto, 2, '.', '' ) . $quotarchar . $separator;
			
			if ( $variant == '0' ){
				
				$schema .=	$quotarchar . $products [ 'products_ean' ] . $quotarchar . $separator;
				
			}else{
				
				$schema .=	$quotarchar . '' . $quotarchar . $separator;
				
			}	
			
			$schema .=	$quotarchar . $this->getShippingTime ( $products [ 'products_shippingtime' ], $_SESSION [ 'languages_id' ] ) . $quotarchar . $separator;
		
			if ( $products [ 'products_vpe_status' ] == '1'  && ( float ) $products [ 'products_vpe_value' ] > 0 ){
				
				$vpe = $this->getVPE ( $products [ 'products_vpe' ],  $_SESSION [ 'languages_id' ] );	

				$schema .=	$quotarchar . number_format ( $price / $products [ 'products_vpe_value' ], 2, '.', '' ) . ' EUR / ' . $vpe  . $quotarchar . $separator;
													
			}else{
				
				$schema .= $quotarchar . ''. $quotarchar . $separator;
				
			}
			
			
			$weight = 0;
			
			if ( empty ( $products [ 'products_weight' ] ) ){
				
				$schema .= $quotarchar . 'keine Angabe' . $quotarchar . $separator;
				
			}else{
				
				$weight = (float) $products [ 'products_weight' ] + (float) $extraWeight;
				
				$weight = str_replace ( ",", ".", $weight );
				
				$schema .= $quotarchar . $weight . $quotarchar . $separator;
				
			}
			
			$portocoment = $this->shippingcomment;
		      	
	      	if ( $this->checkMinOrder ( $price ) ){

	      		$portocoment = IDEALO_CSV_MIN_ORDER .  number_format( $this->minOrder, 2, '.', '' ) . ' EUR';

	      	}
	      	
	      	if ( $this->minOrderPrice != '' ){
     	
		     	if ( $this->checkMinExtraPrice ( $price ) ){
		     		
		     		$portocoment = number_format( $this->minOrderPrice, 2, '.', '' ) . 
								   IDEALO_CSV_MIN_ORDER_EXTRA_PRICE .
		     					   number_format( $this->minorderBorder, 2, '.', '' ) . 
		     					   IDEALO_CSV_SUM;
		     	
		     	}
	      		
      		}
			
			$schema .= $quotarchar . $portocoment . $quotarchar . $separator;
						
			foreach ( $this->shipping as $ship ){
				if ( $ship [ 'active' ] == '1' ){
					
					$costs = $this->getShippingCosts ( $price, (float)$weight, $ship );
					
				}
				
				foreach ( $this->payment as $payment ){
					
					if ( $payment [ 'active' ] == '1' && $this->paymentAllowed ( $ship [ 'country' ], $payment [ 'country' ] ) === true ){
						
						$schema .= $quotarchar . $this->getPaymentCosts ( $payment, $ship [ 'country' ], $price, $costs ) . $quotarchar . $separator;
														
					}
					
				}
				
			}
			
			if ( $this->minOrderPrice != '' ){
	     	
			     	if ( $this->checkMinExtraPrice ( $price ) ){
			     		
			     		$schema .= $quotarchar . number_format( $this->minOrderPrice, 2, '.', '' ) . $quotarchar . $separator;
			     		
			     	}else{
			     		
			     		$schema .= $quotarchar . '0.00' . $this->quoting . $quotarchar . $separator;
			     		
			     	}
			     	
			     }
			
			$schema .= $quotarchar . $extraName . $this->quoting . $quotarchar . $separator;
			
			$schema .= "\n";
		
		}else{
			
			$schema = '';
			
		}
		
	    return $schema;   
	        
    }
	
	 
	 
	 public function getImages( $id, $main_image ){
	 	
	 	$images = HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES . $main_image . ';';

	 	$images_query = xtc_db_query ( "SELECT `image_name` FROM `products_images` WHERE `products_id` = " . $id . ";" );
	 	
	 	while ( $image = xtc_db_fetch_array ( $images_query ) ) {
	 		
	 		$images .= HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES . $image [ 'image_name' ] . ';';
	 		
	 	}
		
		return substr( $images, 0, -1 );	
		 	
	 }
	 
	 
	 
	  
    public function getPaymentCosts ( $payment, $country, $price, $shipping ){
																	 
		$costs = $shipping;
		
		if ( $payment [ 'max' ] != '' ){
			
			if ( ( float ) $payment [ 'max' ] <= ( float ) $price ){
		
				return '';
			
			}
			
		}
		if ( $payment [ 'fix' ] != '' ){
			
			$costs = $costs + $payment [ 'fix' ];
			
		}
		
		if ( $payment [ 'percent' ] != '' ){
			
			if ( $payment [ 'shipping' ] == '1' ){
				
				$costs = $costs + ( ( $price + $costs ) * $payment [ 'percent' ] / 100 );
				
			}else{
				
				$costs = $costs + ( $price * $payment [ 'percent' ] / 100 );
				
			}
			
		}
				
		return number_format ( $costs, 2, '.', '' );
					
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
	 
	 public function getTax($tax,$id){
	 	
	 	$value = xtc_db_query ( " SELECT `tax_rate`
								  FROM `tax_rates`
								  WHERE `tax_class_id` = " . $tax . " 
								  		AND `tax_zone_id` = " . $this->geo_zone . ";" );
	 	
	 	$value = xtc_db_fetch_array ( $value );
	 	
	 	return $value [ 'tax_rate' ];
	 	
	 }
	 
	 
	  public function getPrice( $tax, $price, $id ){
	  	
	  	$value = $this->getTax($tax,$id);
	  	
	 	$special = xtc_db_query ( "	SELECT	`specials_new_products_price`
	                                FROM `specials`
	                                WHERE `products_id` = " . $id . " 
	                                	  AND `status` = 1;" );
                                            
        $special = xtc_db_fetch_array ( $special );
            
        if ( !empty ( $special ) ){
        	
           	$price = $special [ 'specials_new_products_price' ];
           	
        }else{
        	$special = xtc_db_query ( "	SELECT `personal_offer`
	                                FROM `personal_offers_by_customers_status_1`
	                                WHERE `products_id` = " . $id . ";" );
	                                	  
	        $special = xtc_db_fetch_array ( $special );
            
        	if ( !empty ( $special ) ){   
        		
        		if ( ( float ) $special [ 'personal_offer' ] > ( float ) 0.0000 ){
        			
        			$price = $special [ 'personal_offer' ];

        		}
        		
        	}    	  
        	
        }
	 	
	 	$price = $price * ( 1 + $value / 100 );
	 	
	 	return ( float ) $price; 
	 
	  }
	 
	 
	 
	  public function getShippingTime( $id, $la_id ){
	  	$value = xtc_db_query ( " SELECT `shipping_status_name`
								  FROM `shipping_status`
								  WHERE `shipping_status_id` = " . $id . " 
								  		AND `language_id` = " . $la_id . ";" );
	 	
	 	$value = xtc_db_fetch_array ( $value );
	 	
	 	return $value [ 'shipping_status_name' ];
	 	
	  }	
	
	
	
	 public function getVPE( $product_vpe, $language = '1' ){

	 	$vpe = xtc_db_query ( " SELECT `products_vpe_name` 
	 							FROM `products_vpe` 
	 							WHERE `products_vpe_id` = " . $product_vpe . " 
	 								  AND `language_id` = " . $language . ";" );	
	 	$vpe = xtc_db_fetch_array ( $vpe );
	 	
		return $vpe [ 'products_vpe_name' ];
	 	
	 }
	
	
   private function buildCAT ( $catID ) {
		if ( isset ( $this->CAT [ $catID ] ) ){
			
		 return  $this->CAT [ $catID ];
		 
		}else{
			
		   $cat = array();
		   
		   $tmpID=$catID;

		   while ( $this->getParent ( $catID ) != 0 || $catID != 0 ){
		   	
		        $cat_select = xtc_db_query ( " 	SELECT `categories_name` 
		        								FROM `".TABLE_CATEGORIES_DESCRIPTION."` 
		        								WHERE `categories_id` = '" . $catID . "' 
		        									  AND `language_id` = '" . $_SESSION [ 'languages_id' ] . "'" );
		  	    $cat_data = xtc_db_fetch_array ( $cat_select );
		  	    
		    	$catID = $this->getParent ( $catID );
		    	
		    	$cat[] = $this->cleanText ( $cat_data [ 'categories_name' ], 100 );
		    	
		   }

		   $catStr = '';
		   
		   for ( $i = count ( $cat ); $i > 0;$i-- ){
		   	
		      $catStr .= $cat [ $i - 1 ] . ' -> ';
		      
		   }
		   
		   $this->CAT [ $tmpID ] = substr ( $catStr, 0, -4 );

		  return $this->CAT [ $tmpID ];
		  
		}
		
    }
    
    
    
   private function getParent( $catID ) {
   	
      if ( isset ( $this->PARENT [ $catID ]  ) ) {
      	
       return $this->PARENT [ $catID ];
       
      } else {
      	
       $parent_query = xtc_db_query ( " SELECT `parent_id` 
       									FROM `" . TABLE_CATEGORIES . "` 
       									WHERE `categories_id` = '" . $catID . "'" );
       $parent_data = xtc_db_fetch_array ( $parent_query );
       
       $this->PARENT [ $catID ] = $parent_data [ 'parent_id' ];
       
       return  $parent_data [ 'parent_id' ];
       
      }
      
    }
    
    
}
