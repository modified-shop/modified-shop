<?php
include("klarna.php");
include("klarna_settings.php");
$invno = $_POST["invno"];

$status = invoice_address($eid, $invno, $secret, $result);

if ($status == 0) {
    $addr = "";
    if (is_array($result) && sizeof($result) > 0) {
	for ($i = 0; $i < sizeof($result); $i++) {
	    $addr = $addr . "<br>" . $result[$i];
	}
    }	
    echo "<p>Address is: <br>" . $addr . ".";
} else {     
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}

?>
