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

class PayoneAddressCheck {
	protected $_payone;
  var $content = array();
	
	public function __construct() {
		$this->_payone = new PayoneModified();
	}

  function set_content_data($key, $value) {
    $this->content[$key] = $value;
  }

	protected function _correctAddressBookEntry($ab_id, $data) {
		$sql_data_array = array('entry_street_address' => $data['street_address'],
                            'entry_postcode' => $data['postcode'],
                            'entry_city' => $data['city'],
                            );
    xtc_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_book_id='".(int)$ab_id."' AND customers_id='".(int)$_SESSION['customer_id']."'");
	}

	protected function _getPStatusMapping($pstatus) {
		$config = $this->_payone->getConfig();
		switch($pstatus) {
			case 'PPB':
				$mapping = $config['address_check']['pstatus']['fullnameknown'];
				break;
			case 'PHB':
				$mapping = $config['address_check']['pstatus']['lastnameknown'];
				break;
			case 'PAB':
				$mapping = $config['address_check']['pstatus']['nameunknown'];
				break;
			case 'PKI':
				$mapping = $config['address_check']['pstatus']['nameaddrambiguity'];
				break;
			case 'PNZ':
				$mapping = $config['address_check']['pstatus']['undeliverable'];
				break;
			case 'PPV':
				$mapping = $config['address_check']['pstatus']['dead'];
				break;
			case 'PPF':
				$mapping = $config['address_check']['pstatus']['postalerror'];
				break;
			case 'NONE':
			default:
				$mapping = $config['address_check']['pstatus']['nopcheck'];
		}
		return $mapping;
	}

