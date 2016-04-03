<?php

/* --------------------------------------------------------------
   $Id: talkto.php

   INTEFACE FOR SENDING REQUEST AND GETTING RESPONSE

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommercecoding standards (a typical file) www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  //VERSION


function talkto($params) {
	global $modulVersion;

	// create request header;
	$reqheader = "POST /api/api.php HTTP/1.1\r\n";  
	$reqheader.= "Host: www.safeterms.de\r\n";  
    	$reqheader.= "User-Agent: XTC_MODULE (Version ".$modulVersion.")\r\n"; 
	$reqheader.= "Accept: text/html\r\n";
	$reqheader.= "Pragma: no-cache\r\n"; 
	$reqheader.= "Cache-Control: no-cache\r\n";
	$reqheader.= "Content-Type: application/x-www-form-urlencoded\r\n";

	$reqbody = "";

	// create the request body
	foreach($params as $paramname=>$value) {
		$reqbody.="<ENTRY name=\"".encode_htmlspecialchars($paramname)."\">".encode_htmlspecialchars($value)."</ENTRY>";
	}

	$reqbody = "request=".urlencode($reqbody);

	// put the length to the Header
	$reqheader.= "Content-Length: ".strlen($reqbody)."\r\n";
	$reqheader.= "Connection: Close\r\n";


	// Form the Request
	$request = $reqheader."\r\n".$reqbody;


	$txtresp = "";

	// Connecting the Safeterms.de Server
	$fp = fsockopen("www.safeterms.de", 80, $errno, $errstr, 10);
	if (!$fp) {
		$txtresp = "<STATUS>ERROR</STATUS>\n";
		$txtresp.= "<ENTRY name=\"ERROR\">\n";
		$txtresp.= "\t<ENTRY name=\"ERROR_NUM\">0</ENTRY>\n";
		$txtresp.= "\t<ENTRY name=\"ERROR_DESC\">NO CONNECTION TO SAFETERMS.DE</ENTRY>\n";
		$txtresp.= "</ENTRY>";
	} else {
    		fwrite($fp, $request);
    		while (!feof($fp)) {
        		$txtresp.= fgets($fp, 128);
    		}
    		fclose($fp);
	}

	$txtresp = $txtresp;


	$temp = explode("\r\n\r\n",$txtresp);
	$respheader = $temp[0];
	$resplength = explode("Content-Length: ",$respheader);
	$resplength = explode("\r\n",$resplength[1]);
	$resplength = $resplength[0];
	$respdata = substr($txtresp,strlen($txtresp)-$resplength,$resplength);


	// Creating the Response array
	$response = array();

	// DONT USE XML PARSER FOR MORE COMPABILITY
		// STATUS
		$temp = explode("<STATUS>",$respdata);
		$temp = explode("</STATUS>",$temp[1]);
		$response["STATUS"] = $temp[0];
		// ENTRYS
		$temp = explode("<ENTRYS>",$respdata);
		$temp = explode("</ENTRYS>",$temp[1]);
		$entrys = $temp[0];
		// Filter Entrys
		$temp = explode("<ENTRY name=\"",$entrys);
		foreach ($temp as $entry) {
			$entry = explode("\"",$entry);
			$entry_name = preg_replace("/[^0-9a-zA-Z]/","",$entry[0]);
			if ($entry_name<>"") {
				$entry = explode(">",$entry[1]);
				$entry = explode("<",$entry[1]);
				$entry_value = $entry[0];
				$response[$entry_name]=decode_htmlspecialchars($entry_value);
			}
		}

	return $response;
}


?>