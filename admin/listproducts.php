<?php
  /* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (validproducts.php,v 0.01 2002/08/17); www.oscommerce.com
   (c) 2003 XT-Commerce (listproducts.php 1312 2005-10-18); www.xt-commerce.com

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

require (DIR_WS_INCLUDES.'head.php');
?>
</head>
<body>
    <table width="550" cellspacing="1">
      <tr>
        <td class="pageHeading" colspan="3">
          <?php echo TEXT_VALID_PRODUCTS_LIST;?>
        </td>
      </tr>
      <?php
      $coupon_get=xtc_db_query("select restrict_to_products from " . TABLE_COUPONS . " where coupon_id='".$_GET['cid']."'");
      $get_result=xtc_db_fetch_array($coupon_get);
      echo "<tr>
              <th class=\"dataTableHeadingContent\">".TEXT_VALID_PRODUCTS_ID."</th>
              <th class=\"dataTableHeadingContent\">".TEXT_VALID_PRODUCTS_NAME."</th>
              <th class=\"dataTableHeadingContent\">".TEXT_VALID_PRODUCTS_MODEL."</th>
            </tr>";
      $pr_ids = explode(",", $get_result['restrict_to_products']);  // Hetfield - 2009-08-18 - replaced deprecated function split with explode to be ready for PHP >= 5.3
      for ($i = 0; $i < count($pr_ids); $i++) {
        $result = xtc_db_query("SELECT * FROM ".TABLE_PRODUCTS." p, 
                                              ".TABLE_PRODUCTS_DESCRIPTION." pd 
                                        WHERE p.products_id = pd.products_id 
                                          AND pd.language_id = '" . $_SESSION['languages_id'] . "'
                                          AND p.products_id = '" . $pr_ids[$i] . "'");
        if ($row = xtc_db_fetch_array($result)) {
          echo "<tr><td class=\"dataTableHeadingContent\">".$row["products_id"]."</td>\n";
          echo "<td class=\"dataTableHeadingContent\">".$row["products_name"]."</td>\n";
          echo "<td class=\"dataTableHeadingContent\">".$row["products_model"]."</td>\n";
          echo "</tr>\n";
        }
      }
      ?>
    </table>
    <br />
    <table width="550" border="0" cellspacing="1">
      <tr>
        <td align=middle><input type="button" value="<?php echo BUTTON_CLOSE_WINDOW;?>" onclick="window.close()"></td>
      </tr>
    </table>
  </body>
</html>