<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_EXTERNAL.'paypal/classes/PayPalPaymentV2.php');

  function set_paypal_contact() {
    $paypal = new PayPalPaymentV2('paypalexpress');
    if (!$paypal->is_valid_ajax_token()) {
      return array('success' => false);
    }

    $contacts = array();
    foreach (array('shippingContact' => 'shipping', 'billingContact' => 'billing') as $post_key => $contact_type) {
      if (!isset($_POST[$post_key])) {
        continue;
      }

      if (!is_string($_POST[$post_key]) || strlen($_POST[$post_key]) > 16384) {
        return array('success' => false);
      }

      $contact = json_decode($_POST[$post_key], true, 8);
      if (json_last_error() !== JSON_ERROR_NONE || !is_array($contact)) {
        return array('success' => false);
      }

      $entries = 0;
      $validate_structure = function ($value, $depth = 0) use (&$validate_structure, &$entries) {
        if ($depth > 3 || ++$entries > 50) {
          return false;
        }
        if (is_array($value)) {
          foreach ($value as $item) {
            if (!$validate_structure($item, $depth + 1)) {
              return false;
            }
          }
          return true;
        }
        return (is_null($value) || is_scalar($value));
      };

      if (!$validate_structure($contact)) {
        return array('success' => false);
      }
      $contacts[$contact_type] = $contact;
    }

    if (empty($contacts)) {
      return array('success' => false);
    }

    foreach ($contacts as $contact_type => $contact) {
      $_SESSION['paypal']['contact'][$contact_type] = $contact;
    }

    return array('success' => true);
  }
