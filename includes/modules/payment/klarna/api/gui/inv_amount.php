<?php
include("klarna.php");
include("klarna_settings.php");
$invno = $_POST["invno"];

$status = invoice_amount($eid, $invno, $secret, $result);

if ($status == 0) {
    $addr = "";
    echo "<p>The amount of invoice $invno is $result</p>";
} else {     
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
