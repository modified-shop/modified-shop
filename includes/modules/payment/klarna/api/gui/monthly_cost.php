<?php
include("klarna.php");
include("klarna_settings.php");

$sum = $_POST["sum"];
$sum = $sum *100;
$months = 24;
$monthsfee = 2900;
$rate = 19.5;
$flags = 0;

if($_POST["currency"] == "sek") {
	$currency = $KRED_SEK;
	$cur = "SEK";
} elseif($_POST["currency"] == "nok") {
	$currency = $KRED_NOK;
	$cur = "NOK";
} elseif($_POST["currency"] == "eur") {
	$currency = $KRED_EUR;
	$cur = "EUR";
} elseif($_POST["currency"] == "dkk") {
	$currency = $KRED_DKK;
	$cur = "DKK";
}

$status = monthly_cost($sum, $rate, $months, $monthsfee, $flags, $currency, $result);

switch ($status) {
 case 0:
      echo $result /100 . $cur;
      break;
 case -99:
      echo "<p>Internal error: <pre>" . $result . "</pre></p>";
      break;
 default:
      echo "<p>Error code: <em>" . $status . "</em></p>";
      echo "<p>Reason: <em>" . $result . "</em></p>";
}
?>
