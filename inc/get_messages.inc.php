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
  $message  = encode_htmlspecialchars($_GET[$msg]); 
  $message .= isset($_GET[$info]) ? strip_tags($_GET[$info]) : '';
  return $message;
}
?>