<?php
include("klarna.php");
include("klarna_settings.php");;

$rno = $_POST["rno"];
$amount = $_POST["amount"];
$orderid1 = $_POST["orderid1"];
$orderid2 = $_POST["orderid2"];
$flags = 0;
$status = split_reservation($rno, $amount, $orderid1, $orderid2, $flags, $eid, $secret, $result);

switch ($status) {
 case 0:
      echo "<p>Result: $result </p><p>Reservation " . $rno . " has been splitted.<br/>";
      echo "<p>New reservation id " . $result . "<br/>";  
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
