<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_create_password.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

  function xtc_RandomString($length) {
    $chars = array( 'a', 'A', 'b', 'B', 'c', 'C', 'd', 'D', 'e', 'E', 'f', 'F', 'g', 'G', 'h', 'H', 'i', 'I', 'j', 'J',  'k', 'K', 'l', 'L', 'm', 'M', 'n','N', 'o', 'O', 'p', 'P', 'q', 'Q', 'r', 'R', 's', 'S', 't', 'T',  'u', 'U', 'v','V', 'w', 'W', 'x', 'X', 'y', 'Y', 'z', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');

    $max_chars = count($chars) - 1;
    srand( (double) microtime()*1000000);

    $rand_str = '';
    for($i=0;$i<$length;$i++) {
     $rand_str = ( $i == 0 ) ? $chars[rand(0, $max_chars)] : $rand_str . $chars[rand(0, $max_chars)];
    }
     return $rand_str;
  }

  function xtc_create_password($length) {
    $pass=xtc_RandomString($length); //DokuMan - 2011-02-10 - corrected typo $lenght -> $length
    return md5($pass);
  }
  
?>