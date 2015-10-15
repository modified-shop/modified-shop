<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class PayoneCreditRisk {
	protected $_payone;
  var $content = array();
  
	public function __construct($code) {
		$this->_payone = new PayoneModified();
		$this->code = $code;
	}

  function set_content_data($key, $value) {
    $this->content[$key] = $value;
  }
    
	function get_html() {
	  global $PHP_SELF;
	  
	  $config = $this->_payone->getConfig();
	  
		$this->set_content_data('notice', $config['credit_risk']['notice']['text']);
		$this->set_content_data('confirmation', $config['credit_risk']['confirmation']['text']);
    $this->set_content_data('timeofcheck', $config['credit_risk']['timeofcheck']);
    $this->set_content_data('IMGBUTTON_CONFIRM', xtc_image_button('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
    $this->set_content_data('IMGBUTTON_CANCEL', xtc_image_button('small_delete.gif', IMAGE_BUTTON_CANCEL));
 
    $hidden = xtc_draw_hidden_field('p1crcheck', 'true').PHP_EOL;
    foreach ($_POST as $key => $value) {
      if (!is_object(${$key})) {
        $hidden .= xtc_draw_hidden_field($key, $value).PHP_EOL;
      }
    }
		$this->set_content_data('form_action',  xtc_draw_form('p1crconfirm', xtc_href_link(basename($PHP_SELF), '', 'SSL')).$hidden);
    $this->set_content_data('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');

		$t_html_output = $this->_payone->build_html('checkout_payone_cr.html', $this->content);
		return $t_html_output;
	}
	
	function credit_risk_check() {
		$config = $this->_payone->getConfig();

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['noconfirm'])) {
      if ($config['credit_risk']['timeofcheck'] == 'before') {
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'p1crskip=1', 'SSL'));
      } else {
        $_SESSION['payone_error'] = CREDIT_RISK_FAILED;
        xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error='.$this->code, 'SSL'));				
      }
    }

    // A/B testing: only perform scoring every n-th time
    $do_score = true;
    if ($config['credit_risk']['abtest']['active'] == 'true') {
      $ab_value = max(1, (int)$config['credit_risk']['abtest']['value']);
      $score_count = (int)MODULE_PAYMENT_PAYONE_AB_TESTING;
      $do_score = ($score_count % $ab_value) == 0;
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='" . ($score_count + 1) . "', last_modified = NOW() where configuration_key='MODULE_PAYMENT_PAYONE_AB_TESTING'");
    }

    if ($do_score) {
      $score = $this->_payone->scoreCustomer($_SESSION['billto']);
    } else {
      $score = false;
    }
            
    if ($score instanceof Payone_Api_Response_Consumerscore_Valid) {
      switch((string)$score->getScore()) {
        case 'G':
          $_SESSION['payone_cr_result'] = 'green';
          break;
        case 'Y':
          $_SESSION['payone_cr_result'] = 'yellow';
          break;
        case 'R':
          $_SESSION['payone_cr_result'] = 'red';
          break;
        default:
          $_SESSION['payone_cr_result'] = $config['credit_risk']['newclientdefault'];
      }
      $_SESSION['payone_cr_hash']  = $this->_payone->getAddressHash($_SESSION['billto']);
    } else {
      // could not get a score value
      $_SESSION['payone_cr_result'] = $config['credit_risk']['newclientdefault'];
      $_SESSION['payone_cr_hash']  = $this->_payone->getAddressHash($_SESSION['billto']);
    }    
  }
}
?>