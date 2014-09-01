<?php
include("klarna.php");
include("klarna_settings.php");

$no = $_POST["no"];
$result = "";
$status = reserve_ocr_nums($no, $eid, $secret, $KRED_ISO3166_SE, $result);

switch ($status) {
 case 0:
   echo "<p>Result: <br/>";
   foreach($result as $ocr){
     echo $ocr . "<br/>";
   }
   echo "</p>";  
   break;
 case -99:
   echo "<p>Internal error: <pre>" . $result . "</pre></p>";
   break;
 default:
   echo "<p>Error code: <em>" . $status . "</em></p>";
   echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