	protected function _checkAddresses() {
		$checktypes = array('basic' => 'BA', 'person' => 'PE');
		$addresses_correct = true;
		$config = $this->_payone->getConfig();

		$_SESSION['payone_ac_billing_pstatus_mapping'] = $config['address_check']['pstatus']['nopcheck'];

		if ($config['address_check']['billing_address'] != 'none') {
			$billto_check = false;
			$ab_billto = $this->_payone->getAddressBookEntry($_SESSION['billto']);
			$this->set_content_data('billto_address', $ab_billto);
			$billto_checktype = $checktypes[$config['address_check']['billing_address']];
			if ($billto_checktype == 'PE' && $ab_billto['countries_iso_code_2'] != 'DE') {
				// fall back to basic check if address is not in Germany
				$billto_checktype = 'BA';
			}
			$billto_check = $this->_payone->addressCheck($_SESSION['billto'], $billto_checktype);
			if ($billto_check instanceof Payone_Api_Response_AddressCheck_Invalid || $billto_check instanceof Payone_Api_Response_Error) {
				$addresses_correct = false;
				$this->set_content_data('billto_customermessage', $billto_check->getCustomermessage());
				$this->set_content_data('billto_corrected_street', $ab_billto['entry_street_address']);
				$this->set_content_data('billto_corrected_zip', $ab_billto['entry_postcode']);
				$this->set_content_data('billto_corrected_city', $ab_billto['entry_city']);
			}
			else if ($billto_check instanceof Payone_Api_Response_AddressCheck_Valid) {
				if ($billto_check->isCorrect() == false) {
					$addresses_correct = false;
					$this->set_content_data('billto_corrected_street', $billto_check->getStreet());
					$this->set_content_data('billto_corrected_zip', $billto_check->getZip());
					$this->set_content_data('billto_corrected_city', $billto_check->getCity());
				}
				else {
					// fully validated address, store PStatus mapping
					$_SESSION['payone_ac_billing_pstatus_mapping'] = $this->_getPStatusMapping($billto_check->getPersonstatus());
					
					// store hash of validated address in session so we can detect any subsequent changes
					$_SESSION['payone_ac_billing_hash'] = $this->_payone->getAddressHash($_SESSION['billto']);
				}
			}
		}
		else {
			// no check, consider address validated
			$_SESSION['payone_ac_billing_hash'] = $this->_payone->getAddressHash($_SESSION['billto']);
		}

		$_SESSION['payone_ac_delivery_pstatus_mapping'] = $config['address_check']['pstatus']['nopcheck'];

		if ($config['address_check']['delivery_address'] != 'none') {
			$sendto_check = false;
			$ab_sendto = $this->_payone->getAddressBookEntry($_SESSION['sendto']);
			$this->set_content_data('sendto_address', $ab_sendto);
			$sendto_checktype = $checktypes[$config['address_check']['billing_address']];
			if ($sendto_checktype == 'PE' && $ab_sendto['countries_iso_code_2'] != 'DE') {
				// fall back to basic check if address is not in Germany
				$sendto_checktype = 'BA';
			}
			$sendto_check = $this->_payone->addressCheck($_SESSION['sendto'], $sendto_checktype);
			if ($sendto_check instanceof Payone_Api_Response_AddressCheck_Invalid || $sendto_check instanceof Payone_Api_Response_Error) {
				$addresses_correct = false;
				$this->set_content_data('sendto_customermessage', $sendto_check->getCustomermessage());
				$this->set_content_data('sendto_corrected_street', $ab_sendto['entry_street_address']);
				$this->set_content_data('sendto_corrected_zip', $ab_sendto['entry_postcode']);
				$this->set_content_data('sendto_corrected_city', $ab_sendto['entry_city']);
			}
			else if ($sendto_check instanceof Payone_Api_Response_AddressCheck_Valid) {
				if ($sendto_check->isCorrect() == false) {
					$addresses_correct = false;
					$this->set_content_data('sendto_corrected_street', $sendto_check->getStreet());
					$this->set_content_data('sendto_corrected_zip', $sendto_check->getZip());
					$this->set_content_data('sendto_corrected_city', $sendto_check->getCity());
				}
				else {
					// fully validated address, store PStatus mapping
					$_SESSION['payone_ac_delivery_pstatus_mapping'] = $this->_getPStatusMapping($sendto_check->getPersonstatus());

					// store hash of validated address in session so we can detect any subsequent changes
					$_SESSION['payone_ac_delivery_hash'] = $this->_payone->getAddressHash($_SESSION['sendto']);
				}
			}
		}
		else {
			// no check, consider address validated
			$_SESSION['payone_ac_delivery_hash'] = $this->_payone->getAddressHash($_SESSION['sendto']);
		}

		return $addresses_correct;
	}

	function get_html() {
	  global $PHP_SELF;
	  
		$config = $this->_payone->getConfig();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!empty($_POST['billto'])) {
				$this->_correctAddressBookEntry($_SESSION['billto'], $_POST['billto']);
			}
			if (!empty($_POST['sendto'])) {
				$this->_correctAddressBookEntry($_SESSION['sendto'], $_POST['sendto']);
			}
			// user has had a chance to review/correct addresses, consider them validated now
			$_SESSION['payone_ac_billing_hash'] = $this->_payone->getAddressHash($_SESSION['billto']);
			$_SESSION['payone_ac_delivery_hash'] = $this->_payone->getAddressHash($_SESSION['sendto']);
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
		}

		$addresses_correct = $this->_checkAddresses();
		if ($addresses_correct) {
			xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
		}

		if (isset($_SESSION['payone_error']) && $_SESSION['payone_error'] == 'address_changed') {
			$this->set_content_data('note_address_changed', NOTE_ADDRESS_CHANGED);
			unset($_SESSION['payone_error']);
		}

		$this->set_content_data('FORM_ACTION', xtc_draw_form('payone_address_check', xtc_href_link(basename($PHP_SELF), '', 'SSL'), 'post'));
    $this->set_content_data('BUTTON_CONFIRM', xtc_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM));
    $this->set_content_data('payonecss', DIR_WS_EXTERNAL.'payone/css/payone.css');

		$t_html_output = $this->_payone->build_html('checkout_payone_addresscheck.html', $this->content);
		return $t_html_output;
	}
}
?>