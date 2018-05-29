<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


// new error handling
/*
if (isset($error_exceptions) && is_array($error_exceptions) && count($error_exceptions) > 0) {
  echo '<div style="max-width:1000px; margin:20px auto; font-family: Verdana,Arial,sans-serif; font-size: 10px;">' . PHP_EOL;
  foreach ($error_exceptions as $error_name => $error_exception) {
    echo '<h2 style="color: #BE3232;">Exception '.$error_name.':</h2>' . PHP_EOL;
    echo implode('<div style="height:1px; border-top:1px dotted #000; margin:10px 0px;"></div>'.PHP_EOL, $error_exception);
  }
  echo '</div>';
}
*/

// close MySQL connection
session_write_close();
if (function_exists('xtc_db_close')) {
  xtc_db_close();
}
?>

</body>
</html>