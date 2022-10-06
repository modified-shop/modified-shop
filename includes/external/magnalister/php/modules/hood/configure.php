<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodShippingDetailsProcessor.php');
require_once(DIR_MAGNALISTER_MODULES.'hood/classes/HoodTopTenCategories.php');

class HoodConfigure extends MagnaCompatibleConfigure {

	protected $exchangeRateField = 'conf_hood.price.exchangerate_update';

	protected function getAuthValuesFromPost() {
		$nAPIKey = trim($_POST['conf'][$this->marketplace.'.apikey']);
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);
		$nAPIKey = $this->processPasswordFromPost('apikey', $nAPIKey);

		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		if (empty($nAPIKey)) {
			unset($_POST['conf'][$this->marketplace.'.apikey']);
		}
		
		return array (
			'KEY' => $nAPIKey,
			'MPUSERNAME' => $nMPUser,
		);
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		
		$forms[] = 'orderStatus';
		$forms[] = 'orderStatusHood';
		$forms[] = 'orders_payment_matching';
		$forms[] = 'template';
		
		return $forms;
	}

	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		
		# prepare
		$this->form['payment']['fields']['paymentmethod']['values'] = HoodApiConfigValues::gi()->getHoodPaymentOptions();
		
		if (false === getDBConfigValue('hood.imagepath', $this->mpID, false)) {
			$this->form['images']['fields']['imagepath']['default'] = defined('DIR_WS_CATALOG_POPUP_IMAGES')
				? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
				: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
			setDBConfigValue('hood.imagepath', $this->mpID, $this->form['images']['fields']['imagepath']['default'], true);
		}
		mlGetLanguages($this->form['listingdefaults']['fields']['language']);
		mlGetManufacturers($this->form['listingdefaults']['fields']['manufacturerfilter']);
		
		#fixed listings
		mlGetCustomersStatus($this->form['fixedsettings']['fields']['whichprice'], true);
		if (!empty($this->form['fixedsettings']['fields']['whichprice'])) {
			$this->form['fixedsettings']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
			ksort($this->form['fixedsettings']['fields']['whichprice']['values']);
			unset($this->form['fixedsettings']['fields']['specialprices']);
		} else {
			unset($this->form['fixedsettings']['fields']['whichprice']);
		}	
		mlGetCustomersStatus($this->form['fixedsettings']['fields']['whichstrikeprice'], true);
		if (!empty($this->form['fixedsettings']['fields']['whichstrikeprice'])) {
			$this->form['fixedsettings']['fields']['whichstrikeprice']['values']['-1'] = ML_LABEL_DONT_USE;
			$this->form['fixedsettings']['fields']['whichstrikeprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
			ksort($this->form['fixedsettings']['fields']['whichstrikeprice']['values']);
		} else {
			unset($this->form['fixedsettings']['fields']['whichstrikeprice']);
		}	
		
		$this->form['fixedsettings']['fields']['fixedduration']['values'] = HoodApiConfigValues::gi()->getListingDurations();
		
		# bidding auctions
		mlGetCustomersStatus($this->form['classicsettings']['fields']['whichprice'], true);
		if (!empty($this->form['classicsettings']['fields']['whichprice'])) {
			$this->form['classicsettings']['fields']['whichprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
			ksort($this->form['classicsettings']['fields']['whichprice']['values']);
			unset($this->form['classicsettings']['fields']['specialprices']);
		} else {
			unset($this->form['classicsettings']['fields']['whichprice']);
		}
		
		# no checkin config, this is all in prepare.form
		# checkin is set by magnacompatible
		unset($this->form['checkin']);
		# same with price
		unset($this->form['price']);

		$this->form['orders']['fields']['onlycomplete'] = array (
				'label' => ML_HOOD_IMPORTONLYPAID_LABEL,
				'desc' => ML_HOOD_IMPORTONLYPAID_DESC,
				'rightlabel' => ML_HOOD_IMPORTONLYPAID_RIGHTLABEL,
				'key' => 'hood.order.importonlypaid',
				'type' => 'radio',
				'submit' => 'Orders.ImportOnlyPaid',
				'values' => array (
					'true' => ML_BUTTON_LABEL_YES,
					'false' => ML_BUTTON_LABEL_NO
				),
				'default' => 'false'
		);

		
		# OrderSync
		mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
		unset($this->form['orderSyncState']['fields']['carrierMatch']);
		unset($this->form['orderSyncState']['fields']['trackingMatch']);
                unset($this->form['orderSyncState']['fields']['cancelstatus']);
                mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatusnostock']);
		mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatusdefect']);
		mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatusrevoked']);
		mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatusnopayment']);

		mlPresetTrackingCodeMatching($this->mpID, 'hood.orderstatus.carrier.dbmatching', 'hood.orderstatus.trackingcode.dbmatching');
		
	}
	
	protected function finalizeForm() {
		parent::finalizeForm();
		#echo var_dump_pre($this->isAuthed, '$this->isAuthed');
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
		
	}

	public function process() {
		parent::process();
		$cG = new MLConfigurator($this->form, $this->mpID, 'conf_hood');
		# case "HoodPay": Activate "Only Paid" setting
		if (1 == count($this->form['payment']['fields']['paymentmethod']['values'])) {
			?><script>/*<!CDATA[*/
				$('input[id="conf_hood.order.importonlypaid_true"]').val('true');
				$('input[id="conf_hood.order.importonlypaid_true"]').prop('checked', true);
				$('input[id="conf_hood.order.importonlypaid_false"]').val('false');
				$('input[id="conf_hood.order.importonlypaid_false"]').prop('checked', false);
				$('input[id="conf_hood.order.importonlypaid_false"]').change(function() {
					var rdio = $(this);
					if (rdio.attr('checked') != 'checked') return true;
					$('<div></div>').html('<?php echo ML_HOOD_IMPORTONLYPAID_HOODPAY_POPUP ?>').jDialog({
						title: '<?php echo ML_HOOD_IMPORTONLYPAID_HOODPAY_POPUP_TITLE ?>',
						buttons: {
							'<?php echo ML_BUTTON_LABEL_OK; ?>': function() {
							$('input[id="<?php echo 'conf_hood.order.importonlypaid_true';?>"]').attr('checked', 'checked');
							rdio.removeAttr('checked');
							jQuery(this).dialog('close');
							}
						}
					});
				});
			/*]]>*/</script><?php
		} else {
		echo $cG->radioAlert('conf_hood.order.importonlypaid', ML_HOOD_IMPORTONLYPAID_LABEL, ML_HOOD_IMPORTONLYPAID_DESC);
		}
	}
}
