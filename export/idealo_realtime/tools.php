<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/






include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_shipping.php' );
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_payment.php' ); 
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_universal.php' ); 
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/db_connection.php' ); 
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_definition.php' );



define ( 'XML_BEGIN', '<?xml version="1.0" encoding="UTF-8"?><offers>' );
define ( 'XML_END', '</offers>' );
define ( 'IDEALO_CAMPAIGN', '?refID=94511215' );

class tools extends idealo_universal{
	
	public $shipping = array();
	
	public $payment = array();
	private $link= ' ';

	private $shippingcomment = '';

	private $campaignSet = false;
	
	private $shop_url = '';
	
	private $image_url ='';

	public function __construct(){

	}

	public function AllNeeded(){
		
		$this->getShipping();

		$this->getPayment();
		
		$this->getUrls();
		
		$this->shippingcomment();
				
		$this->checkCampaign();
		
		$this->getMinorderValues();
		
		
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

	
	public function getValueIdealoSetting( $value ){
		
		$value_query = xtc_db_query ( "SELECT `" . $value . "` FROM `idealo_realtime_setting`;" );
     	$value_db = xtc_db_fetch_array ( $value_query );
     	
     	return $value_db [ $value ];
     	
	}

	
	 public function getPayment(){
	 	
	 	$idealo_payment = new idealo_payment();
	 	$this->payment = $idealo_payment->payment;
	 	
	 	foreach ( $this->payment as $pay ){
			
			$payment = array();
			
			$active = 'idealo_' . $pay [ 'db' ] . '_active';
			$payment [ 'active' ] = $this->getValueIdealoSetting ( $active );
			
			$countries = 'idealo_' . $pay [ 'db' ] . '_countries';
			$payment [ 'country' ] = $this->getValueIdealoSetting ( $countries );
			
			$fix = 'idealo_' . $pay [ 'db' ] . '_fix';
			$payment [ 'fix' ] = $this->getValueIdealoSetting ( $fix );
			
			$percent = 'idealo_' . $pay [ 'db' ] . '_percent';
			$payment [ 'percent' ] = $this->getValueIdealoSetting ( $percent );
			
			$shipping = 'idealo_' . $pay [ 'db' ] . '_shipping';
			$payment [ 'shipping' ] = $this->getValueIdealoSetting ( $shipping );

			
			$payment [ 'title' ] = $pay [ 'title' ];
			$payment [ 'db' ] = $pay [ 'db' ];
			
			$this->payment [ $pay [ 'db' ] ] = $payment;	
			
		}
		
	 }

	
	public function getShipping(){
		
		$idealo_shipping = new idealo_shipping();
		$this->shipping = $idealo_shipping->shipping;
		
		foreach ( $this->shipping as $ship ){
			
			$shipping = array();
     		
     		$active = 'idealo_' . $ship [ 'country' ] . '_active';
			$shipping [ 'active' ] = $this->getValueIdealoSetting( $active );
			
			$costs = 'idealo_' . $ship [ 'country' ] . '_costs';
     		$shipping [ 'costs' ] = $this->getValueIdealoSetting( $costs );
     
     		$free = 'idealo_' . $ship [ 'country' ] . '_free';
     		$shipping [ 'free' ] = $this->getValueIdealoSetting( $free );
			
			$type = 'idealo_' . $ship [ 'country' ] . '_type';
     		$shipping [ 'type' ] = $this->getValueIdealoSetting( $type );
     		
			$shipping [ 'country' ] = $ship [ 'country' ];
			
			$this->shipping	[ $ship [ 'country' ] ] = $shipping;
			
		}
		
	}


	
	 public function getValue ( $value ){
	 	
	 	$result = xtc_db_query ( "	SELECT `configuration_value`
									FROM `configuration`
									WHERE `configuration_key` LIKE 'MODULE_IDEALO_REALTIME_" . $value . "';" );
		$result = xtc_db_fetch_array ( $result );
		
		return $result [ 'configuration_value' ];
		
	 }

 
	public function getLogin(){
		
		$result = array();
				
   		$result [ 'user' ] = $this->getValue ( 'FILE' );
		$result [ 'webservice' ] = $this->getValue ( 'URL' );
    	$result [ 'password' ] = $this->getValue ( 'PASSWORD' );
    	$result [ 'idealo_shop_id' ] = $this->getValue ( 'SHOP_ID' );
    	$result [ 'certificate' ] = $this->getValue ( 'CERTIFICATE' );
    	$result [ 'pagesize' ] = $this->getValue ( 'PAGESIZE' );

    	if ($result [ 'pagesize' ] == ''){

    		$result [ 'pagesize' ] = 50;
    		
    	}
    	
    	$result [ 'testmode' ] = $this->getValue ( 'TESTMODE' );
    	
    	if ( $result [ 'testmode' ] == 'yes' ){
    		
    		$result [ 'testmode' ] = '1';
    		
    	}else{
    		
    		$result [ 'testmode' ] = '0';
    		
    	}
    	
		$result [ 'status' ] = $this->getValue ( 'STATUS' );

		return $result;
		
	}


	
	public function newTimestamp(){

		$id = xtc_db_query ( "SELECT `id` FROM `" . IDEALO_REALTIME_CRON_TABLE . "` LIMIT 1" );
 		$id = xtc_db_fetch_array ( $id );
 			 			 		
		xtc_db_query ( "UPDATE `" . IDEALO_REALTIME_CRON_TABLE . "` SET `create_at` = current_timestamp, `to_execute` = ADDTIME(current_timestamp, '0:30:0') WHERE `id` = " . $id [ 'id' ] .";" );
					
	}

	
	
	 public function cleanTableIdealoRealtimeFailedRequest(){
	 	$db_connection = new Idealo_DB_Connection();
	 	
	 	if ( $db_connection->tableExists ( 'idealo_realtime_failed_request' ) ){
	 		
	 		xtc_db_query( "TRUNCATE `idealo_realtime_failed_request`" );
	 		
	 	}
	 	
	 }


	
	 public function cleanTableIdealoRealtimeUpdate(){
	 	$db_connection = new Idealo_DB_Connection();
	 	
	 	if ( $db_connection->tableExists ( 'idealo_realtime_update' ) ){
	 		
	 		xtc_db_query( "TRUNCATE `idealo_realtime_update`" );
	 		
	 	}
	 	
	 }


	
	 public function getUrls(){
	 	
	 	$dir = dirname ( __FILE__ );
	 	
	 	$dir = substr ( $dir, 0, -15 );
	 		 	
	 	$url = fopen ( $dir . "link.ido", "r" ); 	
     	$urls =  fgets ( $url );
     	
     	$urls = explode ( '|', $urls );
     	$this->shop_url = $urls [0];
     	$this->image_url = $urls [1];
     	
   	 }

	
	
	public function checkCampaign(){
		
		$campaign_query = xtc_db_query ( "	SELECT `configuration_value` 
											FROM `" . TABLE_CONFIGURATION . "` 
											WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_CAMPAIGN' 
											LIMIT 1" );
		$campaign_db = xtc_db_fetch_array ( $campaign_query );
				
		if ( $campaign_db [ 'configuration_value' ] != 'no' ){
			
			$this->campaignSet = true;
			
		}
		
	}
	
	
	
	public function getShippingcomment(){
		
		return $this->shippingcomment;
		
	}
	
	
	
	 public function shippingcomment(){
	 	
	 	$shipping_input_query = xtc_db_query ( "SELECT `configuration_value` 
	 											FROM `" . TABLE_CONFIGURATION . "` 
	 											WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT' 
	 											LIMIT 1" );
		$shipping_comment_db = xtc_db_fetch_array ( $shipping_input_query );
				
		$this->shippingcomment = $shipping_comment_db [ 'configuration_value' ];
		
	}

	
	
	public function getXMLBegin(){
		
		return XML_BEGIN;
		
	}


	
	 public function getXMLEnd(){
	 	
	 	return XML_END;
	 	
	 }
	 	 
	 
	
	public function getArticle ( $id ){
		
		$language = 'de';

		$language_id = xtc_db_query("SELECT `languages_id`
								  FROM `languages`
								  WHERE `code` LIKE '" . $language . "';" );

		$language_id = xtc_db_fetch_array ( $language_id );
		
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
				                             p.products_vpe
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
	                            
		return xtc_db_fetch_array ( $export_query );
	                            
	}


	
	 public function cleanTestFile(){
	 	
	 	$path = DIR_FS_CATALOG.IDEALO_REALTIME;
    	$path = substr ( $path, 0 , -16 );
    	
	 	$fp = fopen ( $path . 'idealo_realtime_test.csv', "w" );
        fputs ( $fp, 'empty' );
        fclose( $fp );
        
        $fp = fopen ( $path . 'idealo_realtime_test.html', "w" );
        fputs ( $fp, 'no errors' );
        fclose( $fp );
        		
	 }


	public function getXML ( $id ){

		if ( $id != '' ){
				
			$products = $this->getArticle ( $id );
	
			$xml .= '<offer>';
				
			$products_price = $this->getPrice ( $products [ 'products_tax_class_id' ], $products [ 'products_price' ], $id );
	        $categorie_query = xtc_db_query("	SELECT
	                                            categories_id
	                                            FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
	                                            WHERE products_id = '" . $products [ 'products_id' ] . "'
	                                            ORDER BY categories_id DESC;" );
	
	         while ( $categorie_data = xtc_db_fetch_array ( $categorie_query ) ) {
	         	
	                $categories = $categorie_data [ 'categories_id' ];
	                
	         }
	         
	         $cat = $this->buildCAT ( $categories, $id );
						
			if( $products [ 'products_status' ] == 1 && 
				$products_price > 0.00 &&
				$this->filter ( $id, $products[ 'manufacturers_name' ] ) === true && 
				$this->filterCat ( $cat ) === true 		
			){		
				
				$xml .= '<command>InsertOrReplace</command>'.
							'<sku><![CDATA[' . $id . ']]></sku>';
	
	      		$products_description = $this->cleanText ( $products [ 'products_description' ], 1000 );
	
	            $products_short_description = $this->cleanText ( $products[ 'products_short_description' ] , 255 );
	
				if ( $products [ 'products_image' ] != '' ){
					
				    $image =  $this->shop_url . $this->image_url . $products [ 'products_image' ];
				    
				}else{
					
				    $image = '';
				    
				}
										
				$price = number_format ( $products_price, 2, '.', '' );
	
				$language = 'de';
	
				$language_id = xtc_db_query("SELECT `languages_id`
										  FROM `languages`
										  WHERE `code` LIKE '" . $language . "';" );
	
				$language_id = xtc_db_fetch_array ( $language_id );
				
				$url = $this->shop_url . DIR_WS_CATALOG . 'product_info.php?' . xtc_product_link ( $products [ 'products_id' ], $products [ 'products_name' ] );
	
				if ( $this->campaignSet === true ){
					
					$url .= IDEALO_CAMPAIGN;
					
				}
	
	
				$xml .=	'<title><![CDATA[' . $this->cleanText ( $products [ 'products_name' ], 200 ) . ']]></title>' .
						'<url>' . $url . '</url>' .
						'<price>' . $price . '</price>';
					
					$xml .='<image>' . $image . '</image>';
						
				$xml .=	'<brand><![CDATA[' . $this->cleanText ( $products[ 'manufacturers_name' ], 100 ) . ']]></brand>' .
						'<description><![CDATA[' . $products_description . ']]></description>' .
						'<delivery><![CDATA[' . $this->getShippingTime ( $products [ 'products_shippingtime' ], $language_id [ 'languages_id' ] ) . ']]></delivery>' .
						'<category><![CDATA[' .  $cat . ']]></category>';
				if( $this->checkEan ( $products [ 'products_ean' ] ) ){
	
						$xml .= '<ean>' . $products [ 'products_ean' ] . '</ean>';
						
					}
	
				if ( $products [ 'products_vpe_status' ] == '1'  && ( float ) $products [ 'products_vpe_value' ] > 0 ){
	
					$vpe = $this->getVPE ( $products [ 'products_vpe' ], $language_id [ 'languages_id' ] );	
	
					$xml .=	'<basePrice context="DE" measure="' . $vpe [ 'measure' ] . '" unit="' . $vpe [ 'unit' ]. '">' . number_format ( $price / $products [ 'products_vpe_value' ], 2, '.', '' ) . '</basePrice>';
										
				}
				
				foreach ( $this->shipping as $ship ){
					if ( $ship [ 'active' ] == '1' ){
						
						$costs = $this->getShippingCosts ( $price, $products [ 'products_weight' ], $ship );

						if ( $this->minOrderPrice != '' ){

					     	if ( $this->checkMinExtraPrice ( $price ) ){

					     		$costs = $costs + $this->minOrderPrice;
					     		
					     	}
					     	
					     }
						
						
					}

					foreach ( $this->payment as $payment ){
						
						if ( $payment [ 'active' ] == '1' ){
							
							$xml .= $this->getPaymentCosts ( $payment, $ship [ 'country' ], $price, $costs );
															
						}
						
					}
					
				}

				$portocoment = $this->shippingcomment;
		      	
		      	if ( $this->checkMinOrder ( $price ) ){
	
		      		$portocoment = IDEALO_REALTIME_MIN_ORDER .  number_format( $this->minOrder, 2, '.', '' ) . ' EUR';
	
		      	}
		      	
		      	if ( $this->minOrderPrice != '' ){
	     	
			     	if ( $this->checkMinExtraPrice ( $price ) ){
			     		
			     		$portocoment = number_format( $this->minOrderPrice, 2, '.', '' ) . 
									   IDEALO_REALTIME_MIN_ORDER_EXTRA_PRICE .
			     					   number_format( $this->idealoMinorderBorder, 2, '.', '' ) . 
			     					   IDEALO_REALTIME_SUM;
			     	
			     	}
		      		
	      		}
				$xml .= '<shippingComment>' . $portocoment . '</shippingComment>';
			
			}elseif ($id!=''){
				
				$xml .=	'<command>DELETE</command>'.				
						'<sku>' . $id . '</sku>';
				
			}
			
			$xml .= '</offer>';
			
		    return $xml;
		      
		}else{
			
			return '';
			
		}
		        
    }
	 
	 
	  
    public function getPaymentCosts ( $payment, $country, $price, $shipping ){
		
		$payment_coutry = 'DE';
		
		if ( $payment [ 'country' ] == '2' ){
			
			$payment_coutry = 'DE';
			
		}
		
		if ( $payment [ 'country' ] == '3' ){
			
			$payment_coutry = 'DE/AT';
			
		}	
															 
		$costs = $shipping;
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
		
		$return = '';
		
		if ( strpos ( $payment_coutry, $country ) !== false ){
			
			$return = '<shipping context="' . $country . '" type="' . $payment [ 'db' ] . '">' . number_format ( ( float ) $costs, 2, '.', '' ) . '</shipping>';
			
		}
		
		return $return;
		
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
	 
	 
	  public function getPrice( $tax, $price, $id ){
	  	
	  	$value = xtc_db_query ( " SELECT `tax_rate`
								  FROM `tax_rates`
								  WHERE `tax_class_id` = " . $tax . " 
								  		AND `tax_zone_id` = 5;" );
	 	
	 	$value = xtc_db_fetch_array ( $value );
	 	
	 	$value = $value [ 'tax_rate' ];
	 	
	 	$special = xtc_db_query ( "	SELECT	`specials_new_products_price`
	                                FROM `specials`
	                                WHERE `products_id` = " . $id . " 
	                                	  AND `status` = 1;" );
                                            
        $special = xtc_db_fetch_array ( $special );
            
        if ( !empty ( $special ) ){
        	
           	$price = $special [ 'specials_new_products_price' ];
           	
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
	 	
	 	$vpe = explode ( ' ', $vpe [ 'products_vpe_name' ] );
	 	
	 	if ( count ( $vpe ) == 1 ){
	 		
	 		$vpe [ 'measure' ] = '1';	 		
	 		$vpe [ 'unit' ] = utf8_encode ( $vpe [ '0' ] );
	 		
	 	}else{
	 		
	 		$vpe [ 'measure' ] =  $vpe [ '0' ];	 		
	 		$vpe [ 'unit' ] = utf8_encode ( $vpe [ '1' ] ) ;
	 		
	 	}
	 	
		return $vpe;
	 	
	 }
	
	
   private function buildCAT ( $catID, $product_id ) {
		
		if ( isset ( $this->CAT [ $catID ] ) ){
			
		 return  $this->CAT [ $catID ];
		 
		}else{

			if ( $catID == '0' ){
				
				$new_cat = xtc_db_query(" SELECT MAX(`categories_id`)
										  FROM `products_to_categories`
										  WHERE `products_id` = '" . $product_id . "';" );
	
				$new_cat = xtc_db_fetch_array ( $new_cat );
				
				$catID = $new_cat [ 'MAX(`categories_id`)' ];

			}
			
			$language = 'de';		

			$language_id = xtc_db_query("SELECT `languages_id`
										  FROM `languages`
										  WHERE `code` LIKE '" . $language . "';" );
	
			$language_id = xtc_db_fetch_array ( $language_id );
				
			
		   $cat = array();
		   
		   $tmpID=$catID;

		   while ( $this->getParent ( $catID ) != 0 || $catID != 0 ){
		   	
		        $cat_select = xtc_db_query ( " 	SELECT `categories_name` 
		        								FROM `".TABLE_CATEGORIES_DESCRIPTION."` 
		        								WHERE `categories_id` = '" . $catID . "' 
		        									  AND `language_id` = '" . $language_id [ 'languages_id' ] . "'" );
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
    
     
    public function checkActive() {
     
    	$check_query = xtc_db_query ( " SELECT `configuration_value` 
    									FROM `" . TABLE_CONFIGURATION . "` 
    									WHERE `configuration_key` = 'MODULE_IDEALO_REALTIME_STATUS'" );
    	$check = xtc_db_fetch_array( $check_query );
    	    	
    	return $check [ 'configuration_value' ];
              	
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
    	
    	$email = xtc_db_fetch_array( $email_query );
    	
    	return $email [ 'customers_email_address' ];
    	     	
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

		if ( IDEALO_REALTIME_BRAND_FILTER_VALUE != '' ){
			
			$isIn = $this->isIn ( $brand, IDEALO_REALTIME_BRAND_FILTER_VALUE );
			
			if ( IDEALO_REALTIME_BRAND_EXPORT == 'export' ){
				
				if ( $isIn === true ){
					
					return true;
					
				}else{
					
					return false;
					
				}
				
			}
					
			if ( IDEALO_REALTIME_BRAND_EXPORT == 'filter' ){
				
				if ( $isIn === true ){
					
					return false;
					
				}
				
			}
			
		}
		
		if ( IDEALO_REALTIME_ARTICLE_FILTER_VALUE != '' ){

			$isIn = $this->isIn ( $id, IDEALO_REALTIME_ARTICLE_FILTER_VALUE );
			if ( IDEALO_REALTIME_ARTICLE_EXPORT == 'export' ){

				if ( $isIn === true ){
					
					return true;
					
				}else{
					
					return false;
					
				}
				
			}
					
			if ( IDEALO_REALTIME_ARTICLE_EXPORT == 'filter' ){
				
				if ( $isIn === true ){
					
					return false;
					
				}
				
			}
			
		}
		
		return true;
		
	}


	
	 public function filterCat( $cat ){
	 	
	 	if ( IDEALO_REALTIME_CAT_FILTER_VALUE != '' ){
	 		
	 		$cat_filter = explode ( ';', IDEALO_REALTIME_CAT_FILTER_VALUE );
	 		
	 		foreach ( $cat_filter as $ca ){
	 			
	 			if ( strpos ( $cat, $ca ) !== false ){
	 				
	 				if ( IDEALO_REALTIME_CAT_EXPORT == 'export' ){
	 					
	 					return true;
	 						
	 				}else{
	 					
	 					return false;
	 					
	 				}
	 				
	 				if ( IDEALO_REALTIME_CAT_EXPORT == 'filter' ){
	 					
	 					return false;
	 						
	 				}
	 				
	 			}
	 			
	 		}	
	 					
		}
		
		if ( IDEALO_REALTIME_CAT_FILTER_VALUE != '' && IDEALO_REALTIME_CAT_EXPORT == 'export' ){
			
			return false;
				
		}
		
		return true;
			
	 }
     
     
    
}
