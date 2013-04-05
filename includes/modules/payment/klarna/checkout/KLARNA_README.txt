
CHMOD the following files to CHMOD 0777 recursively:
 > catalog/tmp
 > catalog/klarna
 > catalog/images/klarna/campaign/
 > catalog/includes/classes/klarna/standardRegister/html/campaigns/
 
 
 *** OS COMMERCE 2.2RC2 ***
 Klarna does NOT support this version of OSCommerce, however this module does work on OSCommerce after some so called "Core hacks".
 You will have to add the following line:
 
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js" type="text/javascript"></script>
 
 To the following files, right beneath the <head> tag:
 LINE	FILE
 92: 	checkout_confirmation.php
 199: 	checkout_payment_address.php
 88: 	checkout_payment.php
 211: 	checkout_shipping_address.php
 156: 	checkout_shipping.php
 66: 	checkout_success.php