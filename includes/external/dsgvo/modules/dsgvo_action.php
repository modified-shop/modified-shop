<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_SYSTEM_DSGVO_STATUS')
      && MODULE_SYSTEM_DSGVO_STATUS == 'true'
      && MODULE_SYSTEM_DSGVO_CONTENT != ''
      )
  {
    if (isset ($_GET['action']) && ($_GET['action'] == 'dsgvo')) {
      $content_group_array = explode(',', MODULE_SYSTEM_DSGVO_CONTENT);

      if (isset($_POST['dsgvo']) && is_array($_POST['dsgvo'])) {      
        foreach ($_POST['dsgvo'] as $content_group) {
          if (in_array((int)$content_group, $content_group_array) !== false) {
            xtc_db_query("DELETE FROM `customers_dsgvo` 
                                WHERE content_group = '".(int)$content_group."' 
                                  AND customers_id = '".(int)$_SESSION['customer_id']."'");
            $sql_data_array = array(
              'customers_id' => (int)$_SESSION['customer_id'],
              'content_group' => (int)$content_group,
              'date_confirmed' => 'now()',
            );
            xtc_db_perform('customers_dsgvo', $sql_data_array);
          
            unset($_SESSION['dsgvo'][(int)$content_group]);
          }
        }
        
        if (count($_SESSION['dsgvo']) < 1) {
          unset($_SESSION['dsgvo']);
          
          if (strpos(basename($PHP_SELF), 'checkout') !== false) {
            xtc_redirect(xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('action')), 'SSL')); 
          }
                    
          // redirect to last viewed page
          $cnt_pageview_history = count($_SESSION['tracking']['pageview_history']);
          if ($cnt_pageview_history > 1) {
            $redirect = $_SESSION['tracking']['pageview_history'][$cnt_pageview_history - 2];
            if ($_SESSION['old_customers_basket_cart'] === true) {
              unset($_SESSION['old_customers_basket_cart']);
              $messageStack->add_session('global', TEXT_SAVED_BASKET);
            }
            xtc_redirect(xtc_href_link(ltrim($redirect, '/'))); 
          }
      
          // redirect fallback
          if ($_SESSION['cart']->count_contents() > 0) {
            if ($_SESSION['old_customers_basket_cart'] === true) {
              unset($_SESSION['old_customers_basket_cart']);
              $messageStack->add_session('info_message_3', TEXT_SAVED_BASKET);
            }
            xtc_redirect(xtc_href_link(FILENAME_SHOPPING_CART),'NONSSL'); 
          } else {          
            xtc_redirect(xtc_href_link(FILENAME_DEFAULT),'NONSSL');           
          }          
        }
      }
      
      if (count($_SESSION['dsgvo']) > 0) {
        $messageStack->add('dsgvo', TEXT_DSGVO_ERROR);
      }
    }
  }
