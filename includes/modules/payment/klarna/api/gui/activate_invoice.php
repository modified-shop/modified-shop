<?php
include("klarna.php");
include("klarna_settings.php");
global $eid;
global $secret;
$invno = $_POST["invno"];

$status = activate_invoice($eid, $invno, $secret, $result);

switch ($status) {
 case 0:
      echo "<p>Invoice " . $invno . " has been activated.";
      echo "The invoice is available here: <a href=\"". $result ."\">". $result ."</a>.</p>";  
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
