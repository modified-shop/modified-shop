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


class klarna_klarna extends KlarnaPayment {

  var $code;
  var $klarna_code;

  function __construct() {
    global $order;

    $this->code = 'klarna_klarna';
    $this->klarna_code = 'klarna';

    KlarnaPayment::__construct($this->code);

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

    $_SESSION['klarna']['script'][$this->klarna_code] = '
          Klarna.Payments.load({
            container: "#klarna-payments-klarna",
            payment_method_category: "'.$this->klarna_code.'"
          });';

    return array(
      'id' => $this->code,
      'module' => MODULE_PAYMENT_KLARNA_KLARNA_TEXT_TITLE,
      'description' => $info,
    );
  }

}
