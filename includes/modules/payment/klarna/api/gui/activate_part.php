<?php
include("klarna.php");
include("klarna_settings.php");;

$artnoList =
    array(mk_artno(intval($_GET["qty0"]), $_GET["artno0"]),
	  mk_artno(intval($_GET["qty1"]), $_GET["artno1"]),
	  mk_artno(intval($_GET["qty2"]), $_GET["artno2"]));
$invno = $_POST["invno"];

$status = activate_part($eid, $invno, $artnoList, $secret, $result);

switch ($status) {
 case 0:
      echo "<p>Invoice with invoice number " . $invno . " has been activated.";
      echo "You can view the invoice at: <a href=\"" . $result["url"] . "\">" . $result["url"] . "</a>.</p>";
      if ($result["invno"] > 0) { 
	      echo "<p>Invoice has been split, new invoice: " . $result["invno"] . "</p>";
	}   
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
