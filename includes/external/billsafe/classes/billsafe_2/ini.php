<?php
/* -----------------------------------------------------------------------------------------
   $Id: ini.php 4307 2013-01-14 07:38:50Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = ini.php
* location = /includes/external/billsafe/classes/billsafe_2 // DokuMan - 2012-06-19 - move billsafe to external directory
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 PayPal SE and Bernd Blazynski
* @license GPLv2
*/

//isLiveMode MUST be set to false for testing and debugging!
$ini['isLiveMode'] = false;
//Enter your API credentials provided by BillSAFE:
$ini['merchantId'] = '';
$ini['merchantLicenseSandbox'] = '';
$ini['merchantLicenseLive'] = '';
//geändert Ben BillSAFE
$ini['applicationSignature'] = '1406801e3073bc303cf0d3fff0fb85f7';
//$ini['applicationSignature'] = '3acef6967aac4c3d460242fe3a2a8468';
$ini['applicationVersion'] = 'modified-shop-2.00_v2.8 2014-01-04';
//Set this to true if your data is utf-8 encoded.
//Set this to false if your data is latin-1 encoded.
//The encoding of the response object will be affected accordingly.
$ini['isUtf8Mode'] = true;
//API version
$ini['apiVersion'] = 211;
//Payment Gateway version
$ini['gatewayVersion'] = 200;
?>
