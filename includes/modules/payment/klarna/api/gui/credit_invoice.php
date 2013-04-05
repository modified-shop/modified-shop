<html>
<body>
<?php
include("klarna.php");
include("klarna_settings.php");

$invno = $_POST["invno"];
$credno = $_POST["credno"];

$status = credit_invoice($eid, $invno, $credno, $secret, $result);

switch ($status) {
 case 0:
      echo "<p>Invoice" . $invno . " has been credited </p>";  
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
</body>
</html>
