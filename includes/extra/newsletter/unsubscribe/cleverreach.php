<?php    
  if (defined('MODULE_CLEVERREACH_STATUS') && MODULE_CLEVERREACH_STATUS == 'true') {
    $api = new SoapClient('http://api.cleverreach.com/soap/interface_v5.1.php?wsdl');
    $result = $api->receiverDelete(MODULE_CLEVERREACH_APIKEY, MODULE_CLEVERREACH_GROUP, $mail);  
  }
?>