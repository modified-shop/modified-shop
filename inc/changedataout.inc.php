<?php
/*------------------------------------------------------------------------------
  $Id: changedataout.inc.php 899 2005-04-29 02:40:57Z hhgag $

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
require_once(DIR_FS_INC . 'base64todec.inc.php');

function changedataout($cipher_data,$key){

   // decode cipher data with key using xoft encryption */

   $m=0;
   $all_bin_chars="";

   for($i=0;$i<strlen($cipher_data);$i++){
	$c=substr($cipher_data,$i,1);             // c = ciphertext
	$decimal_value=base64todec($c);           //convert to decimal value

	$decimal_value=($decimal_value - $m) / 4; //substract by m where m=0,1,2,or 3 then divide by 4

	$four_bit=decbin($decimal_value);

	while(strlen($four_bit)<4){
		$four_bit="0".$four_bit;
	}

	$all_bin_chars=$all_bin_chars.$four_bit;
	$m++;

	if($m>3){
		$m=0;
	}
   }

   $key_length=0;
   $plain_data="";

   for($j=0;$j<strlen($all_bin_chars);$j=$j+8){
	$c=substr($all_bin_chars,$j,8);
	$k=substr($key,$key_length,1);

	$dec_chars=bindec($c);
	$dec_chars=$dec_chars - strlen($key);
	$c=chr($dec_chars);
	$key_length++;

	if($key_length>=strlen($key)){
		$key_length=0;
	}

	$dec_chars=ord($c)^ord($k);
	$p=chr($dec_chars);
	$plain_data=$plain_data.$p;
   }

   return $plain_data;
}
?>
