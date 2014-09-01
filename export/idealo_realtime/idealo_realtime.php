<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2013,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/



ini_set('error_reporting', E_ERROR);
ini_set('display_errors', '1');

require_once dirname(__FILE__) . '/../../includes/configure.php';
include_once dirname(__FILE__) . '/../../inc/xtc_db_query.inc.php';
include_once dirname(__FILE__) . '/../../includes/database_tables.php';
include_once dirname(__FILE__) . '/application_idealo.php';
include_once dirname(__FILE__) . '/export_functions_idealo.php';
include_once dirname(__FILE__) . '/tools.php';
include_once dirname(__FILE__) . '/communication.php';
include_once dirname(__FILE__) . '/idealo_definition.php';

class idealo_realtime{
	
	private $send_failed_request = false;
	
	private $update = array();
	
	private $login = array();
	
	private $timeGone = false;
		
	public function __construct(){	
		$tools = new tools();
		$this->login = $tools->getLogin();

		$communication = new Communication($this->login);
		if($tools->checkActive() == "True"){
			if($this->timeGone()){
			    $this->timeGone = true;
			    $communication->tools->newTimestamp();
				$this->sendFailedRequests();
			}
			$this->checkUpdates();
			$this->updateIdealo();
			
		}else{
			$communication->dropErrorTable();
		}
		$tools->cleanTableIdealoRealtimeUpdate();
		echo'DONE';
	}
	
	
	
	public function updateIdealo(){
		$tools = new tools();
		$tools->AllNeeded();
		
    	$communication = new Communication($this->login);
    	
    	$count = count($this->update);		
		$update = 0;
		$interval = $this->login['pagesize'];
		
		if($count < $this->login['pagesize']){
			$interval = $count;
		}
		
		while($count > 0){
			$tools->getXMLBegin($this->login['testmode']);
	    	for($i = $update; $i < $update + $interval; $i++){
	    		if($this->update[$i]['event'] == 'DELETE'){
	    		    $tools->deleteProductAtIdealo($this->update[$i]['product']);
	    		}else{	
	    			$tools->getXML($this->update[$i]['product']);
	       		}
	    	}
	    	@$communication->sendRequest($tools->xml->saveXML());
	    	
	    	$count = $count - $this->login['pagesize'];
	    	$update = $update + $this->login['pagesize'];
		}
				 	
	}
	
	
	
	 private function timeGone(){
	 	$result = xtc_db_query("SELECT * FROM " . IDEALO_REALTIME_CRON_TABLE . " WHERE  ( `to_execute` < now() ) order by `to_execute`");
	 	
	 	if(xtc_db_fetch_array($result) !== false){	
			return true;
	 	}else{
			return false;
	 	}
	 	
	 }
	
	
	
	 private function sendFailedRequests(){	 	
 		$communication = new Communication($this->login);
 		
 		$sql = "SELECT * FROM `" . IDEALO_REQUEST_ERROR_TABLE . "`;";		
		$error_requests = xtc_db_query($sql);

		while($error_request = xtc_db_fetch_array($error_requests)){
			$this->send_failed_request = true;
			@$communication->sendRequest($error_request['xml'], false,  $error_request['id']);
		}
	 	
	 }
	
	
	
	public function checkUpdates(){
		$ids = array();
		$product_ids = xtc_db_query("	SELECT DISTINCT `products_id`
										FROM `idealo_realtime_update`");
		
		while($product = xtc_db_fetch_array($product_ids)){
			$ids [] = $product['products_id'];
		}
		if(empty($ids)){
			if(!$this->send_failed_request){
				if($this->timeGone){
					$communication = new Communication($this->login);
					$communication->setTimeStamp();
				}
			}			
		}else{
			foreach($ids as $id){
				$product_ids = xtc_db_query ( "	SELECT `id`, `event`
												FROM `idealo_realtime_update`
												WHERE `products_id` = " . $id .
											  " ORDER BY `id` ASC" );
				$update = array();
			
				while($product = xtc_db_fetch_array($product_ids)){
					$update[] = $product['event'];
				}
				
				$count = count($update);
				$count--;
				
				$this->update[] = array('product'	=>	$id,
										'event'		=>	$update[$count]
										);
			}			
		}
		
	}
	
}
$className = new idealo_realtime();
?>