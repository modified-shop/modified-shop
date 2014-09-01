<?php
/*------------------------------------------------------------------------------
  $Id: changedatain.inc.php 899 2005-04-29 02:40:57Z hhgag $

  XTC-CC - Contribution for XT-Commerce http://www.xt-commerce.com
  modified by http://www.netz-designer.de

  Copyright (c) 2003 netz-designer
  -----------------------------------------------------------------------------
  based on:
  credit card encryption functions for the catalog module

  BMC 2003 for the CC CVV Module

  (c) Mainframes 2003 http://www.mainframes.co.uk
------------------------------------------------------------------------------*/

// include needed functions
require_once(DIR_FS_INC . 'dectobase64.inc.php');

function changedatain($plain_data,$key){

   // encode plain data with key using xoft encryption

   $key_length=0; //key length counter
   $all_bin_chars="";
   $cipher_data="";

   for($i=0;$i<strlen($plain_data);$i++){
	$p=substr($plain_data,$i,1);   // p = plaintext
	$k=substr($key,$key_length,1); // k = key
	$key_length++;

	if($key_length>=strlen($key)){
		$key_length=0;
	}

	$dec_chars=ord($p)^ord($k);
	$dec_chars=$dec_chars + strlen($key);
	$bin_chars=decbin($dec_chars);

	while(strlen($bin_chars)<8){
		$bin_chars="0".$bin_chars;
	}

	$all_bin_chars=$all_bin_chars.$bin_chars;

   }

   $m=0;

   for($j=0;$j<strlen($all_bin_chars);$j=$j+4){
	$four_bit=substr($all_bin_chars,$j,4);     // split 8 bit to 4 bit
	$four_bit_dec=bindec($four_bit);

	$decimal_value=$four_bit_dec * 4 + $m;     //multiply by 4 plus m where m=0,1,2, or 3

	$base64_value=dectobase64($decimal_value); //convert to base64 value
	$cipher_data=$cipher_data.$base64_value;
	$m++;

	if($m>3){
		$m=0;
	}
   }

   return $cipher_data;
}
?>
