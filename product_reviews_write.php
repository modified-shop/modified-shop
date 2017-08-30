<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_reviews_write.php,v 1.51 2003/02/13); www.oscommerce.com
   (c) 2003   nextcommerce (product_reviews_write.php,v 1.13 2003/08/1); www.nextcommerce.org
   (c) 2006 XT-Commerce (product_reviews_write.php 1101 2005-07-24)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

// captcha
$use_captcha = array();
if (defined('MODULE_CAPTCHA_ACTIVE')) {
  $use_captcha = explode(',', MODULE_CAPTCHA_ACTIVE);
}
defined('MODULE_CAPTCHA_CODE_LENGTH') or define('MODULE_CAPTCHA_CODE_LENGTH', 6);
defined('MODULE_CAPTCHA_LOGGED_IN') or define('MODULE_CAPTCHA_LOGGED_IN', 'True');

// create smarty elements
$smarty = new Smarty;

// include boxes
require (DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/source/boxes.php');

foreach(auto_include(DIR_FS_CATALOG.'includes/extra/modules/product_reviews_write/','php') as $file) require ($file);

if ($_SESSION['customers_status']['customers_status_write_reviews'] == 0) {
  if (is_object($product) && $product->isProduct() === true) {
    xtc_redirect(xtc_href_link(FILENAME_LOGIN, 'review_prod_id=' .(int)$product->data['products_id'], 'SSL'));
  } else {
    xtc_redirect(xtc_href_link(FILENAME_DEFAULT));
  }
}

$review = '';
$rating = '';
if (isset ($_GET['action']) && $_GET['action'] == 'process') {
  if (is_object($product) && $product->isProduct() === true) { // We got to the process but it is an illegal product, don't write
    
    $review = xtc_db_prepare_input($_POST['review']);
    $rating = xtc_db_prepare_input($_POST['rating']);
    $author = xtc_db_prepare_input($_POST['author']);
    
    $error = false;
    if (strlen($review) < REVIEW_TEXT_MIN_LENGTH) {
      $messageStack->add('product_reviews_write', ERROR_REVIEW_TEXT);
      $error = true;
    }
    if (!isset($_POST['rating'])) {
      $messageStack->add('product_reviews_write', ERROR_REVIEW_RATING);
      $error = true;
    }
    if (strlen($author) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $messageStack->add('product_reviews_write', ERROR_REVIEW_AUTHOR);
      $error = true;
    }
    if (in_array('reviews', $use_captcha) && (!isset($_SESSION['customer_id']) || MODULE_CAPTCHA_LOGGED_IN == 'True')) {
      if (!isset($_SESSION['vvcode'])
          || !isset($_POST['vvcode'])
          || $_SESSION['vvcode'] == ''
          || $_POST['vvcode'] == ''
          || strtoupper($_POST['vvcode']) != $_SESSION['vvcode']
          ) 
      {
        $messageStack->add('product_reviews_write', strip_tags(ERROR_VVCODE, '<b><strong>'));
        $error = true;
      }
      unset($_SESSION['vvcode']);
    }
    
    if ($error === false) {
      $customer = xtc_db_query("SELECT customers_firstname,
                                       customers_lastname
                                  FROM ".TABLE_CUSTOMERS."
                                 WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
      $customer_values = xtc_db_fetch_array($customer);

      $sql_data_array = array('products_id' => $product->data['products_id'],
                              'customers_id' => (int) $_SESSION['customer_id'],
                              'customers_name' => $author,
                              'reviews_rating' => $rating,
                              'reviews_status' => $_SESSION['customers_status']['customers_status_reviews_status'],
                              'date_added' =>  'now()'
                              );
      xtc_db_perform(TABLE_REVIEWS,$sql_data_array);
      $insert_id = xtc_db_insert_id();

      $sql_data_array = array('reviews_id' => $insert_id,
                              'languages_id' => (int) $_SESSION['languages_id'],
                              'reviews_text' => $review
                              );
      xtc_db_perform(TABLE_REVIEWS_DESCRIPTION,$sql_data_array);
      
      if ($_SESSION['customers_status']['customers_status_reviews_status'] != '1') {
        $messageStack->add_session('product_reviews', PRODUCT_REVIEWS_SUCCESS_WAITING);
      } else {
        $messageStack->add_session('product_reviews', PRODUCT_REVIEWS_SUCCESS);
      }
      
      xtc_redirect(xtc_href_link(FILENAME_PRODUCT_REVIEWS, $_POST['get_params']));
    }
  }
}

$breadcrumb->add(NAVBAR_TITLE_REVIEWS_WRITE, xtc_href_link(FILENAME_PRODUCT_REVIEWS, xtc_get_all_get_params()));

require (DIR_WS_INCLUDES.'header.php');

if ($product->isProduct() === false) {
  $smarty->assign('error', ERROR_INVALID_PRODUCT);
  $smarty->assign('no_product', true);
} else {
  if ($messageStack->size('product_reviews_write') > 0) {
    $smarty->assign('error', $messageStack->output('product_reviews_write'));
  }
  if (!isset($author)) {
    if(isset($_SESSION['customer_id'])) {
      $customer_info_query = xtc_db_query("SELECT customers_firstname,
                                                  customers_lastname
                                             FROM ".TABLE_CUSTOMERS."
                                            WHERE customers_id = '".(int) $_SESSION['customer_id']."'");
      if (xtc_db_num_rows($customer_info_query) > 0) {
        $customer_info = xtc_db_fetch_array($customer_info_query);
        $author = $customer_info['customers_firstname'].' '.$customer_info['customers_lastname'][0].'.';
      }
    }
  }
  if (in_array('reviews', $use_captcha) && (!isset($_SESSION['customer_id']) || MODULE_CAPTCHA_LOGGED_IN == 'True')) {
    $smarty->assign('VVIMG', '<img src="'.xtc_href_link(FILENAME_DISPLAY_VVCODES, '', 'SSL') .'" alt="Captcha" />');
    $smarty->assign('INPUT_CODE', xtc_draw_input_field('vvcode', '', 'style="width:240px;" size="'.MODULE_CAPTCHA_CODE_LENGTH.'" maxlength="'.MODULE_CAPTCHA_CODE_LENGTH.'"', 'text', false));
  }
  $link = 'javascript:history.back(1)';
  if (!isset($_SERVER['HTTP_REFERER']) 
      || strpos($_SERVER['HTTP_REFERER'], HTTP_SERVER) === false
      )
  {
    $link = xtc_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product->data['products_id'], 'NONSSL');    
  } 
    
  $smarty->assign('PRODUCTS_NAME', $product->data['products_name']);
  $smarty->assign('INPUT_AUTHOR', xtc_draw_input_field('author', $author, 'style="width:235px;"'));
  $smarty->assign('INPUT_TEXT', xtc_draw_textarea_field('review', 'soft', '60', '15', $review));
  $smarty->assign('FORM_ACTION', xtc_draw_form('product_reviews_write', xtc_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, xtc_get_all_get_params(array('action')).'action=process'), 'post', 'onSubmit="return check_form_review();"').xtc_draw_hidden_field('get_params', xtc_get_all_get_params(array('action'))));
  $smarty->assign('BUTTON_BACK', '<a href="'.$link.'">'.xtc_image_button('button_back.gif', IMAGE_BUTTON_BACK).'</a>');
  $smarty->assign('BUTTON_SUBMIT', xtc_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE));
  $smarty->assign('FORM_END', '</form>');

  $input_rating_array = array(
    xtc_draw_radio_field('rating', '1', (($rating == '1') ? true : false)),
    xtc_draw_radio_field('rating', '2', (($rating == '2') ? true : false)),
    xtc_draw_radio_field('rating', '3', (($rating == '3') ? true : false)),
    xtc_draw_radio_field('rating', '4', (($rating == '4') ? true : false)),
    xtc_draw_radio_field('rating', '5', (($rating == '5') ? true : false)),
  );
  $smarty->assign('INPUT_RATING', implode(' ', $input_rating_array));
  $smarty->assign('INPUT_RATING_ARRAY', $input_rating_array);
}

$smarty->assign('language', $_SESSION['language']);
$main_content = $smarty->fetch(CURRENT_TEMPLATE.'/module/product_reviews_write.html');

$smarty->assign('main_content', $main_content);
$smarty->caching = 0;
if (!defined('RM'))
  $smarty->load_filter('output', 'note');
$smarty->display(CURRENT_TEMPLATE.'/index.html');
include ('includes/application_bottom.php');
?>