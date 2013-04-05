<?php

require_once('../Klarna.php');

//Dependencies from http://phpxmlrpc.sourceforge.net/
require_once(DIR_WS_INCLUDES.'external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc');
require_once(DIR_WS_INCLUDES.'external/klarna/api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc');

/**
 * 1. Initialize and setup the Klarna instance.
 */

$k = new Klarna();

/** Configure the Klarna object using the setConfig() method. (Alternative 1) **/

//Later we define the settings in code, thus we don't save the config to a file in JSON format.
/*
KlarnaConfig::$store = false;

//Create the config object, since we aren't storing the file, we don't need to specify a URI.
$config = new KlarnaConfig($file = null);

//Define the values:
$config['eid'] = 0; //Merchant ID or Estore ID, an integer above 0.
$config['secret'] = "sharedSecret"; //The shared secret which accompanied your eid.
$config['country'] = KlarnaCountry::DE;
$config['language'] = KlarnaLanguage::DE;
$config['currency'] = KlarnaCurrency::EUR;
$config['mode'] = Klarna::BETA; //or Klarna::BETA, depending on which server your eid is associated with.

//Define pclass settings:
$config['pcStorage'] = 'json'; //Which storage module? can be json, xml or mysql.
$config['pcURI'] = '/srv/pclasses.json'; //Where the json file for the pclasses are stored.

//Should we use HTTPS?
$config['ssl']  = false;

//Should we error report/status report to Klarna?
$config['candice'] = true; //(set to false if your server doesn't support UDP)

//Do we want to see xmlrpc debugging information?
$config['xmlrpcDebug'] = null; //If this is defined to anything, it will debug.

//Do we want to see normal debug information?
$config['debug'] = null; //If this is defined to anything, it will debug.

//Set the config object.
$k->setConfig($config);
 */

/** Configure the Klarna object using the config() method. (Alternative 2) **/

//Specify the values:
/*
$k->config(
    $eid = 0,
    $secret = 'sharedSecret',
    $country = KlarnaCountry::DE,
    $language = KlarnaLanguage::DE,
    $currency = KlarnaCurrency::EUR,
    $mode = Klarna::BETA,
    $pcStorage = 'json',
    $pcURI = '/srv/pclasses.json',
    $ssl = true,
    $candice = true
);

Klarna::$xmlrpcDebug = false;
Klarna::$debug = false;
*/
/** Configure the Klarna object using the setConfig() method. (Alternative 3) **/

//KlarnaConfig::$store = true; //This is default to true, so this is just to be more detailed.

//Set the config which loads from file /srv/klarna.json:
$k->setConfig(new KlarnaConfig('/srv/klarna.json'));

/* The file would contain the following data to set the same information as the above alternatives:
{
  "eid": 0,
  "secret":"sharedSecret",
  "country": 81,
  "language": 28,
  "currency": 2,
  "mode":0,
  "pcStorage":"json",
  "pcURI":"\/srv\/pclasses.json",
  "ssl": 1,
  "candice": 1,
  "xmlrpcDebug":0,
  "debug":0
}
*/


/**
 * 2. Add the article(s), shipping and/or handling fee.
 */

//Here we add a normal product to our goods list.
$k->addArticle(
    $qty = 1, //Quantity
    $artNo = "MG200MMS", //Article number
    $title = "Matrox G200 MMS", //Article name/title
    $price = 299.99,
    $vat = 19, //19% VAT
    $discount = 0,
    $flags = KlarnaFlags::INC_VAT //Price is including VAT.
);

//Next we might want to add a shipment fee for the product
$k->addArticle(
    $qty = 1,
    $artNo = "",
    $title = "Shipping fee",
    $price = 4.5,
    $vat = 19,
    $discount = 0,
    $flags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_SHIPMENT //Price is including VAT and is shipment fee
);

//Lastly, we want to use an invoice/handling fee as well
$k->addArticle(
    $qty = 1,
    $artNo = "",
    $title = "Handling fee",
    $price = 1.5,
    $vat = 19,
    $discount = 0,
    $flags = KlarnaFlags::INC_VAT + KlarnaFlags::IS_HANDLING //Price is including VAT and is handling/invoice fee
);


/**
 * 3. Create and set the address(es).
 */

//Create the address object and specify the values.
$addr = new KlarnaAddr(
    $email = 'uno.eins@example.com',
    $telno = '', //We skip the normal land line phone, only one is needed.
    $cellno = '015 2211 3356',
    $fname = 'Uno',
    $lname = 'Eins',
    $careof = '',  //No care of, C/O.
    $street = 'Hellersbergstrasse', //For DE and NL specify street number in houseNo.
    $zip = '41460',
    $city = 'Neuss',
    $country = KlarnaCountry::DE,
    $houseNo = '14', //For DE and NL we need to specify houseNo.
    $houseExt = null //Only required for NL.
);

//There are also set/get methods to do the same thing, like:
$addr->setEmail('uno.eins@example.com');

//Next we tell the Klarna instance to use the address in the next order.
$k->setAddress(KlarnaFlags::IS_BILLING, $addr); //Billing / invoice address
$k->setAddress(KlarnaFlags::IS_SHIPPING, $addr); //Shipping / delivery address

/**
 * 4. Specify relevant information from your store. (OPTIONAL)
 */

//Set store specific information so you can e.g. search and associate invoices with order numbers.
$k->setEstoreInfo(
    $orderid1 = '175012', //Maybe the estore's order number/id.
    $orderid2 = '1999110234', //Could an order number from another system?
    $user = '' //Username, email or identifier for the user?
);

//If you don't have the order id available at this stage, you can later use the method updateOrderNo().

/**
 * 5. Set additional information. (OPTIONAL)
 */

/** Comment? **/

$k->setComment('A text string stored in the invoice commentary area.');

/** Session IDs? **/

/* If you've called this before the purchase, where the user enters his information,
   Klarna will be able to easier detect fraud or identify previous customer.
   Increasing your acceptance rate. */
echo $k->checkoutHTML();

/** Shipment type? **/

//Normal shipment is defaulted, delays the start of invoice expiration/due-date.
$k->setShipmentInfo('delay_adjust', KlarnaFlags::EXPRESS_SHIPMENT);


/**
 * 6. Invoke addTransaction and transmit the data.
 */

try {
    //Transmit all the specified data, from the steps above, to Klarna.
    $result = $k->addTransaction(
        $pno = '07071960', //Date of birth for DE.
        $gender = KlarnaFlags::MALE, //The customer is a male.
        $flags = KlarnaFlags::NO_FLAG, //No specific behaviour like RETURN_OCR or TEST_MODE.
        $pclass = KlarnaPClass::INVOICE //-1, notes that this is an invoice purchase, for part payment purchase you will have a pclass object on which you use getId().
    );

    //Check the order status
    if($result[1] == KlarnaFlags::PENDING) {
        /* The order is under manual review and will be accepted or denied at a later stage.
           Use cronjob with checkOrderStatus() or visit Klarna Online to check to see if the status has changed.
           You should still show it to the customer as it was accepted, to avoid further attempts to fraud. */
    }

    //Here we get the invoice number
    $invno = $result[0];

    //Order is complete, store it in a database.
}
catch(Exception $e) {
    //The purchase was denied or something went wrong, print the message:
    echo $e->getMessage() . " (#" . $e->getCode() . ")";
}
