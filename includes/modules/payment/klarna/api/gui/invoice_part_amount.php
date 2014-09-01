<?php
include("klarna.php");
include("klarna_settings.php");

$artnoList =
    array(mk_artno(intval($_POST["qty0"]), $_POST["artno0"]),
	  mk_artno(intval($_POST["qty1"]), $_POST["artno1"]),
	  mk_artno(intval($_POST["qty2"]), $_POST["artno2"]));
$invno = $_POST["invno"];


$status = invoice_part_amount($eid, $invno, $artnoList, $secret,
			      $result);

switch ($status) {
 case 0:
   echo "<p>Result: " . $result . "</p>";
   break;
 case -99:
   echo "<p>Internal error: <pre>" . $result . "</pre></p>";
   break;
 default:
   echo "<p>Error code: <em>" . $status . "</em></p>";
   echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
