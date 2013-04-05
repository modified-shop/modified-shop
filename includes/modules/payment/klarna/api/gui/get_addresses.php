<?php
include("klarna.php");
include("klarna_settings.php");
global $GA_NEW;
global $KRED_SE_PNO;

$pno = $_POST["pno"];
$status = get_addresses($eid, $pno, $secret, $KRED_SE_PNO, $GA_OLD, $result);

switch ($status) {
 case 0:
      echo "<p>Addresses for customer: " . $pno . "<br/>";
      foreach($result as $addr){
	foreach($addr as $line){
	  echo $line . "<br/>\n";
	}
	echo "<br/>\n";
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
