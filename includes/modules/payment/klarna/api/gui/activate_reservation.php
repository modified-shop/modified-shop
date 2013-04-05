<?php
include("klarna.php");
include("klarna_settings.php");

$rno = $_POST["rno"];
$pno = $_POST["pno"];
$reference = $_POST["reference"];
$reference_code = $_POST["reference_code"];
$orderid1 = $_POST["orderid1"];
$orderid2 = $_POST["orderid2"];

$lfname = $_POST["lfname"];
$llname = $_POST["llname"];
$lstreet = $_POST["lstreet"];
$lhousenum = $_POST["lhousenum"];
$lpostno = $_POST["lpostno"];
$lcity = $_POST["lcity"];
$lcountry = $_POST["lcountry"];

$ffname = $_POST["ffname"];
$flname = $_POST["flname"];
$fstreet = $_POST["fstreet"];
$fhousenum = $_POST["fhousenum"];
$fpostno = $_POST["fpostno"];
$fcity = $_POST["fcity"];
$fcountry = $_POST["fcountry"];

$shipmenttype = $_POST["shipmenttype"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$cell = $_POST["cell"];
$clientip = $_SERVER["REMOTE_ADDR"];
$flags = 0;
$currency = $_POST["currency"];
$country = $_POST["country"];
$language = $_POST["language"];
$pnoencoding = $_POST["pnoencoding"];
$pclass = -1;
$ysalary = $_POST["ysalary"];
$ocr = "";

$goodslist = array();

$goodslist[] = mk_goods($_POST["qty1"], $_POST["artno1"], $_POST["title1"],
			$_POST["price1"], $_POST["vat1"],0);
$goodslist[] = mk_goods($_POST["qty2"], $_POST["artno2"], $_POST["title2"],
			$_POST["price2"], $_POST["vat2"],0);

$laddr = mk_address($lfname, $llname, $lstreet, $lpostno, $lcity, $lcountry, $lhousenum);
$faddr = mk_address($ffname, $flname, $fstreet, $fpostno, $fcity, $fcountry, $fhousenum);
// Since version 1.47 we have added a $ocr as the third parameter to the
// activate_reservation function. If you are using activate_reservation and dont
// want to do any changes to the parameter list you can use activate_reservation_old instead
// which is a copy of the old activate_reservation function.
$status = activate_reservation($rno, $pno, $ocr, $goodslist, $reference, 
				$reference_code, $orderid1, $orderid2, $laddr, 
				$faddr, $shipmenttype, $email, 
				$phone, $cell, $clientip, $flags,  
				$currency, $country, $language,
				$eid, $secret, $pnoencoding, $pclass, $ysalary, "",
                                $result);

switch ($status) {
 case 0:
      echo "<p>Status and ocr</p>";
      foreach($result as $line){
	echo $line . "<br/>\n";	
      }    
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
