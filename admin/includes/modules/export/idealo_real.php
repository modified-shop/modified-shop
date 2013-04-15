<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/







defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

require_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_set_and_get_setting.php' );
require_once ( DIR_FS_CATALOG . 'export/idealo_realtime/tools.php' );
require_once ( DIR_FS_CATALOG . 'export/idealo_realtime/communication.php' );
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_shipping.php' );
include_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_payment.php' ); 
require_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_definition.php' );
require_once ( DIR_FS_CATALOG . 'export/idealo_realtime/idealo_definition_universal.php' );



class idealo_real{
    public $code;
    public $title;
    public $description;
    public $enabled;
	
	public $payment = array();
	
    public $shipping = array();	

    public function __construct() {
    
      $this->saveUrl();
                      
      $this->code = 'idealo_real';
		
	  if ( TEXT_IDEALO_REALTIME_MODIFIED == 'no' ){
      	      $this->title = MODULE_IDEALO_REALTIME_TEXT_TITLE . ' v. '. VERSION_TEXT_02 . ' - ' . NEW_IDEALO_REALTIME_VERSION_TEXT;
      }else{
      	      $this->title = MODULE_IDEALO_REALTIME_TEXT_TITLE . ' v. '. VERSION_TEXT_02 . '.mod - ' . NEW_IDEALO_REALTIME_VERSION_TEXT;
      }

      $this->description = MODULE_IDEALO_REALTIME_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_IDEALO_REALTIME_SORT_ORDER;
      $this->enabled = ((MODULE_IDEALO_REALTIME_STATUS == 'True') ? true : false);
      $this->CAT=array();
      $this->PARENT=array();
      $this->productsPrice = 0;
      $this->description = '<center><a href="http://www.idealo.de" target="_blank"><img src = "http://www.idealo.de/pics/common/logoidealo_blue_l.gif"></a></center>';
      $this->country_array = array();
    }

	public function saveURL(){
		 $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/link.ido', "w+");
	          fputs($fp, HTTP_CATALOG_SERVER . '|'.  DIR_WS_CATALOG_ORIGINAL_IMAGES);
	          fclose($fp); 
	}

	
	
