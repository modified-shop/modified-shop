<?php
include("klarna.php");
include("klarna_settings.php");

$invno = $_POST["invno"];
$status = send_invoice($eid, $invno, $secret, $result);

switch ($status) {
 case 0:
      echo "<p>Invoice " . $invno . " has been sent."; 
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
