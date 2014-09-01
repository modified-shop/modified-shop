<?php
include("klarna.php");
include("klarna_settings.php");
$estoreUser = $_POST["estore_user"];
$estoreOrderNo = $_POST["estore_order_no"];

$goodsList = array();		   
for($i=0; $i<=4; $i++)
{
	$flags = 0;
	if(isset($_POST['inclvat'.$i]))
		$flags += $KRED_INC_VAT;
		
	switch($_POST['precision'.$i]) 
	{
		case "1000": $flags += $KRED_PRINT_1000; break;
		case "100":  $flags += $KRED_PRINT_100; break;
		case "10":   $flags += $KRED_PRINT_10; break; 
	}
	
	switch($_POST['goods_type'.$i])
	{
		case 'shipment': 
			$flags += $KRED_IS_SHIPMENT; 
		break;
		case 'handling': 
			$flags += $KRED_IS_HANDLING; 
		break;
	}
	
	$goodsList[] = mk_goods_flags(intval($_POST['qty'.$i]), $_POST['artno'.$i], $_POST['desc'.$i],
		   intval($_POST['price'.$i])*100, $_POST['vat'.$i], 0, $flags);
}

$shipmentfee = 0;
$shipmenttype = $NORMAL_SHIPMENT;
$handlingfee = 0;
$pno = $_POST["pno"];
$fname = $_POST["fname"];
$lname = $_POST["lname"];
$street = $_POST["street"];
$postno = $_POST["postno"];
$city = $_POST["city"];
$telno = $_POST["telno"];
$cellno = $_POST["cellno"];
$email = $_POST["email"];
$house_num = "";
if($_POST["country"] == "sweden"){
  $country = $KRED_ISO3166_SE;
}else if($_POST["country"] == "norway"){
  $country = $KRED_ISO3166_NO;
}else if($_POST["country"] == "finland"){
  $country = $KRED_ISO3166_FI;
}else if($_POST["country"] == "denmark"){
  $country = $KRED_ISO3166_DK;
}else if($_POST["country"] == "germany"){
  $country = $KRED_ISO3166_DE;
  $house_num = $_POST["housenum"];
  }
  else if($_POST["country"] == "netherlands"){
  $country = $KRED_ISO3166_NL;
  $house_num = $_POST["housenum"];
  $house_ext = $_POST["houseext"];
  }

$addr = mk_addr("", $street, $postno, $city, $country, $telno, $cellno, $email, $house_num);
$passwd = "";
$clientIp = $_SERVER["REMOTE_ADDR"];
$newPasswd = "";

if ($_POST["auto"] == "yes")
    $flags = $KRED_AUTO_ACTIVATE;
else
    $flags = 0;

if ($_POST["pre"] == "yes")
    $flags |= $KRED_PRE_PAY;
    
if($_POST['test'] == 'yes')
    $flags |= $KRED_TEST_MODE; 

$comment = $_POST["comment"];
$ready_date = "";
$rand_string = "";

if($_POST["currency"] == "sek")
  $currency = $KRED_SEK;
elseif($_POST["currency"] == "nok")
  $currency = $KRED_NOK;
elseif($_POST["currency"] == "eur")
  $currency = $KRED_EUR;
elseif($_POST["currency"] == "dkk")
  $currency = $KRED_DKK;

$pno_encoding = $KRED_SE_PNO;

if($_POST["language"] == "swedish")
  $language = $KRED_ISO639_SV;
else if($_POST["language"] == "norwegian1")
{
  $language = $KRED_ISO639_NB;
  $pno_encoding = $KRED_NO_PNO;
} else if($_POST["language"] == "finnish")
{
  $language = $KRED_ISO639_FI;
  $pno_encoding = $KRED_FI_PNO;
} else if($_POST["language"] == "danish")
{
  $language = $KRED_ISO639_DA;
  $pno_encoding = $KRED_DK_PNO;
} else if($_POST["language"] == "german")
{
  $language = $KRED_ISO639_DE;
  $pno_encoding = $KRED_DE_PNO;
} else if($_POST["language"] == "netherlands")
{
  $language = $KRED_ISO639_NL;
  $pno_encoding = $KRED_NL_PNO;
}

$pclass = -1;
$ysalary = $_POST["ysalary"];
$status =
     add_transaction($eid, $estoreUser, $secret, $estoreOrderNo,  $goodsList,
                 $shipmentfee, $shipmenttype, $handlingfee, $pno, $fname,
                 $lname, $addr, $passwd, $clientIp, $newPasswd, $flags,
                 $comment, $ready_date, $rand_string, $currency, $country,
		 $language, $pno_encoding, $pclass, $ysalary, $result);

switch ($status) {
 case 0:
      echo "Invoice with invoice number " . $result . " has been created.";
      break;
 case -99:
      echo "Internal error:\n" . $result;
      break;
 default:
      echo "Error code:" . $status . "\n";
      echo "Reason: " . $result;
}
?>
