<?php
include("klarna.php");
include("klarna_settings.php");

$cur = $_POST["currency"];
$result = "";
$status = fetch_pclasses($eid, intval($cur), $secret, 209, 138, &$result);

switch ($status) {
 case 0:
   echo "<table cellspacing=\"5\"><tr><th>ID</th><th>Description</th><th>Months</th><th>Interest</th><th>Start fee</th><th>Invoice fee</th><th>Min. sum</th></tr>";
   foreach($result as $ocr){
     echo "<tr><td>" . $ocr[0] . "</td>" . "<td>" . $ocr[1] . "</td>" . "<td>" . $ocr[2] . "</td>" . "<td>" . $ocr[3] . "</td>" . "<td>" . $ocr[4] . "</td>" . "<td>" . $ocr[5] . "</td>" . "<td>" . $ocr[6] . "</td>" . "</tr>";
   }
   echo "</table>";
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
