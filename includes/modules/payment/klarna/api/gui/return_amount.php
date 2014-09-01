<?php
include("klarna.php");
include("klarna_settings.php");

$invno = $_POST["invno"];
$amount = $_POST["amount"];
$vat = $_POST["vat"];
$amount = $amount * 100;
$flags = intval($_POST['flags']);

$status = return_amount($eid, $invno, $amount, $vat, $secret, $flags, utf8_decode('Beskrivning av returen, åäö'), $result);

$amount = $amount / 100;
if(($KRED_INC_VAT & $flags) !== $KRED_INC_VAT)
{
	$vat = ($vat / 100) +1;
	$amount = $amount * $vat;
}	

switch ($status) {
 case 0:
      echo "<p>" . $amount . ":- has been returned from invoice " . $invno . ".</p><br/>";
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
