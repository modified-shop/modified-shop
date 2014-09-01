<?php
include("klarna.php");
include("klarna_settings.php");

$rno = $_POST["rno"];
$amount = $_POST["amount"];
$result = "";
$status = change_reservation($rno, $amount, $eid, $secret, 0, $result);

switch ($status) {
 case 0:
      echo "<p>Result: $result </p><p>Reservation " . $rno . " has ben changed.<br/>";    
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
