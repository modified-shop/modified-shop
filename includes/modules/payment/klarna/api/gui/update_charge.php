<?php
include("klarna.php");
include("klarna_settings.php");
global $KRED_HANDLING;
global $KRED_SHIPMENT;

$invno = $_POST["invno"];
$type = $_POST["type"];
$newAmount = $_POST["newAmount"]*100;

if($type == "handling")
	$chargeType = $KRED_HANDLING;
else if($type == "shipment")
	$chargeType = $KRED_SHIPMENT;

$status = update_charge_amount($eid, $invno, $secret, $chargeType, $newAmount, $result);

switch ($status) {
 case 0:
      echo "<p>The charge amount of invoice " . $invno . " has been updated.";
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
