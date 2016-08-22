<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2005 Daniel Morris dan@rootcube.com
   contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, 
                 Chris, Tobin, Andrew Eddie.
   Modification: Louis Landry
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function get_message($msg, $info = 'add_info') {
  $message  = encode_htmlspecialchars(encode_utf8(urldecode($_GET[$msg]), 'ISO-8859-15')); 
  $message .= isset($_GET[$info]) ? strip_tags($_GET[$info]) : '';
  return $message;
}
?>