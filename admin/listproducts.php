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
    <title>Valid Products List</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
  </head>
  <body>
    <table width="550" class="tableBoxCenter collapse">
      <tr>
        <td class="pageHeading txta-c" colspan="3">Valid Products List</td>
        </tr>
        <?php
          $coupon_query = xtc_db_query("SELECT restrict_to_products
                                          FROM " . TABLE_COUPONS . "
                                         WHERE coupon_id='".(int)$_GET['cID']."'");
          $coupon = xtc_db_fetch_array($coupon_query);
          $coupon['restrict_to_products'] = preg_replace("'[\r\n\s]+'", '', $coupon['restrict_to_products']);
          
          $pr_ids = explode(",", $coupon['restrict_to_products']);
          $pr_ids = array_unique($pr_ids);
          
          echo '<tr class="dataTableHeadingRow">
                  <td class="dataTableHeadingContent">Product ID</td>
                  <td class="dataTableHeadingContent">Product Name</td>
                  <td class="dataTableHeadingContent">Product Model</td>
                </tr>';
          for ($i = 0; $i < count($pr_ids); $i++) {
            $products_query = xtc_db_query("SELECT * 
                                              FROM ".TABLE_PRODUCTS." p
                                              JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                                   ON p.products_id = pd.products_id 
                                                      AND pd.language_id = '".(int)$_SESSION['languages_id']."'
                                             WHERE p.products_id = '".(int)$pr_ids[$i]."'");
            while ($products = xtc_db_fetch_array($products_query)) {
              echo '<tr class="dataTableRow">';
              echo '  <td class="dataTableContent">'.$products['products_id'].'</td>';
              echo '  <td class="dataTableContent">'.$products['products_name'].'</td>';
              echo '  <td class="dataTableContent">'.$products['products_model'].'</td>';
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