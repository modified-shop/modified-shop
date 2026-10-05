<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed classes
require_once(DIR_FS_EXTERNAL.'klarna/classes/KlarnaPayment.php');


class klarna extends KlarnaPayment {

  var $code;
  var $klarna_code;

  function __construct() {
    global $order;

    $this->code = 'klarna';
    $this->klarna_code = 'klarna';

    KlarnaPayment::__construct($this->code);

    if (defined('RUN_MODE_ADMIN') && defined('MODULE_PAYMENT_KLARNA_TEXT_INSTALL_NOTE')) {
      $this->description .= MODULE_PAYMENT_KLARNA_TEXT_INSTALL_NOTE;
    }
    if (defined('RUN_MODE_ADMIN') && defined('MODULE_PAYMENT_KLARNA_REMOVE_NOTE')) {
      $this->properties['remove'] = array(MODULE_PAYMENT_KLARNA_REMOVE_NOTE);
    }

    // checkout_process builds the module before $order exists and without update_status()
    if (isset($_SESSION['klarna']['chosen_category']) && is_string($_SESSION['klarna']['chosen_category'])) {
      $this->klarna_code = $_SESSION['klarna']['chosen_category'];
    }

    if (!defined('RUN_MODE_ADMIN') && is_object($order)) {
      $this->update_status();
    }
  }


  // every category Klarna returns is served here, one choice for payment page, confirmation and checkout_process
  function choose_category() {
    if (!isset($_SESSION['klarna']['methods']) || !is_array($_SESSION['klarna']['methods'])) {
      return;
    }

    $identifiers = array();
    foreach ($_SESSION['klarna']['methods'] as $methods) {
      if (isset($methods['identifier'])) {
        $identifiers[] = $methods['identifier'];
      }
    }

    $category = klarna_choose_category($identifiers);
    if ($category != '') {
      $this->klarna_code = $category;
      $_SESSION['klarna']['chosen_category'] = $category;
    }
  }


  function selection() {
    $info = '<div id="klarna-payments-klarna"></div>
             <script>var klarna_'.$this->klarna_code.'_result = false;</script>';

    // the only Klarna entry on the page, scripts of old modules from earlier page views go
    $_SESSION['klarna']['script'] = array();
    $_SESSION['klarna']['script'][$this->klarna_code] = '
          Klarna.Payments.load({
            container: "#klarna-payments-klarna",
            payment_method_category: "'.$this->klarna_code.'"
          });';

    return array(
      'id' => $this->code,
      'module' => ((defined('MODULE_PAYMENT_KLARNA_TEXT_TITLE') && MODULE_PAYMENT_KLARNA_TEXT_TITLE != '') ? MODULE_PAYMENT_KLARNA_TEXT_TITLE : 'Klarna'),
      'description' => $info,
    );
  }


  function install() {
    parent::install();

    // inactive until the merchant has checked the settings, klarna replaces the old modules in the checkout once it is on
    xtc_db_query("UPDATE ".TABLE_CONFIGURATION."
                     SET configuration_value = 'False'
                   WHERE configuration_key = 'MODULE_PAYMENT_KLARNA_STATUS'");
  }


  function remove() {
    // the shared and the express keys have the same prefix, so only the own keys go, by name
    $own_keys = array_diff($this->keys(), KlarnaPaymentBase::shared_keys());
    if (count($own_keys) < 1) {
      return;
    }
    
    $own_keys = array_map('xtc_db_input', array_values($own_keys));
    xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION."
                        WHERE configuration_key IN ('".implode("', '", $own_keys)."')");
  }

}
