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
    <title>Valid Categories List</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
  </head>
  <body>
    <table width="550" class="tableBoxCenter collapse">
      <tr>
        <td class="pageHeading txta-c" colspan="2">Valid Categories List</td>
        </tr>
        <?php
          $coupon_query = xtc_db_query("SELECT restrict_to_categories
                                          FROM ".TABLE_COUPONS."
                                         WHERE coupon_id = '".(int)$_GET['cID']."'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon['restrict_to_categories'] = preg_replace("'[\r\n\s]+'", '', $coupon['restrict_to_categories']);
          
          $cat_ids = explode(",", $coupon['restrict_to_categories']);
          $cat_ids = array_unique($cat_ids);
          
          echo '<tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent">Category ID</td>
                  <td class="dataTableHeadingContent">Category Name</td>
                </tr>';
          for ($i = 0; $i < count($cat_ids); $i++) {
            $categories_query = xtc_db_query("SELECT * 
                                                FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                               WHERE categories_id = '".(int)$cat_ids[$i]."'
                                                 AND language_id = '".(int)$_SESSION['languages_id']."'");
            while ($categories = xtc_db_fetch_array($categories_query)) {
              echo '<tr class="dataTableRow">';
              echo '  <td class="dataTableContent">'.$categories['categories_id'].'</td>';
              echo '  <td class="dataTableContent">'.$categories['categories_name'].'</td>';
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