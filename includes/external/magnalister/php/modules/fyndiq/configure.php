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
 * $Id: configure.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the GNU General Public License v2 or later
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class FyndiqConfigure extends MagnaCompatibleConfigure {

	protected function getFormFiles() {
		$forms = parent::getFormFiles();

		#$forms[] = 'product_template_generic';
		#$forms[] = 'ordersExtend';
		$forms[] = 'orderStatus';
		#echo print_m($forms);

		return $forms;
	}

	protected function mlGetCountries(&$form) {
		//tbd
		return;
	}

	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		if ($this->isAuthed) {
			if (isset($this->form['price']['fields']['whichpurchaseprice'])) {
				mlGetCustomersStatus($this->form['price']['fields']['whichpurchaseprice'], false);
				if (!empty($this->form['price']['fields']['whichpurchaseprice'])) {
					$this->form['price']['fields']['whichpurchaseprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
					ksort($this->form['price']['fields']['whichpurchaseprice']['values']);
				} else {
					unset($this->form['price']['fields']['whichpurchaseprice']);
				}
			}

			mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);

			//TODO Get all product attributes
//		$shopAttributes = array('values' => array());
//		FyndiqConfigure::getAttributes($shopAttributes);
//		$this->form['prepare']['fields']['brand']['values'] = $shopAttributes['values'];


			try {
				$deliveryServices = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetDeliveryServices'));
			} catch (MagnaException $me) {
				$deliveryServices = array(
					'DATA' => array('null' => ML_ERROR_LABEL)
				);
			}

			foreach ($deliveryServices['DATA'] as $deliveryService) {
				$this->form['orderSyncState']['fields']['service']['values'][$deliveryService] = $deliveryService;
			}
		}
	}

	protected function getAuthValuesFromPost() {
		$accessSettings = array();
		foreach ($_POST['conf'] as $sKey => $val) {
			if (strpos($sKey, $this->marketplace.'.access.') === 0) {
				$accessSettings[str_replace($this->marketplace.'.access.', '', $sKey)] = trim($val);
			}
		}
		$pwFields = array(
			'MPPASSWORD',
			'MPAPITOKEN'
		);
		foreach ($pwFields as $pwField) {
			$accessSettings[$pwField] = $this->processPasswordFromPost('access.'.$pwField, $accessSettings[$pwField]);
			if ($accessSettings[$pwField] === false) {
				unset($_POST[$this->marketplace.'.access.'.$pwField]);
			}
		}
		foreach ($accessSettings as $field => $val) {
			if (empty($val)) {
				unset($_POST[$this->marketplace.'.access.'.$field]);
			}
		}

		#echo print_m($accessSettings, '$accessSettings');

		return $accessSettings;
	}

	protected function finalizeForm() {
		parent::finalizeForm();
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
	}

	public static function taxMatching($args, &$value = '') {
		global $_MagnaSession;

		$taxes = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetVatPercent'));
		$taxes = $taxes['DATA'];
		$shopTaxes = array('values' => array());
		FyndiqConfigure::getTaxes($shopTaxes);

		$configValues = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
		if (!is_array($configValues)) {
			$configValues = array();
		}
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th width="75%">'.ML_LABEL_SHOP_TAXES.'</th>
				<th width="25%">'.ML_FYNDIQ_LABEL_TAXES.'</th>
			</tr></thead>
			<tbody>';
		$shopTaxes = $shopTaxes['values'];
		foreach ($shopTaxes as $keyTax => $tax) {
			$html .= '
				<tr>
					<td width="25%" class="nowrap">'.$tax.'</td>
					<td width="75%"><select name="conf['.$args['key'].']['.$keyTax.']">';
			foreach ($taxes as $sKey => $sVal) {
				$html .= '<option value="'.$sKey.'" '.(
					(array_key_exists($keyTax, $configValues) && ($configValues[$keyTax] == $sKey))
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table><p>&nbsp;</p>';

		return $html;
	}

	public static function getTaxes(&$form) {
		$data = MagnaDB::gi()->fetchArray(eecho('
			SELECT tax_class_id AS id, tax_class_title AS name
			FROM `'.TABLE_TAX_CLASS.'`
		', false));

		$form['values'] = array();

		foreach ($data as $elem) {
			$form['values'][$elem['id']] = fixHTMLUTF8Entities($elem['name']);
		}
	}

	public static function identifierMatching($args, &$value = '') {
		global $_MagnaSession;

		$identifiers = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetIdentifierType'));
		$identifiers = $identifiers['DATA'];
		$shopIdentifiers = array('values' => array());
		FyndiqConfigure::getAttributes($shopIdentifiers);

		$configValues = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
		if (!is_array($configValues)) {
			$configValues = array();
		}
		$html = '
			<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
				<thead><tr>
					<th width="75%">'.ML_LABEL_SHOP_ATTRIBUTES.'</th>
					<th width="25%">'.ML_FYNDIQ_LABEL_IDENTIFIERS.'</th>
				</tr></thead>
				<tbody>
					<tr>
						<td width="25%" class="nowrap">
							<select name="conf['.$args['key'].'][identifier]">';
								$shopIdentifiers = $shopIdentifiers['values'];
								foreach ($shopIdentifiers as $keyIdentifier => $identifier) {
									$html .= '<option value="'.$keyIdentifier.'" '.(
										(array_key_exists('identifier', $configValues) && ($configValues['identifier'] == $keyIdentifier))
											? 'selected="selected"'
											: ''
										).'>'.$identifier.'</option>';
								}
								$html .= '
							</select>
						</td>
						<td width="75%">
							<select name="conf['.$args['key'].'][shop.identifier]">';
								foreach ($identifiers as $sKey => $sVal) {
									$html .= '<option value="'.$sKey.'" '.(
										(array_key_exists('shop.identifier', $configValues) && ($configValues['shop.identifier'] == $sKey))
											? 'selected="selected"'
											: ''
										).'>'.$sKey.'</option>';
								}
								$html .= '
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<p>&nbsp;</p>';

		return $html;
	}

	public static function getAttributes(&$form) {
		$data = MagnaDB::gi()->fetchArray(eecho('
			SELECT tax_class_id AS id, tax_class_title AS name
			FROM `'.TABLE_TAX_CLASS.'`
		', false));

		$form['values'] = array();

		foreach ($data as $elem) {
			$form['values'][$elem['id']] = fixHTMLUTF8Entities($elem['name']);
		}
	}

}

