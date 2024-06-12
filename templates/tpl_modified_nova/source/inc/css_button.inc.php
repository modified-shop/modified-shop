<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]

   Released under the GNU General Public License
   --------------------------------------------------------------*/

require_once(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/lang/buttons_'.$_SESSION['language'].'.php');

function css_button($image, $alt, $parameters = '', $submit = false) {
  
  // default class for color 1
  $default_class = 'cssButtonColor1';
  
  $button_array = array(
    
    // color 1
    'button_back' => array(),
    'button_login_small' => array(),
    'button_search' => array(),
    'small_view' => array(),
    'button_view' => array(),
    'button_update_cart' => array(),
    'button_write_review' => array(),
    'button_shipping_options' => array(),
    'button_reviews' => array(),
    'button_redeem' => array(),
    'button_download' => array(),
    'button_edit_account' => array(),
    'button_change_address' => array(),
    'button_print' => array(),
    'print' => array(),
    'button_continue_shopping' => array(),
    'button_history' => array(),
    'button_product_more' => array(),
    'button_in_wishlist' => array(),
    'button_confirm' => array(),
    'button_continue_account' => array(),
    'button_continue_guest' => array(),
    'button_box_warenkorb' => array(),
    'button_address_book' => array(),
    'edit_content' => array(),
    'edit_product' => array(),
    'button_admin' => array(),
    'button_quick_find_head' => array(),
    'wishlist_del' => array(),
    'button_results' => array(),
    'button_login_newsletter' => array(),


    // color 2
    'button_login' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_send' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_add_address' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_delete' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_update' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_save' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_finish' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_checkout' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_in_cart' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_confirm_order' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_checkout_step2' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_checkout_step3' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_box_checkout' => array(
      'class' => 'cssButtonColor2',
    ),
    'small_cart' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_buy_now' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_continue' => array(
      'class' => 'cssButtonColor2',
    ),
    'button_add_quick' => array(
      'class' => 'cssButtonColor2',
    ),
    
    
    // color 3
    'button_quick_find' => array(
      'class' => 'cssButtonColor3',
    ),
    
    
    // color 4
    'button_checkout_express' => array(
      'class' => 'cssButtonColor4',
    ),
    'small_express' => array(
      'class' => 'cssButtonColor4',
    ),
    
    
    // color 5
    'cart_del' => array(
      'class' => 'cssButtonColor5',
    ),
    'icon_cart' => array(
      'class' => 'cssButtonColor5',
    ),


    // color 6
    'epaypal_de' => array(
      'class' => 'cssButtonColor6',
    ),
    'epaypal_en' => array(
      'class' => 'cssButtonColor6',
    ),

    
    // color 7
    'small_continue' => array(
      'class' => 'cssButtonColor7',
    ),
    'small_edit' => array(
      'class' => 'cssButtonColor7',
    ),
    'small_delete' => array(
      'class' => 'cssButtonColor7',
    ),    
    

  );


  // index
  $image_idx = substr(basename($image), 0, strrpos(basename($image), '.'));
  

  // default
  if (!array_key_exists($image_idx, $button_array)) {
    if ($submit === true) {
      return xtc_image_submit($image, $alt, $parameters, false);
    }
    return xtc_image_button($image, $alt, $parameters, false);
  }


  // parameters
  if (xtc_not_null($parameters)) {
    $parameters = ' '.$parameters;
  }


  // button
  $button = '<span class="cssButton '.((isset($button_array[$image_idx]['class'])) ? $button_array[$image_idx]['class'] : $default_class).'"'.(($submit !== true) ? ' title="'.strip_tags((defined('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE') && constant('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE') != '') ? constant('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE') : $alt).'"' : '').'>';
  if (defined('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_LEFT')
      && constant('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_LEFT') != ''
      )
  {
    $button .= '<span class="cssButtonIcon">'.constant('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_LEFT').'</span>';
  }

  if (!defined('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT') || constant('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT') != '') {
    $button .= '<span class="cssButtonText" '.(($submit !== true) ? $parameters : '').'>'.((defined('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT')) ? constant('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT') : $alt).'</span>';
  }

  if ($submit === true) {
    $button .= '<button type="submit" class="cssButtonText"'.(((defined('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE') && constant('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE')) || $alt != '') ? ' title="'.strip_tags((defined('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE')) ? constant('CSS_IMAGE_'.strtoupper($image_idx).'_TITLE') : $alt).'"' : '').$parameters.'>'.((defined('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT')) ? constant('CSS_IMAGE_'.strtoupper($image_idx).'_TEXT') : $alt).'</button>';
  }

  if (defined('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_RIGHT')
      && constant('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_RIGHT') != ''
      )
  {
    $button .= '<span class="cssButtonIcon">'.constant('CSS_IMAGE_'.strtoupper($image_idx).'_ICON_RIGHT').'</span>';
  }
  
  $button .= '</span>';

  return $button;
}