	public function deleteProduct( $list_idealo ){

		$delete_list = array();

		if ( $this->login [ 'status' ] == 'False' ){
			
			$delete_list = $list_idealo;
			
		}else{
			
			foreach( $list_idealo as $idealo ){

					if ( is_numeric ( $idealo ) ){
						
						$result = xtc_db_query("	SELECT `products_id`
												FROM `products`
												WHERE `products_id` = '"  . $idealo . "' AND `products_status` = 1;");
		
						$result = xtc_db_fetch_array( $result );			 

						if ( empty ( $result ) ){
							
							$delete_list[] = $idealo;
							
						}	
						
						
					}else{

						$delete_list[] = $idealo;
						
					}
			 		
				
			}
			
		}		
		
		if( count ( $delete_list ) > 0 ){
			
			$communication = new Communication( $this->login );
			$communication->deleteProduct( $delete_list );
			
		}

	}
	
	
	
	
	 public function cleanIdealo(){

		$communication = new Communication( $this->login );
			    	    
		$page = 1;
		
		do{
			$article = $communication->getOfferList ( $page );
			if ( count( $article ) > 0 ){
				
				$list = array();
				
				foreach ( $article as $art ){

					$list [] = (string) $art->sku[0];
				
					}
				$this->deleteProduct( $list );
				
				if ( $this->login [ 'status' ] != 'False' ){
				
					$page++;
					
				}
			
			}
			
		}while ( count ( $article ) > 0 );
		
	 }
	
	
	
	public function createRealtime(){
	
		$checkLOF = xtc_db_query("	SELECT table_name
									FROM information_schema.tables
									WHERE `table_name` = 'idealo_realtime_update' AND `table_schema` LIKE '" . DB_DATABASE . "';");

		$result = xtc_db_fetch_array($checkLOF);
		
		if(empty($result)){
			xtc_db_query("	
							CREATE TABLE `idealo_realtime_update`(
							id INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
							products_id int(11),
							event varchar (20))
						");
			$sql = "DROP TRIGGER IF EXISTS `idealo_update`;";
			xtc_db_query($sql);
			
			$sql = "CREATE TRIGGER `idealo_update` AFTER UPDATE ON `products` FOR EACH ROW INSERT INTO `idealo_realtime_update` (`products_id`, `event`) VALUES (OLD.products_id, 'UPDATE');";
			xtc_db_query($sql);
			$sql = "DROP TRIGGER IF EXISTS `idealo_insert`;";
			xtc_db_query($sql);
			
			$sql = "CREATE TRIGGER `idealo_insert` AFTER INSERT ON `products` FOR EACH ROW INSERT INTO `idealo_realtime_update` (`products_id`, `event`) VALUES (new.products_id, 'INSERT');";
			xtc_db_query($sql);
			$sql = "DROP TRIGGER IF EXISTS `idealo_delete`;";
			xtc_db_query($sql);
			
			$sql = "CREATE TRIGGER `idealo_delete` AFTER DELETE ON `products` FOR EACH ROW INSERT INTO `idealo_realtime_update` (`products_id`, `event`) VALUES (OLD.products_id, 'DELETE');";
			xtc_db_query($sql);
			$tools = new tools();
			
			$sql = "CREATE TABLE `idealo_realtime_setting` (`idealo` varchar(10)";
			$sql2 = "INSERT INTO `idealo_realtime_setting` VALUES(''";
			
			$idealo_shipping = new idealo_shipping();
	 		$this->shipping = $idealo_shipping->shipping;
			
			foreach($this->shipping as $ship){
				$active = 'idealo_' . $ship['country'] . '_active';
				$sql .= ", `" . $active . "` varchar(10)";
				$sql2 .= ", '0'";
				
				$costs = 'idealo_' . $ship['country'] . '_costs';
				$sql .= ", `" . $costs . "` varchar(100)";
				$sql2 .= ", ''";
				
				$free = 'idealo_' . $ship['country'] . '_free';
				$sql .= ", `" . $free . "` varchar(20)";
				$sql2 .= ", ''";
				
				$type = 'idealo_' . $ship['country'] . '_type';
				$sql .= ", `" . $type . "` varchar(10)";
				$sql2 .= ", ''";
			}

			$idealo_payment = new idealo_payment();
	 		$this->payment = $idealo_payment->payment;

			foreach($this->payment as $pay){			
				$active = 'idealo_' . $pay['db'] . '_active';
				$sql .= ", `" . $active . "` varchar(10)";
				$sql2 .= ", '0'";
				
				$countries = 'idealo_' . $pay['db'] . '_countries';
				$sql .= ", `" . $countries . "` varchar(10)";
				$sql2 .= ", ''";
						
				$fix = 'idealo_' . $pay['db'] . '_fix';
				$sql .= ", `" . $fix . "` varchar(10)";
				$sql2 .= ", ''";
							
				$percent = 'idealo_' . $pay['db'] . '_percent';
				$sql .= ", `" . $percent . "` varchar(10)";
				$sql2 .= ", ''";
							
				$shipping = 'idealo_' . $pay['db'] . '_shipping';
				$sql .= ", `" . $shipping . "` varchar(10)";			
				$sql2 .= ", '0'";
			}
			
			$sql .= ", `idealoMinorderprice` varchar(10), `idealoMinorder` varchar(10), `idealoMinorderBorder` varchar(10));";
			$sql2 .= ", '', '', '');";
			
			xtc_db_query($sql);			
			xtc_db_query($sql2);			
			
		}		
		
		xtc_db_query ( "CREATE TABLE `" . IDEALO_REALTIME_CRON_TABLE . "` ( `id` INT NOT NULL AUTO_INCREMENT, `create_at` timestamp, `to_execute` timestamp, PRIMARY KEY(id));" );
		xtc_db_query ( "INSERT INTO `" . IDEALO_REALTIME_CRON_TABLE . "` (`create_at`, `to_execute`) VALUES (current_timestamp, ADDTIME(current_timestamp, '0:30:0'));" );
		
		$communication = new Communication();
		$communication->createErrorTable();
		
	}
    
    
     private function deleteRealtime(){
     		xtc_db_query("DROP TABLE `idealo_realtime_update`");
     		xtc_db_query("DROP TABLE `idealo_realtime_setting`");
     		xtc_db_query("DROP TABLE `" . IDEALO_REQUEST_ERROR_TABLE . "`");
     		xtc_db_query("DROP TABLE `" . IDEALO_REALTIME_CRON_TABLE . "`");
			xtc_db_query("DROP TRIGGER IF EXISTS `idealo_update`;");
			xtc_db_query("DROP TRIGGER IF EXISTS `idealo_insert`;");
			xtc_db_query("DROP TRIGGER IF EXISTS `idealo_delete`;");
     }
   
   
   
    private function setValueIdealoSetting($row, $value){
    	$sql = "UPDATE `idealo_realtime_setting` SET `" . $row . "` = '" . $value . "';";
		xtc_db_query($sql);
    }
   
   
    private function errorSettingMessage ( $not_set ){

    	$missing_config = '';
	 	
	 	foreach ( $not_set as $value ){
	 		
	 		$missing_config .= $value . ';';
	 		
	 	}
	 	
	 	$missing_config = substr ( $missing_config, 0, -1 );
    	xtc_db_query("update " . TABLE_CONFIGURATION . "
				      set configuration_value = '" . $missing_config . "'
				      where configuration_key = 'MODULE_IDEALO_REALTIME_MISSING_CONFIG'");

    	echo '	<body bgcolor="#99CCFF">
					<b>
					<center>
					<font face="Arial,MS Sans Serif">
		 			
		 			<form action="javascript:history.back()">
		 				<br><br>
		 				<div id="logo">
							<a href="www.idealo.de" target="_blank"><img src="http://www.idealo.de/pics/common/logoidealo_blue_l.gif" alt="Price Comparison" class="logo noborder"/></a>
						</div>
									
						<br><br>
						Die &Uuml;bertragung konnte nicht an Idealo erfolgen.<br><br>
								
						Bitte erg&auml;nzen Sie die Pflichtfelder in den Moduleinstellungen!<br><br>
	    				<input id="export" type="submit" name="failed" value="Zur&uuml;ck zum Modul" />
	    				 		
	    			</form>
	    			</font>
					</center>
					</b>
				</body>';
    }
   
   	
   	private function checkSettingSet (){

   		$not_set = array();
   		
   		if ( $this->login [ 'testmode' ] != '1' ){
	   		
	   		$user = $_POST [ 'configuration' ];
	   		
	   		$user = $user [ 'MODULE_IDEALO_REALTIME_FILE' ];
	
	   		if ( $user == '' ){
	   			
	   			$not_set [] = 'user';
	   			   			
	   		}
	
	   		if ( $_POST [ 'url_input' ] == '' ){
	   			
	   			$not_set [] = 'url_input';
	   			   			
	   		}
	   		
	   		if ( $_POST [ 'shop_id_input' ] == '' ){
	   			
	   			$not_set [] = 'shop_id_input';
	   			
	   		}
	   		
	   		if ( $_POST [ 'password_input' ] == '' ){
	   			
	   			$not_set [] = 'password_input';
	   			
	   		}
	   		
	   		if ( $_POST [ 'pagesize_input' ] == '' ){
	   			
	   			$not_set [] = 'pagesize_input';
	   			
	   		}
   		
   		}
   		$idealo_shipping = new idealo_shipping();
	 	$this->shipping = $idealo_shipping->shipping;
		
		$shipping_active = false;
				
	 	foreach( $this->shipping as $ship ){

	 		$active = 'idealo_' . $ship['country'] . '_active';
	 		$value = $_POST['shipping_' . $ship['country'] . '_active'];			
				
				if ( $value == '1' ){	
				
					$shipping_active = true;
					
					$costs = 'idealo_' . $ship['country'] . '_costs';
					$value = $_POST['shipping_' . $ship['country'] . '_costs'];
					
					if ( $value == '' ){
						
						$not_set [] = $active;
						
					} 
				
				}
						
			
	 	}

   		if ( !$shipping_active ){
   			
   			$not_set [] = 'shipping';
   			
   		}
   		$idealo_payment = new idealo_payment();
	 	$this->payment = $idealo_payment->payment;
	 	
	 	$payment_active = false;

	 	foreach( $this->payment as $pay ){
	 		
	 		$active = 'idealo_' . $pay['db'] . '_active';
			$value = $_POST['payment_' . $pay['db'] . '_active'];
			
			if ( $value == '1' ){
	
				$payment_active = true;
				
				break;
				
			}
			
		}

	 	if ( !$payment_active ){

   			$not_set [] = 'payment';
   			
   		}
   		
   		if ( count ( $not_set ) > 0 ){
   			
   			$this->errorSettingMessage ( $not_set );
   			
   			return false;
   				
   		}else{
	   		return true;
   			
   		}
   		
   	}
   
	     
	 public function saveSetting(){
	 	xtc_db_query("update " . TABLE_CONFIGURATION . "
				      set configuration_value = ''
				      where configuration_key = 'MODULE_IDEALO_REALTIME_MISSING_CONFIG'");
	 	
	 	$check = $this->checkSettingSet();

		$idealo_shipping = new idealo_shipping();
	 	$this->shipping = $idealo_shipping->shipping;
	 	foreach( $this->shipping as $ship ){
	
	 		$active = 'idealo_' . $ship['country'] . '_active';
	 		$value = $_POST['shipping_' . $ship['country'] . '_active'];
			$shipping['active'] = $this->setValueIdealoSetting($active, $value);
			
			$costs = 'idealo_' . $ship['country'] . '_costs';
			$value = $_POST['shipping_' . $ship['country'] . '_costs'];
     		$this->setValueIdealoSetting($costs, $value);
     
     		$free = 'idealo_' . $ship['country'] . '_free';
     		$value = $_POST['shipping_' . $ship['country'] . '_free'];
     		$this->setValueIdealoSetting($free, $value);
			
			$type = 'idealo_' . $ship['country'] . '_type';
			$value = $_POST['shipping_' . $ship['country'] . '_type'];
			
     		$this->setValueIdealoSetting ( $type, $value );
     		
	 	}
	 	
	 	$idealo_payment = new idealo_payment();
	 	$this->payment = $idealo_payment->payment;
	 	
	 	foreach( $this->payment as $pay ){
	 		
	 		$active = 'idealo_' . $pay['db'] . '_active';
			$value = $_POST['payment_' . $pay['db'] . '_active'];
			$this->setValueIdealoSetting($active, $value);
			
			$countries = 'idealo_' . $pay['db'] . '_countries';
			$value = $_POST['payment_' . $pay['db'] . '_country'];
			$this->setValueIdealoSetting($countries, $value);
					
			$fix = 'idealo_' . $pay['db'] . '_fix';
			$value = $_POST['payment_' . $pay['db'] . '_extrafee_fix'];
			$this->setValueIdealoSetting($fix, $value);
						
			$percent = 'idealo_' . $pay['db'] . '_percent';
			$value = $_POST['payment_' . $pay['db'] . '_extrafee_nofix'];
			$this->setValueIdealoSetting($percent, $value);
				
			$value = '1';		
			$shipping = 'idealo_' . $pay['db'] . '_shipping';
			
			if ( $_POST['payment_' . $pay['db'] . '_nofix_inkl_sc'] == 'no' ){
				
				$value = '0';
				
			}
			
			$this->setValueIdealoSetting ( $shipping, $value );
			
		 }
		 
    	xtc_db_query("UPDATE `idealo_realtime_setting` SET `idealoMinorder` = '" . $_POST['idealo_realtime_minOrder_input'] . "';");	
    	xtc_db_query("UPDATE `idealo_realtime_setting` SET `idealoMinorderprice` = '" . $_POST['idealo_realtime_minOrderPrice_input'] . "';");	
		xtc_db_query("UPDATE `idealo_realtime_setting` SET `idealoMinorderBorder` = '" . $_POST['idealo_realtime_minOrderBorder_input'] . "';");	
				      	 		 	
		 		 	
	 	if ( $check ){
	 		
	 		return true;
	 		
	 	}
	 	
	 	return false;
	 	
	 }

	
	public function sendMail(){
		$tools = new tools();
		
		$eMail = $tools->getEmail();
		
		$baseUrl = HTTP_SERVER . DIR_WS_CATALOG;
		
		$dateihandle = fopen( HTTP_SERVER . DIR_WS_CATALOG . 'export/idealo_realtime_test.html', "r" );
		$zeile = fgets( $dateihandle, 4096 );
		
		$error = 'keine';
						
		if( $zeile != 'no errors' ){	
		
			$error = $baseUrl . 'export/idealo_realtime_test.html';
		
		}
		
		$tools->sendMail ( $eMail, 
						   $baseUrl . 'export/idealo_realtime_test.csv', 
						   $error, 
						   $baseUrl, 
						   MODULE_VERSION_TEXT, 
						   $baseUrl . 'export/log_' . date( "n_Y" ) . '.log', 
						   $baseUrl . 'export/last_answer.xml',
						   $baseUrl . 'export/last_request.xml' );
						   
		 $html = '	<body bgcolor="#99CCFF">
					<b>
					<center>
					<font face="Arial,MS Sans Serif">
		 			
		 			<form name="close" onSubmit= "window.close();>
		 				<br><br>
		 				<div id="logo">
							<a href="www.idealo.de" target="_blank"><img src="http://www.idealo.de/pics/common/logoidealo_blue_l.gif" alt="Price Comparison" class="logo noborder"/></a>
						</div><br><br>';
						
			$html .= IDEALO_QUESTION_AFTER_EMAIL_SEND_01 . $baseUrl . IDEALO_QUESTION_AFTER_EMAIL_SEND_02 . '<br><br>';
								
			$html .= '<input id="close" type="submit" name="close" value="close" />
	 		
	    			</form><br><br>
	    			
	    			</font>
					</center>
					</b>
				</body>';
			
			echo $html;
		die();
		
	}


	
    public function process ( $file ) {

		if ( isset( $_POST [ 'sendIdealoMail' ] ) ){
			
			$this->sendMail();
			
		}
		
		$tools = new tools();
	     $this->login = $tools->getLogin();

		if ( $this->saveSetting() ){

	    	@xtc_set_time_limit(0);
	    	$tools->cleanTableIdealoRealtimeUpdate();
	    	$tools->cleanTableIdealoRealtimeFailedRequest();
	    	
	    	$tools->cleanTestFile();
	    	$tools->AllNeeded();
	      	$this->login = $tools->getLogin();
	    	
	    	$xml = '';
	    	    	
	    	try{

	    		if ( $this->login [ 'testmode' ] != '1' ){
	    			
	    			$this->cleanIdealo();		
	    			
	    		}

	    	} catch ( Exception $e ){}

	    	if ( $this->login [ 'status' ] == 'True' ){
	    		
	    		$tools->newTimestamp();
	    	
		    	$communication = new Communication( $this->login );
		    	
		    	$artikel_start = 0;
		       	$article_count = xtc_db_query ( "SELECT count(*) FROM `products`;" );
		       	
		       	$article_count = xtc_db_fetch_array ( $article_count );
		       	
		     	$article_count = $article_count [ 'count(*)' ];
		       	       	
		       	$repetition = 0;	

		       	if ( $article_count > 0 ){
		       		
					if ( $article_count <= $this->login [ 'pagesize' ] ){
						
						$repetition = 1;
						
					}else{
						
						$repetition = ceil ( $article_count / $this->login [ 'pagesize' ] );
						
					}
					
				}
		       	
		       	for ( $i = 1; $i <= $repetition; $i++ ){
		
		    		$artikel = $this->getArtikelID($artikel_start, $this->login['pagesize']);
			    	$xml = $tools->getXMLBegin();
			    	
			    	if ( $this->login [ 'testmode' ] != '0' ){
			    		
			    		$xml .= '<testMode>true</testMode>';
			    		
			    	}

			    	foreach ( $artikel as $art ){
			    		$xml .= $tools->getXML ( $art );
			    		
			    	}
			    	$xml .= $tools->getXMLEnd();
			    	@$communication->sendRequest ( $xml );
			    	
			    	$artikel_start = $artikel_start + $this->login[ 'pagesize' ];

		    	}
		   
		    	if( $this->login [ 'testmode' ] == '1' ){
		    		
		    		$this->backToBackend ( substr ( $_SERVER [ 'PHP_SELF' ], 0, -24 ) );
		    		
		    		die();
		    		
		    	}
		    	
			}
	    	
		}else{
			die();
			
		}		

	}


	
	 public function backToBackend ( $url ){
	 	
	 $html = '	<body bgcolor="#99CCFF">
					<b>
					<center>
					<font face="Arial,MS Sans Serif">
		 			
		 			<form name="exportReady" action="" method="post">
		 				<br><br>
		 				<div id="logo">
							<a href="www.idealo.de" target="_blank"><img src="http://www.idealo.de/pics/common/logoidealo_blue_l.gif" alt="Price Comparison" class="logo noborder"/></a>
						</div><br><br>';
		$html .= IDEALO_TESTMODE_ACTIVE;
									
		$html .= '<br><br>';
					
		$html .= IDEALO_REALTIME_TEST_DONE . '<br><br>';
		$html .= IDEALO_REALTIME_TEST_OPEN_TESTFILE . '<br><br>';
		
		$html .=		'<a href="' . $url . '/export/idealo_realtime_test.csv"><img src="' . $url . '/export/idealo_realtime/idealo_csv_file.gif"></a><br><br>';
		
		$dateihandle = fopen( HTTP_SERVER . DIR_WS_CATALOG . 'export/idealo_realtime_test.html', "r" );
		$zeile = fgets( $dateihandle, 4096 );						
	
		if( $zeile != 'no errors' ){		
						
			$html .='<br><br><br>' .
					IDEALO_REALTIME_TEST_MISSED . 
					'<br>
					<a href="' . HTTP_SERVER . DIR_WS_CATALOG . 'export/idealo_realtime_test.html" target="_blank">FEHLER</a>
					<br><br><br>';
						
			}
		
		$html .=	IDEALO_REALTIME_TEST_OK . '<br><br>';						 					
	    $html .=	'<input id="sendMail" type="submit" name="sendMail" value="senden" />
	    			 <input type="hidden" name="sendIdealoMail" value="sendIdealoMail">
	 		
	    			</form><br><br>
	    			<form name="back" action="javascript:history.back()">
	    				<input id="back" type="submit" name="back" value="zur&uuml;ck zum Backend" />
	    			</form>		
	    			</font>
					</center>
					</b>
				</body>';
			
			echo $html;
				 
	 }

	
	 public function getArtikelID( $begin, $count ){
	 	
	 	$artikel_array  = array();
	 		 	
	 	 $artikel = xtc_db_query("SELECT `products_id` FROM `products` LIMIT " . $begin . " , " . $count . ";");
	 	 
	 	 while($products = xtc_db_fetch_array($artikel)){
	 	 	
	 	 	$artikel_array [] = $products [ 'products_id' ];
	 	 	
	 	 }
	 		 	 
	 	 return $artikel_array;
	 	 
	 }
	 

	
	
    public function display() {
    	
		$tools = new tools();
		$missing_config_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_MISSING_CONFIG' LIMIT 1");
		$missing_config_db = xtc_db_fetch_array($missing_config_query);
		$missing_config_db = $missing_config_db['configuration_value'];
		$missing_config_user = '';
		$missing_config_url_input = '';
		$missing_config_shop_id_input = '';
		$missing_config_password_input = '';
		$missing_config_pagesize_input = '';
		$missing_config_shipping = '';
		$missing_config_payment = '';
		
		$missing_config_costs_idealo_DE_active = '';
		$missing_config_costs_idealo_DE_active = '';
		if ( $missing_config_db != '' ){
			
			if ( strpos ( $missing_config_db, "user" ) !== false ){
				
				$missing_config_user = IDEALO_TEXT_MISSING_CONFIG;
				
			}
			
			if ( strpos ( $missing_config_db, "url_input" ) !== false ){
				
				$missing_config_url_input = IDEALO_TEXT_MISSING_CONFIG;
				
			}
			
			if ( strpos ( $missing_config_db, "shop_id_input" ) !== false ){
				
				$missing_config_shop_id_input = IDEALO_TEXT_MISSING_CONFIG;
				
			}
			
			if ( strpos ( $missing_config_db, "password_input" ) !== false ){
				
				$missing_config_password_input = IDEALO_TEXT_MISSING_CONFIG;
				
			}
			
			if ( strpos ( $missing_config_db, "pagesize_input" ) !== false ){
				
				$missing_config_page_size_input = IDEALO_TEXT_MISSING_CONFIG;
				
			}
			
			if ( strpos ( $missing_config_db, "shipping" ) !== false ){
				
				$missing_config_shipping = IDEALO_TEXT_MISSING_SHIPPING;
				
			}
			
			if ( strpos ( $missing_config_db, "payment" ) !== false ){
				
				$missing_config_payment = IDEALO_TEXT_MISSING_PAYMENT;
				
			}
			
			if ( strpos ( $missing_config_db, "idealo_DE_active" ) !== false ){
				
				$missing_config_costs_idealo_DE_active = IDEALO_TEXT_MISSING_COSTS_IDEALO_DE;
				
			}
			
			if ( strpos ( $missing_config_db, "idealo_AT_active" ) !== false ){
				
				$missing_config_costs_idealo_AT_active = IDEALO_TEXT_MISSING_COSTS_IDEALO_DE;
				
			}
			
		}
		
		$button_text = 'Aktualisieren';
		$realtime_link = $path = HTTP_CATALOG_SERVER.DIR_WS_CATALOG.IDEALO_REALTIME_LINK;
		$shipping_input_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_SHIPPINGCOMMENT' LIMIT 1");
		$shipping_comment_db = xtc_db_fetch_array($shipping_input_query);
		$shipping_comment_text = ( $shipping_comment_db !== false ) ? $shipping_comment_db['configuration_value'] : '';
		$url_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_URL' LIMIT 1");
		$url_db = xtc_db_fetch_array($url_query);
		
		$url = $url_db['configuration_value'];
		$shop_id_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_SHOP_ID' LIMIT 1");
		$shop_id_db = xtc_db_fetch_array($shop_id_query);

		$shop_id = $shop_id_db['configuration_value'];
		$password_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_PASSWORD' LIMIT 1");
		$password_db = xtc_db_fetch_array($password_query);

		$password = $password_db['configuration_value'];
		$pagesize_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_PAGESIZE' LIMIT 1");
		$pagesize_db = xtc_db_fetch_array($pagesize_query);

		$pagesize = $pagesize_db['configuration_value'];

	    $customers_statuses_array = xtc_get_customers_statuses();	    
	    
	    $campaign_array[] = array ('id' => '0', 'text' => 'no');
		$campaign_array[] = array ('id' => 'refID=' . CAMPAIGN . '&', 'text' => '94511215 (idealo)');
		$campaign_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_CAMPAIGN' LIMIT 1");
		$campaign_db = xtc_db_fetch_array($campaign_query);
		$campaign = $campaign_db['configuration_value'];
		$ship_text = '';
		
		$tools->getShipping();
	 	$this->shipping = $tools->shipping;
		
		foreach($this->shipping as $ship){     		
			
			if ( $ship [ 'country' ] == 'DE'){
				
				$ship_text .= $this->getDisplayShip ( $ship, $missing_config_costs_idealo_DE_active );	
				 
			}
			
			if ( $ship [ 'country' ] == 'AT'){
				
				$ship_text .= $this->getDisplayShip ( $ship, $missing_config_costs_idealo_AT_active );	
				 
			}
     	}
		$payment_text = '';	
		
		$tools->getPayment();
	 	$this->payment = $tools->payment;
		
		foreach($this->payment as $payment){     		
     		$payment_text .= $this->getDisplayPayment($payment);	
     	}
				
		$testmode_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_TESTMODE' LIMIT 1");
		$testmode_db = xtc_db_fetch_array($testmode_query);
		$testmode_db = $testmode_db['configuration_value'];
		
		$textfile_array[] = array ('id' => 'yes', 'text' => 'ja',);
		$textfile_array[] = array ('id' => 'no', 'text' => 'nein');
		
		$certificate_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CERTIFICATE' LIMIT 1");
		$certificate_db = xtc_db_fetch_array($certificate_query);
		$certificate_db = $certificate_db['configuration_value'];
		
		$certificate_array[] = array ('id' => '1', 'text' => 'ja',);
		$certificate_array[] = array ('id' => '0', 'text' => 'nein');
		
		$testmode =  IDEALO_REALTIME_TESTMODE_ACTIVE . '<br>' .
					 xtc_draw_pull_down_menu('testmode', $textfile_array, $testmode_db).'<br>';
		
		$article_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
		$article_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);
		
		$article_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER' LIMIT 1");
		$article_filter_db = xtc_db_fetch_array($article_filter_query);
		$article_value = $article_filter_db['configuration_value'];
		
		$article_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_ARTICLE_FILTER_VALUE' LIMIT 1");
		$article_filter_value_db = xtc_db_fetch_array($article_filter_value_query);
		$article_filter_value = $article_filter_value_db['configuration_value'];
		
		$article_filter = IDEALO_REALTIME_ARTICLE_FILTER . '<br>' .
						  IDEALO_REALTIME_ARTICLE_FILTER_SELECTION . '<br>'.
						  xtc_draw_pull_down_menu('article_filter',$article_filter_array , $article_value).'<br><br>'.
						  IDEALO_REALTIME_ARTICLE_FILTER_TEXT . '<br>' .
						  xtc_draw_input_field('article_filter_value', $article_filter_value) . '<br><br>';
		
		$brand_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
		$brand_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);
		
		$brand_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER' LIMIT 1");
		$brand_filter_db = xtc_db_fetch_array($brand_filter_query);
		$brand_value = $brand_filter_db['configuration_value'];
		
		$brand_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_BRAND_FILTER_VALUE' LIMIT 1");
		$brand_filter_value_db = xtc_db_fetch_array($brand_filter_value_query);
		$brand_filter_value = $brand_filter_value_db['configuration_value'];
		
		$brand_filter = IDEALO_REALTIME_BRAND_FILTER . '<br>' .
						IDEALO_REALTIME_BRAND_FILTER_SELECTION . '<br>'.
						xtc_draw_pull_down_menu('brand_filter',$brand_filter_array , $brand_value).'<br><br>'.
						IDEALO_REALTIME_BRAND_FILTER_TEXT . '<br>' .
						xtc_draw_input_field('brand_filter_value', $brand_filter_value) . '<br><br>';
		$cat_filter_array[] = array ('id' => 'filter', 'text' => 'filtern',);
		$cat_filter_array[] = array ('id' => 'export', 'text' => 'exportieren',);
		
		$cat_filter_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER' LIMIT 1");
		$cat_filter_db = xtc_db_fetch_array($cat_filter_query);
		$cat_value = $cat_filter_db['configuration_value'];
		
		$cat_filter_value_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_CAT_FILTER_VALUE' LIMIT 1");
		$cat_filter_value_db = xtc_db_fetch_array($cat_filter_value_query);
		$cat_filter_value = $cat_filter_value_db['configuration_value'];
		
		$cat_filter = IDEALO_REALTIME_CAT_FILTER . '<br>' .
					  IDEALO_REALTIME_CAT_FILTER_SELECTION . '<br>'.
					  xtc_draw_pull_down_menu('cat_filter',$cat_filter_array , $cat_value).'<br><br>'.
					  IDEALO_REALTIME_CAT_FILTER_TEXT . '<br>' .
					  xtc_draw_input_field('cat_filter_value', $cat_filter_value) . '<br><br>';
		
		$tools->getMinorderValues();
		
		
		$minOrderDisplay =	IDEALO_REALTIME_MIN_ORDER_TITLE . '<br><br>' .
							IDEALO_REALTIME_MIN_ORDER_VALUE . '<br>' .
		   			   		xtc_draw_input_field('idealo_realtime_minOrder_input', $tools->minOrder) . '<br>' .
							IDEALO_REALTIME_MIN_ORDER_TEXT . '<br><br>' .
							IDEALO_REALTIME_MIN_EXTRA_COSTS	. '<br>' .
							xtc_draw_input_field('idealo_realtime_minOrderPrice_input', $tools->minOrderPrice) . '<br>' .
							IDEALO_REALTIME_MIN_ORDER_PRICE_TEXT . '<br>' .
							IDEALO_REALTIME_MIN_ORDER_BORDER_VALUE . '<br>' .
							xtc_draw_input_field('idealo_realtime_minOrderBorder_input', $tools->idealoMinorderBorder) . '<br>' .
							IDEALO_REALTIME_MIN_ORDER_BORDER_TEXT;
		
		
   			   					
	    return array('text' => 	$missing_config_user. '<br><br>' .
	    						
	    						$testmode . '<br>' .
	    							    						
	    						URL . '<br>' .
	    						URL_HINT . '<br>' .
	    						xtc_draw_input_field('url_input', $url) . '<br>' .
	    						$missing_config_url_input . '<br><br>' .
	    						
	    						SHOP_ID . '<br>' .
	    						SHOP_ID_HINT . '<br>' .
	    						xtc_draw_input_field('shop_id_input', $shop_id) . '<br>' .
	    						$missing_config_shop_id_input . '<br><br>' .
	    						
								PASSWORT . '<br>' .
	    						PASSWORT_HINT . '<br>' .
	    						xtc_draw_input_field('password_input', $password) . '<br>' .
	    						$missing_config_password_input . '<br><br>' .
	    						
	    						PAGESIZE . '<br>' .
	    						PAGESIZE_HINT. '<br>' .
	    						xtc_draw_input_field('pagesize_input', $pagesize) . '<br>' .
	    						$missing_config_page_size_input . '<br><br>' .
	    						
	    						CERTIFICATE_TEXT . '<br>' .
	    						CERTIFICATE_TEXT_DESCRIPTION . '<br>' .
	    						xtc_draw_pull_down_menu( 'certificate', $certificate_array, $certificate_db ).'<br>'.
	    						
	    						SHIPPING . '<br>' .
	    						$missing_config_shipping . '<br><br>' .
	    						
	    						$ship_text .
																
	    						PAYMENT . '<br>' .
	    						$missing_config_payment . '<br><br>' .
	    							 
								$payment_text .
								
								SHIPPINGCOMMENT . '<br>' .
								SHIPPINGCOMMENT_HINT . '<br>' .
								xtc_draw_input_field('shippingcomment_input', $shipping_comment_text) . '<br><br>'.
								
	                            CAMPAIGNS.'<br>'.
	                            CAMPAIGNS_DESC.'<br>'.
	                          	xtc_draw_pull_down_menu('campaign',$campaign_array, $campaign).'<br><br>'.
								
								$article_filter .
								$brand_filter .
								$cat_filter .
								
								$minOrderDisplay .
								
								IDEALO_REALTIME_EXPORT_TEXT . '<br>'.
								
								REAL_TEXT . '<br><br>' .
								$realtime_link . '<br><br>' .
								
	                            '<br>' . xtc_button( $button_text ) . '<font size="+1">*</font> ' . 
	                
	                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set =' . $_GET['set'] . '&module=idealo_real')) .
	                            '<input id ="come_from" type="hidden" name="come_from" value="' . $_SERVER['HTTP_REFERER'] . '"> <br>' .
	                            EXPORT . '<br><br>' .
	                            TEXT_WARANTY_IDEALO_REALTIME
	                            );


    }


	
	 public function getDisplayPayment( $payment ){
	 	
	 	$active_array[] = array ('id' => '1', 'text' => 'ja',);
		$active_array[] = array ('id' => '0', 'text' => 'nein',);
		
		$country_array [] = array ('id' => '1', 'text' => 'DE',);
		$country_array [] = array ('id' => '2', 'text' => 'AT',);
		$country_array [] = array ('id' => '3', 'text' => 'DE/AT',);
		
		if( $payment['shipping'] == '1' ) {
			$nofix_scinclusive_yes = true;
			$nofix_scinclusive_no = false;
		} else {
			$nofix_scinclusive_yes = false;
			$nofix_scinclusive_no = true;
		}

		return 	  '<b>' . $payment['title'] . '</b><br>'.
				  xtc_draw_pull_down_menu('payment_' . $payment['db'] .  '_active', $active_array, $payment['active']).'<br>'. 
				  xtc_draw_input_field('payment_' . $payment['db'] . '_extrafee_fix', $payment['fix']).PAYMENTEXTRAFEE_INPUT_FIX.'<br>'.
				  xtc_draw_input_field('payment_' . $payment['db'] . '_extrafee_nofix', $payment['percent']).PAYMENTEXTRAFEE_INPUT_NOFIX.'<br>' .
	              xtc_draw_radio_field('payment_' . $payment['db'] . '_nofix_inkl_sc', 'yes', $nofix_scinclusive_yes).PAYMENTEXTRAFEE_RADIO_SCINCLUSIVE.'&nbsp;'.
	              xtc_draw_radio_field('payment_' . $payment['db'] . '_nofix_inkl_sc', 'no', $nofix_scinclusive_no).PAYMENTEXTRAFEE_RADIO_SCNOTINCLUSIVE.'<br>'.
	              xtc_draw_pull_down_menu('payment_' . $payment['db'] .  '_country', $country_array, $payment['country']).'<br><br>';
	              
		
	 }

	
	 public function getDisplayShip ( $ship, $missing_text ){
	 	
	 	$active_array[] = array ('id' => '1', 'text' => 'ja',);
		$active_array[] = array ('id' => '0', 'text' => 'nein',);
		$country_array[] = array ('id' => '1', 'text' => 'Gewicht',);
		$country_array[] = array ('id' => '2', 'text' => 'Preis',);
		$country_array[] = array ('id' => '3', 'text' => 'Pauschal',);
		
		return  SHIPPING_TEXT_01 . ' '. $ship['country'] . '?<br>' .
				xtc_draw_pull_down_menu('shipping_' . $ship['country'] . '_active', $active_array, $ship['active']).'<br>' .
				SHIPPING_TEXT_02 . '<br>' .
				xtc_draw_input_field('shipping_' . $ship['country'] . '_costs', $ship['costs']) . SHIPPING_TEXT_03 . '<br>' .$missing_text . '<br>' .
				SHIPPING_TEXT_04 . '<br>' .
				xtc_draw_input_field('shipping_' . $ship['country'] . '_free', $ship['free']) . SHIPPING_TEXT_05. '<br>' .
				SHIPPING_TEXT_06. '<br>' .
				xtc_draw_pull_down_menu('shipping_' . $ship['country'] . '_type', $country_array, $ship['type']). '<br><br>';
				
	 }
    
    
    
    public function check() {
    	
      if ( !isset( $this->_check ) ) {
      	
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_IDEALO_REALTIME_STATUS'");
        $this->_check = xtc_db_num_rows( $check_query );
        
      }
      
      return $this->_check;
      
    }

	
    public function install() {
      xtc_db_query ( "delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%IDEALO_REALTIME%'" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_FILE', '',  '6', '1', '', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_FILE_TITLE', 'User',  '6', '1', '', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_FILE_DESC', 'Bitte den Usernamen eingeben',  '6', '1', '', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_STATUS_TITLE', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_STATUS_DESC', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_LANGUAGE', 'DE',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_CERTIFICAT', '1',  '6', '1', '', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_MISSING_CONFIG', '1',  '6', '1', '', now())" );
      xtc_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_IDEALO_REALTIME_TESTMODE', '1',  '6', '1', '', now())" );
      
      $this->createRealtime();
      
    }

	
    public function remove() {
    	
      xtc_db_query ( "delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode ( "', '", $this->keys() ) . "')" );
      xtc_db_query ( "delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE '%IDEALO_REALTIME%'" );
      
      $this->deleteRealtime();
      
    }

    public function keys() {
    	
      return array ( 'MODULE_IDEALO_REALTIME_STATUS','MODULE_IDEALO_REALTIME_FILE' );
      
    }
    
    
  }
?>
