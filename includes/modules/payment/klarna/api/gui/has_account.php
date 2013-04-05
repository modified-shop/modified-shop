<html>
<body>
<?php
//~ include "kreditor.php";
//~ include "shop_info.php";
global $eid;
global $secret;

$pno = $_GET["pno"];
$pno_enc = $_GET["pno_enc"];
$status = has_account($eid, $pno, $secret, $pno_enc, $result);

switch ($status) {
 case 0:
      echo "<p>Result: $result </p>";
      echo "<p><a href=\"javascript:back();\">Back</a></p>";      
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      echo "<p><a href=\"javascript:back();\">Back</a>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
      echo "<p><a href=\"javascript:back();\">Back</a>";
}
?>
</body>
</html>
