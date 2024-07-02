<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (validproducts.php,v 0.01 2002/08/17); www.oscommerce.com

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org


   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


require('includes/application_top.php');
?>
<html>
  <head>
    <title>Valid Manufacturers List</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
  </head>
  <body>
    <table width="550" class="tableBoxCenter collapse">
      <tr>
        <td class="pageHeading txta-c" colspan="2">Valid Manufacturers List</td>
        </tr>
        <?php
          $coupon_query = xtc_db_query("SELECT restrict_to_manufacturers
                                          FROM ".TABLE_COUPONS."
                                         WHERE coupon_id = '".(int)$_GET['cID']."'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon['restrict_to_manufacturers'] = preg_replace("'[\r\n\s]+'", '', $coupon['restrict_to_manufacturers']);
          
          $manu_ids = explode(",", $coupon['restrict_to_manufacturers']);
          $manu_ids = array_unique($manu_ids);
          
          echo '<tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent">Manufacturers ID</td>
                  <td class="dataTableHeadingContent">Manufacturers Name</td>
                </tr>';
          for ($i = 0; $i < count($manu_ids); $i++) {
            $manufacturers_query = xtc_db_query("SELECT * 
                                                   FROM ".TABLE_MANUFACTURERS."
                                                  WHERE manufacturers_id = '".(int)$manu_ids[$i]."'");
            while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
              echo '<tr class="dataTableRow">';
              echo '  <td class="dataTableContent">'.$manufacturers['manufacturers_id'].'</td>\n';
              echo '  <td class="dataTableContent">'.$manufacturers['manufacturers_name'].'</td>\n';
              echo '</tr>';
            }
          }
        ?>
    </table>
    <br />
    <table width="550" border="0" cellspacing="1">
      <tr>
        <td align=middle><input type="button" value="Close Window" onclick="window.close()"></td>
      </tr>
    </table>
  </body>
</html>