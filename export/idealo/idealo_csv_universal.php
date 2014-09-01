<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/



class idealo_csv_universal {
	public $minOrderPrice = '';
	public $minOrder = '';
	public $minorderBorder = '';
	
	
	 public function checkMinExtraPrice ( $art_price ){	

	 	if ( ( float ) $this->minorderBorder > ( float ) $art_price ){
	 		
	 		return true;
	 		
	 	}
	 	
	 	return false;
	 	
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
	
	
	 public function prepareText( $string ){
	 	
	 	$spaceToReplace = array ( "$", ".", "|" );
	 	
	 	$string = str_replace ( $spaceToReplace, " ", $string );
	 	
	 	$string = str_replace ( "ä", "ae", $string );
	 	$string = str_replace ( "ü", "ue", $string );
	 	$string = str_replace ( "ö", "oe", $string );
	 	$string = str_replace ( "Ä", "Ae", $string );
	 	$string = str_replace ( "Ü", "ue", $string );
	 	$string = str_replace ( "Ö", "Oe", $string );
	 	
	 	$string = str_replace ( "ß", "ss", $string );
	 	
		return $string;	 
			
	 }
	 
	 
    public function cleanText( $text, $cut ){
				
		if ( mb_detect_encoding( $text, 'UTF-8, ISO-8859-1' ) !== 'UTF-8' ){
			
			$text = utf8_encode ( $text );
			
		}
    	$text = str_replace ( array ( "\r\n", "\r", "\n", "|", "&nbsp;" ), "", $text );
		$spaceToReplace = array ( "<br>", "<br />", "\n", "\r", "\t", "\v", chr(13) );
		$commaToReplace = array ( "'" );
        $text = strip_tags ( $text );
		$text = str_replace ( $spaceToReplace, " ", $text );
		$text = str_replace ( $commaToReplace, ", ", $text ) ;
		$Regex = '/<.*>/';
		$Ersetzen = ' ';
		$text = preg_replace ( $Regex, $Ersetzen, $text );
		$text = html_entity_decode ( $text, ENT_QUOTES, "UTF-8" );
		
		$text = $this->prepareText ( $text );
		$regex ='/[^\d\w\s_\!\$\%&;:+\^\~#\-|\/]/';
		$text = preg_replace ( $regex, '', $text );
		if ( function_exists ( mb_substr ) ){
			
			$text = mb_substr ( $text, 0, $cut );
				
		}else{
			 
		 	$text = substr( $text, 0, $cut );
		 		
		}
		$text = htmlentities ( $text, ENT_QUOTES, "UTF-8" );

		return $text;
				
    }
	
	
}

?>
