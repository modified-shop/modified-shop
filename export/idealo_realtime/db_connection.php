<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/



class Idealo_DB_Connection{
	
	
	
	public function writeDB ( $sql ){

		try{

			xtc_db_query ( $sql );
	
		} catch ( Exception $e ){}
		
	}
	
	
	
	public function readDB ( $sql ){
		
		try{
			
			$result = xtc_db_query ( $sql );

			return array ( xtc_db_fetch_array ( $result ) );
	
		} catch ( Exception $e ){}
		
	}
	
	
	
	public function dropTable ( $table ){
		
		if ( $this->tableExists ( $table ) ){

			$this->writeDB ( "DROP TABLE `" . $table . "`;");
			
		}
				
	}
	
	
	public function tableExists ( $table ){
		
		$exists = $this->readDB ( "SHOW tables LIKE '" . $table . "';" );
		$exists = $exists[0];
		
		if ( $exists !== false ){

			return true;
			
		}else{

			return false;
			
		}
						
	}
	
	
	
	public function checkTableExists ( $table ){
		
		return $this->tableExists ( $table );
		
	}
	
}

?>
