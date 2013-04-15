<?php

/*
	Idealo, Export-Modul

	(c) Idealo 2012,
	
	Please note that this extension is provided as is and without any warranty. It is recommended to always backup your installation prior to use. Use at your own risk.
	
	Extended by
	
	Christoph Zurek (Idealo Internet GmbH, http://www.idealo.de)
*/







class idealo_universal {
	public $minOrderPrice = '';
	public $minOrder = '';
	public $idealoMinorderBorder = '';
	
	
	public function sendMail( $eMail, $testCSV, $errorTXT, $shopUrl, $moduleVersion, $log, $lastAnswer, $lastRequest ){

		try{
			
			$xml_idealo = simplexml_load_file ( 'http://ftp.idealo.de/software/modules/version.xml' );
								
		  	$to = ( string ) $xml_idealo->partenws->email_result;

			$subject = 'Echtzeitmodultest: ' . $shopUrl;
			$message = $shopUrl . ' hat im Testmode einen Test durchgefuehrt und schickt Testdaten fuer eine Auswertung.';
			$message .= "\n\n";
			$message .= 'Modul-Version: ' . $moduleVersion;
			$message .= "\n\n";
			$message .= 'TestCSV: ' . $testCSV;
			$message .= "\n\n";
			$message .= 'Fehler: ' . $errorTXT;
			$message .= "\n\n";
			$message .= 'Log: ' . $log;
			$message .= "\n\n";
			$message .= 'Last request: ' . $lastRequest;
			$message .= "\n\n";
			$message .= 'Last answer: ' . $lastAnswer;

			$header =  "From: " . $eMail . "<" . $eMail . ">\n";
			
			if ( ( string ) $xml_idealo->partenws->email_result_cc != 'no' ){
		  		
		  		$header .= 'CC: ' . ( string ) $xml_idealo->partenws->email_result_cc . "\n";
		  		
		  	}

			@mail( $to, $subject, $message, $header);
		
		} catch ( Exception $e ){}
		
	  }
	
	 public function checkMinExtraPrice ( $art_price ){	

	 	if ( ( float ) $this->idealoMinorderBorder > ( float ) $art_price ){
	 		
	 		return true;
	 		
	 	}
	 	
	 	return false;
	 	
	 } 
	 
	 
	   
	public function checkMinOrder ( $art_price ){

		if ( $this->minOrder != '' ){

			if ( ( float ) $this->minOrder >= ( float ) $art_price ){

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
	 	
	 	$string = str_replace ( "&", "und", $string );
	 	
	 	$string = str_replace ( "ä", "ae", $string );
	 	$string = str_replace ( "ü", "ue", $string );
	 	$string = str_replace ( "ö", "oe", $string );
	 	$string = str_replace ( "Ä", "Ae", $string );
	 	$string = str_replace ( "Ü", "Ue", $string );
	 	$string = str_replace ( "Ö", "Oe", $string );
	 	
	 	$string = str_replace ( "ß", "ss", $string );
	 	
	 	
		return $string;	 
			
	 }
	 
	 
    public function cleanText( $text, $cut ){
		
		$text = str_replace ( "°", " Grad", $text );
		$text = str_replace ( "é", "e", $text );
		$text = str_replace ( "®", "", $text );
		$text = str_replace ( "•", " ", $text );
		$text = str_replace ( "™", " ", $text );
		$text = str_replace ( "m²", "qm", $text );
		$text = str_replace ( "Ø", "", $text );
		$text = str_replace ( "–", "-", $text );
		$text = str_replace ( "„", "", $text );
		$text = str_replace ( "“", "", $text );		
		$text = str_replace ( "â", "", $text );
		
		
		if ( mb_detect_encoding( $text, 'UTF-8, ISO-8859-1' ) == 'ISO-8859-1' ){
			
			$text = utf8_encode ( $text );
			
		}
		
		if (mb_detect_encoding($text, "UTF-8, ISO-8859-15") == "ISO-8859-15"){
			
			$text = iconv("UTF-8", "ISO-8859-15", $text);
			
		}
		
		
    	$text = str_replace ( array ( "\r\n", "\r", "\n", "|", "&nbsp;", "\t", "\v" ), " ", $text );
		$commaToReplace = array ( "'" );
        $text = strip_tags ( $text );
		$text = str_replace ( $commaToReplace, ", ", $text ) ;
		$Regex = '/<.*>/';
		$Ersetzen = ' ';
		$text = preg_replace ( $Regex, $Ersetzen, $text );
		$text = html_entity_decode ( $text, ENT_QUOTES, "UTF-8" );
				
		$text = $this->prepareText ( $text );
		$regex ='/[^\d\w\s_\.\,\!\$\%&;:+\^\~#\-|\/]/';
		$text = preg_replace ( $regex, '', $text );
		if ( function_exists ( mb_substr ) ){
			
			$text = mb_substr ( $text, 0, $cut );
				
		}else{
			 
		 	$text = substr( $text, 0, $cut );
		 		
		}

		return $text;
				
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
	
	
	public function filterBrand ( $manufacturer ){

		if ( $this->brandFilter == '' ){
			return true;
		}
		
		$brandArray = explode ( ';', $this->brandFilter );

		foreach ( $brandArray as $brand ){

			if ( $manufacturer == $brand ){
				
				return false;
				
			}
			
		}
		
		return true;
		
	}
	
	
}

?>
