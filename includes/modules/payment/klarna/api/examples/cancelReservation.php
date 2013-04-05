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
 * 2. Cancelling a reservation
 */

//Here you enter the reservation number you got from reserveAmount():
$rno = $_GET['rno'];

try {
    $k->cancelReservation($rno);

    //Reservation cancelled, proceed accordingly.
}
catch(Exception $e) {
    //Something went wrong or the reservation doesn't exist.
    echo $e->getMessage() . " (#" . $e->getCode() . ")";
}
