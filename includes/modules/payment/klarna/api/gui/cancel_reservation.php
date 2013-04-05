<?php
include("klarna.php");
include("klarna_settings.php");
global $eid;
global $secret;

$rno = $_POST["rno"];
$flags = 0;
$result = "";
$status = cancel_reservation($rno, $eid, $secret, $flags, $result);

switch ($status) {
 case 0:
      echo "<p>Result: $result </p><p>Reservation " . $rno . " has ben cancelled.<br/>";
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
