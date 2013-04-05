<?php
include("klarna.php");
include("klarna_settings.php");
global $KRED_HANDLING;
global $KRED_SHIPMENT;

$invno = $_POST["invno"];
$artno = $_POST["artno"];
$newQty = $_POST["qty"];

$status = update_goods_qty($eid, $invno, $secret, $artno, $newQty, $result);

switch ($status) {
 case 0:
      echo "<p>The quantity of goods $artno of invoice " . $invno . " has been updated.";
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
