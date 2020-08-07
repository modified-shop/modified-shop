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

class TradoriaConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nAPIKey = trim($_POST['conf'][$this->marketplace.'.apikey']);
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);

		if (empty($nAPIKey)) {
			unset($_POST['conf'][$this->marketplace.'.apikey']);
		}
		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		
		return array (
			'KEY' => $nAPIKey,
			'MPUSERNAME' => $nMPUser,
		);
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'checkinTaxMatching';
		$forms[] = 'checkinLeadtimeToShip';
		$forms[] = 'checkinShippingGroup';
		$forms[] = 'checkinSubmitVariations';
		$forms[] = 'orderStatus';
		$forms[] = 'catMatch';
		$forms[] = 'manufacturerPartNumberMatching';
		$forms[] = 'orderImportExtras';
		$forms[] = 'promotionmail';
		return $forms;
	}

	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		if($this->form['orderSyncState']){
		    require_once(DIR_MAGNALISTER_MODULES.'tradoria/classes/TradoriaApiConfigValues.php');
		    $this->form['orderSyncState']['fields']['carrier']['type'] = 'selection';
		    $this->form['orderSyncState']['fields']['carrier']['values'] = TradoriaApiConfigValues::gi()->GetCarriers();
		}
		unset($this->form['checkin']['fields']['leadtimetoship']['values']['__calc__']);
		if (isset($this->form['orderSyncState']['fields']['shippedstatus'])) {
			mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
		}
		if (isset($this->form['orderSyncState']['fields']['cancelstatus'])) {
			mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
		}
		if (isset($this->form['price']['fields']['whichstrikeprice'])) {
			mlGetCustomersStatus($this->form['price']['fields']['whichstrikeprice'], false);
			if (!empty($this->form['price']['fields']['whichstrikeprice'])) {
				$this->form['price']['fields']['whichstrikeprice']['values']['-1'] = ML_LABEL_DONT_USE;
				$this->form['price']['fields']['whichstrikeprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
				ksort($this->form['price']['fields']['whichstrikeprice']['values']);
			} else {
				unset($this->form['price']['fields']['whichstrikeprice']);
			}
		}
	}
	
	protected function finalizeForm() {
		parent::finalizeForm();
		$this->form['checkin']['fields']['tax']['procFunc'] = array($this, 'confTaxMatching');
	}

	public function confTaxMatching($args, &$value = '') {
		$taxes = MagnaDB::gi()->fetchArray('
		    SELECT tc.tax_class_id AS id, MAX(tr.tax_rate) AS rate, tc.tax_class_title AS title
		      FROM '.TABLE_TAX_RATES.' tr, '.TABLE_TAX_CLASS.' tc
		     WHERE tr.tax_class_id=tc.tax_class_id
		  GROUP BY tc.tax_class_id
		');
		$taxMatch = getDBConfigValue($args['key'], $this->mpID, array());
		$opts = array (
			'1' => '19 %',
			'2' => '7 %',
			'3' => '0 %',
			'4' => '10,7 %',
			'10' => '10 %',
			'11' => '12 %',
			'12' => '20 %',
		);
		$html = '<table class="nostyle"><tbody>';
		foreach ($taxes as $tax) {
			$html .= '
				<tr>
					<td>'.$tax['title'].' ('.$tax['rate'].'%)</td>
					<td><select name="conf['.$args['key'].']['.$tax['id'].']">';
			foreach ($opts as $key => $val) {
				$html .= '<option value="'.$key.'" '.(
					(array_key_exists($tax['id'], $taxMatch) && ($taxMatch[$tax['id']] == $key))
						? 'selected="selected"'
						: ''
				).'>'.$val.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table>';
	
	#	$html .= print_m($taxes, '$taxes');
	#	$html .= print_m(func_get_args(), 'func_get_args');
		return $html;
	}
	
}
