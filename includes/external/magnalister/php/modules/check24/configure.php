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

class Check24Configure extends MagnaCompatibleConfigure {

	protected function getFormFiles() {
		$forms = parent::getFormFiles();

		#$forms[] = 'ordersExtend';
		//$forms[] = 'orderStatus';
		$forms[] = 'promotionmail';

		#echo print_m($forms);

		return $forms;
	}

	protected function mlGetCountries(&$form) {
		//tbd
		return;
	}

	protected function loadChoiseValues() {
		$apiUrl = MagnaConnector::gi()->getApiUrl();
		$apiUrl = rtrim($apiUrl, '/') . '/';

		$getInventoryFileUrlResult = MagnaConnector::gi()->submitRequest(array('ACTION' => 'GetInventoryFileUrl'));
		$this->form['login']['fields']['inventorypath']['value'] = $getInventoryFileUrlResult['DATA']['URL'];

		if (isset($this->form['price']['fields']['whichpurchaseprice'])) {
			mlGetCustomersStatus($this->form['price']['fields']['whichpurchaseprice'], false);
			if (!empty($this->form['price']['fields']['whichpurchaseprice'])) {
				$this->form['price']['fields']['whichpurchaseprice']['values']['0'] = ML_LABEL_SHOP_PRICE;
				ksort($this->form['price']['fields']['whichpurchaseprice']['values']);
			} else {
				unset($this->form['price']['fields']['whichpurchaseprice']);
			}
		}

		$this->form['shipping']['fields']['shippingtime']['values'] = array_slice(range(0, 30), 1, null, true);

		parent::loadChoiseValues();
	}

	protected function getAuthValuesFromPost() {
		$accessSettings = array();
		foreach ($_POST['conf'] as $sKey => $val) {
			if (strpos($sKey, $this->marketplace.'.access.') === 0) {
				$accessSettings[str_replace($this->marketplace.'.access.', '', $sKey)] = trim($val);
			}
		}
		$pwFields = array(
			'MPPASSWORD'
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

	public static function leadTimeToShipMatching($args, &$value = '') {
		global $_MagnaSession;
		if (!defined('TABLE_SHIPPING_STATUS') || !MagnaDB::gi()->tableExists(TABLE_SHIPPING_STATUS)) {
			return ML_ERROR_NO_SHIPPINGTIME_MATCHING;
		}

		$aShippingTimes = array('values' => array());
		mlGetShippingStatus($aShippingTimes);
		$aShippingTimes = $aShippingTimes['values'];

		$aLeadTimeToShipMatching = getDBConfigValue($args['key'], $_MagnaSession['mpID'], array());

		$aOpts = array_merge(array (
			'0' => '&mdash;',
		), range(1, 30));

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

	public function process() {
		parent::process();
		ob_start(); ?>
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
        /**
         * hide all related inputs of select option === lump
         */
        $('td.input > select option[value="EigeneAngaben"], td.input > select option[value="EigeneAngaben"]').closest("select").on("change", function() {
                var self = $(this);
                if (self.val() == "EigeneAngaben") {
                        self.closest("td").find(" > * ").not(self).show();
                } else {
                        self.closest("td").find(" > * ").not(self).hide();
                }
        }).trigger("change");
});
/*]]>*/</script>
		<?php
		$jsDeliveryModeHideMorefields = ob_get_contents();
		ob_end_clean();
		echo $jsDeliveryModeHideMorefields;
	}

}

