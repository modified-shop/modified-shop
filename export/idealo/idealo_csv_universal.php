<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2013,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/


include_once ( DIR_FS_CATALOG . 'export/idealo/idealo_db_tools.php' );


class idealo_csv_universal extends idealo_db_tools_csv{
	public $minOrderPrice = '';
	public $minOrder = '';
	public $minorderBorder = '';
	
	
	public $separatorArray = array ('0' => array('separator'	=> '|', 
												 'comes' 	 	=> 0,),
									'1' => array('separator' 	=> ';', 
												 'comes' 	 	=> 0,),			 
									'2' => array('separator' 	=> '$', 
												 'comes' 	 	=> 0,),			 
									'3' => array('separator'	=> '~', 
												 'comes' 	 	=> 0,),			 
									'4' => array('separator' 	=> ',', 
												 'comes' 	 	=> 0,),
									'5' => array('separator' 	=> '@', 
												 'comes' 	 	=> 0,),
									'6' => array('separator' 	=> '*', 
												 'comes' 	 	=> 0,),
									'7' => array('separator' 	=> '%', 
												 'comes' 	 	=> 0,),
									'8' => array('separator' 	=> '<', 
												 'comes' 	 	=> 0,),
									'9' => array('separator'	=> '>', 
												 'comes' 	 	=> 0,),
									'10' => array('separator' 	=> '#', 
												 'comes' 		=> 0,),
									'11' => array('separator' 	=> '{', 
												 'comes' 		=> 0,),
									'12' => array('separator' 	=> '}', 
												 'comes' 		=> 0,),
									'13' => array('separator' 	=> '^', 
												 'comes' 		=> 0,),			 			 			 			 			 			 
									);			 
												 	
	public $separatorWarning = false;
	
	public $separatorInt = 0;
	
	
	 public function checkMinExtraPrice ( $art_price ){	

	 	if ( ( float ) $this->minorderBorder > ( float ) $art_price ){
	 		
	 		return true;
	 		
	 	}
	 	
	 	return false;
	 	
	 } 
	 
	 
	 
	 public function checkSeparator( $text, $separator ){

	 	if ( strpos ($text, $separator ) !== false ){
	 		
	 		$this->separatorWarning = true;
	 		$this->separatorInt++;
	 		
	 	}
	 	
	 	foreach ( $this->separatorArray as $key => $separ ){
	 		
	 		if ( $separ != $separator ){
	 			
	 			if ( strpos ($text, $separ['separator'] ) !== false ){
	 			
		 			$this->separatorArray[$key]['comes']++;
		 			
		 		}
		 		
	 		}
	 		
	 	}
	 	
	 	return $text;
	 	
	 }
	
	 
	public function checkMinOrder ( $art_price ){

		if ( $this->minOrder != '' ){

			if ( ( float ) $this->minOrder > ( float ) $art_price ){

				return true;
				
			} 
			
		}
		
		return false;
		
	}
	
	
	
	public function checkEan ( $ean ){
		$ean = preg_replace ( "/([^\d])/", "", $ean );
		if ( strlen ( $ean ) == 13 ){
			if ( $this->Ean13Checksum ( substr ( $ean, 0, 12 ) ) == $ean { 12 } ) {
				
	        	return true;
	        	
			}
			
	    }
	    
	    return false;
			
	}

	
	public function Ean13Checksum ( $ean ){
	    if ( strlen ( $ean ) != 12 ) {
	    	
	        return false;
	        
	    }
	    
	    $check = 0;
	    for ( $i = 0; $i < 12; $i++ ){
	    	
	        $check += ( ( $i % 2 ) * 2 + 1 ) * $ean { $i };
	        
	    }
	    
	    $check = ( 10 - ( $check % 10 ) ) % 10;
	    
	    return $check;
	    
	}

	 
	 
    public function cleanText( $text, $cut ){
		$spaceToReplace = array ( "&nbsp;", "\r\n", "\n", "\r", "\t", "\v", chr(13) );
		$commaToReplace = array ( "'" );
		$text = str_replace ( $spaceToReplace, " ", $text );
		$text = str_replace ( $commaToReplace, ", ", $text ) ;
		$Regex = '/<.*>/';
		$Ersetzen = ' ';
		$text = preg_replace ( $Regex, $Ersetzen, $text );
		if ( function_exists ( mb_substr ) ){
			
			$text = mb_substr ( $text, 0, $cut );
				
		}else{
			 
		 	$text = substr( $text, 0, $cut );
		 		
		}	
		
		return $text;
				
    }
	
	
}

?>
