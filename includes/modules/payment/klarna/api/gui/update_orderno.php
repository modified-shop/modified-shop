<?php
include("klarna_settings.php");

$config = new KlarnaConfig(KCONFIG);

$klarna = new KlarnaExperimental();
$klarna->config($eid, $secret, null, null, null, $mode, $pcStorage = 'json', $pcURI = 'gui-pclasses.json');

try {
    //Returns the invoice number again, no need to handle it.
    $klarna->updateOrderNo(@$_POST["invno"], @$_POST["newOrderno"]);
}
catch(Exception $e) {
    echo $e->getMessage() . " # " . $result;
}
echo "Order number updated on invoice #$_POST[invno].";

?>
