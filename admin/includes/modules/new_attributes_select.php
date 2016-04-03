<?php
/* --------------------------------------------------------------
   $Id: new_attributes_select.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_select.php); www.oscommerce.com
   (c) 2003 nextcommerce (new_attributes_select.php,v 1.9 2003/08/21); www.nextcommerce.org
   (c) 2006 xt-commerce (new_attributes_select.php 901 2005-04-29); www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b      Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   copy attributes                          Autor: Hubi | http://www.netz-designer.de

   Released under the GNU General Public License
   --------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
$adminImages = DIR_WS_CATALOG . "lang/". $_SESSION['language'] ."/admin/images/buttons/";

$product_select = $products_copy = array();
                               
$dbQuery = xtc_db_query(
    "SELECT p.products_model,
            p.products_id,
            pd.products_name
       FROM ".TABLE_PRODUCTS."  p,
            ".TABLE_PRODUCTS_DESCRIPTION."  pd
      WHERE p.products_id != '0'
        AND p.products_id = pd.products_id
        AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'
   ORDER BY pd.products_name ASC
   ");

if (xtc_db_num_rows($dbQuery)) {
  while ($line = xtc_db_fetch_array($dbQuery)) {
    $product_select[] = array('id'=> $line['products_id'], 'text'=> $line['products_name'] .' [' .$line['products_model'] . ']' ); 
  }
} 

$dbQuery = xtc_db_query(
    "SELECT p.products_model,
            p.products_id,
            pd.products_name
       FROM ".TABLE_PRODUCTS."  p, 
            ".TABLE_PRODUCTS_DESCRIPTION."  pd, 
            ".TABLE_PRODUCTS_ATTRIBUTES." pa 
      WHERE p.products_id != '0'
        AND p.products_id = pd.products_id
        AND pa.products_id = pd.products_id
        AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' 
   GROUP BY pd.products_id 
   ORDER BY pd.products_name ASC
  ");
if (xtc_db_num_rows($dbQuery)) {
  $products_copy[] = array('id'=> '0', 'text'=> TEXT_SELECT );
  while ($line = xtc_db_fetch_array($dbQuery)) {
    $products_copy[] = array('id'=> $line['products_id'], 'text'=> $line['products_name'] .' [' .$line['products_model'] . ']' ); 
  }
} 
?>
<tr>
  <td>
    <div class="pageHeading pdg2"><?php echo $pageTitle; ?></div>
    <?php 
      echo xtc_draw_form('SELECT_PRODUCT', FILENAME_NEW_ATTRIBUTES, '', 'post').PHP_EOL;
      echo '<input type="hidden" name="action" value="edit">'.PHP_EOL;
      echo '<div class="main pdg2"><br /><strong>'.SELECT_PRODUCT.'</strong><br /></div>'.PHP_EOL;
      echo '<div class="main pdg2">'.PHP_EOL;
      
      echo '<div class="main pdg2">'.PHP_EOL;
      if (count($product_select) > 0) {
        echo xtc_draw_pull_down_menu('current_product_id', $product_select).PHP_EOL;
      } else {
        echo 'You have no products at this time.'.PHP_EOL;
      }
      echo '</div>'.PHP_EOL;

      echo '<div class="main pdg2">'. xtc_button(BUTTON_EDIT).'</div>'.PHP_EOL;
      // start change for Attribute Copy

      echo '<div class="main pdg2"><br /><strong>'.SELECT_COPY.'</strong><br /></div>'.PHP_EOL;

      echo '<div class="main pdg2">'.PHP_EOL;
      if (count($products_copy) > 0) {
        echo xtc_draw_pull_down_menu('copy_product_id', $products_copy).PHP_EOL;
      } else {
        echo 'No products to copy attributes from'.PHP_EOL;
      }
      echo '</div>'.PHP_EOL;
      echo '<div class="main pdg2">'. xtc_button(BUTTON_EDIT).'</div>'.PHP_EOL;
    ?>

    </form>
  </td>
</tr>