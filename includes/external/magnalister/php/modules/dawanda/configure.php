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

class DawandaConfigure extends MagnaCompatibleConfigure {

	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'ordersExtend';
		$forms[] = 'orderStatus';
		return $forms;
	}
	
	protected function mlGetProductTypes(&$form) {
		$aOpts = DawandaApiConfigValues::gi()->getProductTypes();
		if (!is_array($aOpts) || empty($aOpts)) {
			$form['values'][''] = ML_LABEL_DATA_CANNOT_BE_FETCHED;
			return;
		}
		foreach ($aOpts as $id => $val) {
			$form['values'][$id] = $val;
		}
	}
	
	protected function mlGetReturnPolicies(&$form) {
		$aOpts = DawandaApiConfigValues::gi()->getReturnPolicies();
		if (!is_array($aOpts) || empty($aOpts)) {
			$form['values'][''] = ML_LABEL_DATA_CANNOT_BE_FETCHED;
			return;
		}
		foreach ($aOpts as $id => $val) {
			$form['values'][$id] = $val['Title'];
		}
	}
	
	protected function mlGetShippingTimes(&$form) {
		$aOpts = DawandaApiConfigValues::gi()->getShippingTimes();
		if (!is_array($aOpts) || empty($aOpts)) {
			$form['values'][''] = ML_LABEL_DATA_CANNOT_BE_FETCHED;
			return;
		}
		foreach ($aOpts as $id => $val) {
			$form['values'][$id] = $val;
		}
	}
	
	protected function loadChoiseValues() {
		if ($this->isAuthed) {
			$this->mlGetProductTypes($this->form['prepare']['fields']['producttype']);
			$this->mlGetReturnPolicies($this->form['prepare']['fields']['returnpolicy']);
			
			$this->mlGetShippingTimes($this->form['checkin']['fields']['leadtimetoship']);
		}
		
		mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
		parent::loadChoiseValues();
	}

	protected function getAuthValuesFromPost() {
		$nAPIKey = trim($_POST['conf'][$this->marketplace.'.apikey']);
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);
		$nMPPass = trim($_POST['conf'][$this->marketplace.'.mppassword']);

		$nAPIKey = $this->processPasswordFromPost('apikey', $nAPIKey);
		$nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);

		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		if ($nMPPass === false) {
			unset($_POST['conf'][$this->marketplace.'.mppassword']);
		}
		if (empty($nAPIKey)) {
			unset($_POST['conf'][$this->marketplace.'.apikey']);
		}

		return array (
			'KEY' => $nAPIKey,
			'MPUSERNAME' => $nMPUser,
			'MPPASSWORD' => $nMPPass,
		);
	}

	protected function finalizeForm() {
		parent::finalizeForm();
		// DaWanda doesn't provide cancel funcionality
		unset($this->form['orderSyncState']['fields']['cancelstatus']);
		if (!$this->isAuthed) {
			$this->form = array (
				'login' => $this->form['login']
			);
			return;
		}
	}
	
	public static function leadTimeToShipMatching($args, &$value = '') {
		global $_MagnaSession;
		if (!defined('TABLE_SHIPPING_STATUS') || !MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
			return ML_ERROR_NO_SHIPPINGTIME_MATCHING;
		}
		
		$aShippingTimes = array('values' => array());
		mlGetShippingStatus($aShippingTimes);
		$aShippingTimes = $aShippingTimes['values'];
		
		$aLeadTimeToShipMatching = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
	
		$aOpts = DawandaApiConfigValues::gi()->getShippingTimes();
		
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th width="25%">'.ML_LABEL_SHIPPING_TIME_SHOP.'</th>
				<th width="75%">'.ML_DAWANDA_LABEL_SHIPPINGTIME.'</th>
			</tr></thead>
			<tbody>';
		foreach ($aShippingTimes as $stId => $stName) {
			$html .= '
				<tr>
					<td width="25%" class="nowrap">'.$stName.'</td>
					<td width="75%"><select name="conf['.$args['key'].']['.$stId.']">';
			foreach ($aOpts as $sKey => $sVal) {
				$html .= '<option value="'.$sKey.'" '.(
					(array_key_exists($stId, $aLeadTimeToShipMatching) && ($aLeadTimeToShipMatching[$stId] == $sKey))
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table><p>&nbsp;</p>';
		
		#	$html .= print_m($taxes, '$taxes');
		#	$html .= print_m(func_get_args(), 'func_get_args');
		return $html;
	}

	public static function languageMatching($args, &$value = '') {
		global $_MagnaSession;
		
		$languages = DawandaApiConfigValues::gi()->getLanguages();
		if (!isset($languages['MainLanguage']) || empty($languages['MainLanguage'])
			|| !isset($languages['AvailableLanguages']) || empty($languages['AvailableLanguages'])
		) {
			return ML_LABEL_DATA_CANNOT_BE_FETCHED;
		}
		
		$shopLanguages = array('values' => array());
		mlGetLanguages($shopLanguages);
		
		$configValues = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());
		if (!is_array($configValues)) {
			$configValues = array();
		}
		$html = '<table class="nostyle" width="100%" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th width="25%">'.ML_DAWANDA_LABEL_LANGUAGE.'</th>
				<th width="75%">'.ML_LABEL_SHOP_LANGUAGE.'</th>
			</tr></thead>
			<tbody>';
		foreach ($languages['AvailableLanguages'] as $lang) {
			$shopLangs = $shopLanguages['values'];
			if ($lang != $languages['MainLanguage']) {
				$shopLangs = array('' => ML_LABEL_DONT_SUBMIT) + $shopLangs;
			}
			$html .= '
				<tr>
					<td width="25%" class="nowrap">'.$lang.'</td>
					<td width="75%"><select name="conf['.$args['key'].']['.$lang.']">';
			foreach ($shopLangs as $sKey => $sVal) {
				$html .= '<option value="'.$sKey.'" '.(
					(array_key_exists($lang, $configValues) && ($configValues[$lang] == $sKey))
						? 'selected="selected"'
						: ''
					).'>'.$sVal.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table><p>&nbsp;</p>';
	
		#	$html .= print_m($taxes, '$taxes');
		#	$html .= print_m(func_get_args(), 'func_get_args');
		return $html;
	}
}

