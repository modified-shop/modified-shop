<?php
/* -----------------------------------------------------------------------------------------
   $Id: admin.php 5518 2013-09-05 13:02:47Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce (admin.php, v1 2002/08/28 02:14:35); www.oscommerce.com
   (c) 2003 nextcommerce (admin.php,v 1.12 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (admin.php,v 1.12 2006/10/03); www.xtcommerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include smarty
include(DIR_FS_BOXES_INC . 'smarty_default.php');

  $admin_link = '';
  $box_content = '';
  $orders_contents = '';

  $orders_status_validating_query = xtc_db_query("SELECT orders_status FROM ".TABLE_ORDERS ." WHERE orders_status ='0'");
  $orders_status_validating = xtc_db_num_rows($orders_status_validating_query);
  $orders_contents .='<a href="'.xtc_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status=0').'">'.TEXT_VALIDATING.'</a>: '.$orders_status_validating.'<br />';

  $orders_status_query = xtc_db_query("SELECT orders_status_name, orders_status_id FROM ".TABLE_ORDERS_STATUS." WHERE language_id = '".(int)$_SESSION['languages_id']."' ORDER BY sort_order");
  while ($orders_status = xtc_db_fetch_array($orders_status_query)) {
    $orders_pending_query = xtc_db_query("SELECT count(*) AS count FROM ".TABLE_ORDERS." WHERE orders_status = '".$orders_status['orders_status_id']."'");
    $orders_pending = xtc_db_fetch_array($orders_pending_query);
    $orders_contents .= '<a href="'.xtc_href_link_admin(FILENAME_ORDERS, 'selected_box=customers&status='.$orders_status['orders_status_id']).'">'.$orders_status['orders_status_name'].'</a>: '.$orders_pending['count'].'<br />';
  }
  $orders_contents = substr($orders_contents, 0, -6);

  $customers_query = xtc_db_query("select count(*) as count from " . TABLE_CUSTOMERS);
  $customers = xtc_db_fetch_array($customers_query);
  $products_query = xtc_db_query("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '1'");
  $products = xtc_db_fetch_array($products_query);
  $reviews_query = xtc_db_query("select count(*) as count from " . TABLE_REVIEWS);
  $reviews = xtc_db_fetch_array($reviews_query);
  $admin_status_query = xtc_db_query("SELECT customers_status_name
                                        FROM " . TABLE_CUSTOMERS_STATUS . "
                                       WHERE customers_status_id = '" . DEFAULT_CUSTOMERS_STATUS_ID_ADMIN . "' 
                                         AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
  $admin_status = xtc_db_fetch_array($admin_status_query);
  $admin_image = '<a href="'.xtc_href_link_admin(FILENAME_START,'').'">'.xtc_image_button('button_admin.gif', IMAGE_BUTTON_ADMIN).'</a>';
  if ($product->isProduct() === true) {
    $admin_link='<a href="'.xtc_href_link_admin(FILENAME_EDIT_PRODUCTS, 'cPath='.$cPath.'&pID='.$product->data['products_id']).'&action=new_product'.'">'.xtc_image_button('edit_product.gif', IMAGE_BUTTON_PRODUCT_EDIT).'</a>';
  } elseif (isset($_GET['coID'])) {
    $admin_link = '<a href="'.xtc_href_link_admin((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'content_manager.php', 'action=edit&coID='.(int)$_GET['coID']).'">'.xtc_image_button('edit_content.gif', IMAGE_BUTTON_CONTENT_EDIT).'</a>';
  }
  
  $box_content = '<strong>' . BOX_TITLE_STATISTICS . '</strong><br />' . 
                 $orders_contents . '<br />' .
                 BOX_ENTRY_CUSTOMERS . ' ' . $customers['count'] . '<br />' .
                 BOX_ENTRY_PRODUCTS . ' ' . $products['count'] . '<br />' .
                 BOX_ENTRY_REVIEWS . ' ' . $reviews['count'] .'<br />' .
                 BOX_LOGINBOX_STATUS . ' ' . $admin_status['customers_status_name'] .'<br />' .
                 $admin_image . '<br />' .
                 $admin_link;

  $box_smarty->assign('BOX_CONTENT', $box_content);

  $box_smarty->caching = 0;
  $box_admin= $box_smarty->fetch(CURRENT_TEMPLATE.'/boxes/box_admin.html');
  $smarty->assign('box_ADMIN',$box_admin);
?>