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
 * $Id$
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class HitmeisterConfigure extends MagnaCompatibleConfigure {

	protected function getAuthValuesFromPost() {
		$nIdent = trim($_POST['conf'][$this->marketplace.'.ident']);
		$nAccessKey = trim($_POST['conf'][$this->marketplace.'.accesskey']);
		$nAccessKey = $this->processPasswordFromPost('mppassword', $nAccessKey);
		$nMPUser = trim($_POST['conf'][$this->marketplace.'.mpusername']);
		$nMPPass = trim($_POST['conf'][$this->marketplace.'.mppassword']);
		$nMPPass = $this->processPasswordFromPost('mppassword', $nMPPass);

		if (empty($nIdent)) {
			unset($_POST['conf'][$this->marketplace.'.ident']);
		}
		if ($nAccessKey === false) {
			unset($_POST['conf'][$this->marketplace.'.accesskey']);
			return false;
		}
		if (empty($nMPUser)) {
			unset($_POST['conf'][$this->marketplace.'.mpusername']);
		}
		if ($nMPPass === false) {
			unset($_POST['conf'][$this->marketplace.'.mppassword']);
			return false;
		}
		$data = array (
			'IDENT' => $nIdent,
			'ACCESSKEY' => $nAccessKey,
			'MPUSERNAME' => $nMPUser,
			'MPPASSWORD' => $nMPPass,
		);
		#echo print_m($data);
		return $data;
	}
	
	protected function getFormFiles() {
		$forms = parent::getFormFiles();
		$forms[] = 'login';
		$forms[] = 'prepareadd';
		return $forms;
	}
	
	public function confShippingtimeMatching($args, &$value = '') {
		if (!defined('TABLE_SHIPPING_STATUS') || !MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
			return ML_ERROR_NO_SHIPPINGTIME_MATCHING;
		}
		$shippingtimes = MagnaDB::gi()->fetchArray('
		    SELECT shipping_status_id as id, shipping_status_name as name
		      FROM '.TABLE_SHIPPING_STATUS.'
		     WHERE language_id = '.$_SESSION['languages_id'].' 
		  ORDER BY shipping_status_id ASC
		');
		$shippingtimeMatch = getDBConfigValue($args['key'], $this->mpID, array());
		$opts = HitmeisterHelper::GetShippingTimes();
		$html = '<table class="nostyle" style="float: left; margin-right: 2em;">
			<thead><tr>
				<th>'.ML_LABEL_SHIPPING_TIME_SHOP.'</th>
				<th>'.ML_HITMEISTER_SHIPPINGTIME_HM.'</th>
			</tr></thead>
			<tbody>';
		foreach ($shippingtimes as $st) {
			$html .= '
				<tr>
					<td class="nowrap">'.$st['name'].'</td>
					<td><select name="conf['.$args['key'].']['.$st['id'].']">';
			foreach ($opts as $key => $val) {
				$html .= '<option value="'.$key.'" '.(
					(array_key_exists($st['id'], $shippingtimeMatch) && ($shippingtimeMatch[$st['id']] == $key))
						? 'selected="selected"'
						: ''
				).'>'.$val.'</option>';
			}
			$html .= '
					</select></td>
				</tr>';
		}
		$html .= '</tbody></table>';
	
		#$html .= print_m(func_get_args(), 'func_get_args');
	
		#$html .= print_m($shippingtimes, '$shippingtimes');
		#$html .= print_m($shippingtimeMatch, 'shippingtimeMatch');
		return $html;
	}
	
	protected function loadChoiseValues() {
		parent::loadChoiseValues();
		
		mlGetCountriesWithIso2Keys($this->form['prepare']['fields']['location']);
		HitmeisterHelper::GetConditionTypesConfig($this->form['prepare']['fields']['condition']);
		HitmeisterHelper::GetShippingTimesConfig($this->form['prepare']['fields']['shippingtime']);
		
		$this->form['prepare']['fields']['shippingtimeMatching']['procFunc'] = array($this, 'confShippingtimeMatching');
		/*
		if (isset($this->form['orders']['fields']['unpaidsatus'])) {
			mlGetOrderStatus($this->form['orders']['fields']['unpaidsatus']);
		}
		*/
	}
	
}
