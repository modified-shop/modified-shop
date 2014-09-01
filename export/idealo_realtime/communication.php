<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/





include_once ( DIR_FS_CATALOG. 'export/idealo_realtime/communication_universal.php');
include_once ( DIR_FS_CATALOG. 'export/idealo_realtime/db_connection.php');
require_once ( DIR_FS_CATALOG. 'export/idealo_realtime/idealo_definition.php' );
include_once ( DIR_FS_CATALOG. 'export/idealo_realtime/tools.php');

class Communication extends communication_universal{
	
	public $login = array();
	public $tools;
	public $path = '';
	public $db_connection;
		
	
	public function __construct( $login = array() ){
		$path = __FILE__;
		$this->path = substr ( $path, 0 , -40 );
		
		if ( $login [ 'testmode' ] == '1' ){
			
			$this->getTestLogin();
			
		}else{
			$this->login = array(	'shop_id'	=> $login [ 'idealo_shop_id' ],
									'user'		=> $login [ 'user' ],
									'password'	=> $login [ 'password' ],
									'url'		=> $login [ 'webservice' ],
									'pagesize'	=> $login [ 'pagesize' ],
									'testmode'	=> '0'
								);
			
			
		}
		
		$this->db_connection = new Idealo_DB_Connection();
		
		$this->tools = new tools();
		
		$this->certificateCheck( $login [ 'certificate' ] ) ;	
			
	}
	
	
	
	private function certificateCheck( $cetrificate_setting ){
		
		if ( $cetrificate_setting == '1' ){
			
			$this->certificate = true;
			
		}else{
			
			$this->certificate = false;
			
		}

	}
	
	
	
	public function deaktivateModule(){
		
		xtc_db_query("	update " . TABLE_CONFIGURATION . "
					    set configuration_value = 'False'
					    where configuration_key = 'MODULE_IDEALO_REALTIME_CERTIFICATE'");
		
	}
	
}
