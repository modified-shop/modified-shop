<?php
include("klarna.php");
include("klarna_settings.php");

$sum = $_POST["sum"];
$sum = $sum *100;
$pclass = $_POST["pclass"];
$flags = 0;

if($_POST["currency"] == "sek"){
  $currency = $KRED_SEK;
  $cur = "SEK";
}else if($_POST["currency"] == "nok"){
  $currency = $KRED_NOK;
  $cur = "NOK";
}else if($_POST["currency"] == "eur"){
  $currency = $KRED_EUR;
  $cur = "EUR";
}else if($_POST["currency"] == "dkk"){
  $currency = $KRED_DKK;
  $cur = "DKK";
}

$status = periodic_cost($eid, $sum, $pclass, $currency, 
                        $flags, $secret, $result);

$result = $result /100;

switch ($status) {
 case 0:
      echo "<p>Result: </p>" . $result . $cur;  
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
