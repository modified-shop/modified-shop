<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_random_charcode.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2004 XT-Commerce
   -----------------------------------------------------------------------------------------
   by Guido Winger for XT:Commerce

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
  // build to generate a random charcode
  function xtc_random_charcode($length) {
    //BOF - Dokuman - 2009-09-04: Captchas only uppercase and do not use chars like "O,0,1,7,I,J"
    //$arraysize = 34; 
    //$chars = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9');
    $arraysize = 28; 
    $chars = array('A','B','C','D','E','F','G','H','K','M','N','P','Q','R','S','T','U','V','W','X','Y','Z','2','3','4','5','6','8','9');
    //EOF - Dokuman - 2009-09-04: Captchas only uppercase and do not use chars like "O,0,1,I,J"

    $code = '';
    for ($i = 1; $i <= $length; $i++) {
    $j = floor(xtc_rand(0,$arraysize));
    $code .= $chars[$j];
    }
    return  $code;
  }
?>