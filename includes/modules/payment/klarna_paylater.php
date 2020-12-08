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


class klarna_paylater extends KlarnaPayment {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

    $this->code = 'klarna_paylater';
    $this->klarna_code = 'pay_later';

    KlarnaPayment::__construct($this->code);

    if (is_object($order)) {
      $this->update_status();
    }
  }


  function selection() {
    $data = $this->get_method();

    $info = '<div id="klarna-payments-pay-later"></div>
             <script>var klarna_'.$this->klarna_code.'_result = false;</script>';
        
    $_SESSION['klarna']['script'][$this->klarna_code] = '          
          Klarna.Payments.load({
            container: "#klarna-payments-pay-later",
            payment_method_category: "'.$this->klarna_code.'"
          });';
    
    return array(
      'id' => $this->code, 
      'module' => $data['name'], 
      'description' => $info,
    );
  }

}