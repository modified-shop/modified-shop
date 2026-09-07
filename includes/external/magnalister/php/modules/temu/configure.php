<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/configure.php');

class TemuConfigure extends MagnaCompatibleConfigure {

	/**
	 * Temu regions configuration (from v3 temu_regions.php)
	 * Maps country code => regionId, siteId, dataArea, name, currency
	 */
	public static $aTemuRegions = array(
		// EU Data Area - Major Markets
		'DE' => array('regionId' => 76,  'siteId' => 105, 'dataArea' => 'EU', 'name' => 'Germany', 'currency' => 'EUR'),
		'FR' => array('regionId' => 69,  'siteId' => 106, 'dataArea' => 'EU', 'name' => 'France', 'currency' => 'EUR'),
		'IT' => array('regionId' => 98,  'siteId' => 107, 'dataArea' => 'EU', 'name' => 'Italy', 'currency' => 'EUR'),
		'ES' => array('regionId' => 186, 'siteId' => 109, 'dataArea' => 'EU', 'name' => 'Spain', 'currency' => 'EUR'),
		'UK' => array('regionId' => 210, 'siteId' => 102, 'dataArea' => 'EU', 'name' => 'United Kingdom', 'currency' => 'GBP'),
		'NL' => array('regionId' => 141, 'siteId' => 108, 'dataArea' => 'EU', 'name' => 'Netherlands', 'currency' => 'EUR'),
		'BE' => array('regionId' => 20,  'siteId' => 142, 'dataArea' => 'EU', 'name' => 'Belgium', 'currency' => 'EUR'),
		'PL' => array('regionId' => 162, 'siteId' => 112, 'dataArea' => 'EU', 'name' => 'Poland', 'currency' => 'PLN'),
		'AT' => array('regionId' => 13,  'siteId' => 143, 'dataArea' => 'EU', 'name' => 'Austria', 'currency' => 'EUR'),
		'PT' => array('regionId' => 163, 'siteId' => 111, 'dataArea' => 'EU', 'name' => 'Portugal', 'currency' => 'EUR'),
		// EU Data Area - Nordic
		'SE' => array('regionId' => 191, 'siteId' => 113, 'dataArea' => 'EU', 'name' => 'Sweden', 'currency' => 'SEK'),
		'DK' => array('regionId' => 54,  'siteId' => 139, 'dataArea' => 'EU', 'name' => 'Denmark', 'currency' => 'DKK'),
		'FI' => array('regionId' => 68,  'siteId' => 144, 'dataArea' => 'EU', 'name' => 'Finland', 'currency' => 'EUR'),
		'NO' => array('regionId' => 151, 'siteId' => 124, 'dataArea' => 'EU', 'name' => 'Norway', 'currency' => 'NOK'),
		// EU Data Area - Southern/Eastern Europe
		'GR' => array('regionId' => 79,  'siteId' => 115, 'dataArea' => 'EU', 'name' => 'Greece', 'currency' => 'EUR'),
		'CZ' => array('regionId' => 53,  'siteId' => 137, 'dataArea' => 'EU', 'name' => 'Czech Republic', 'currency' => 'CZK'),
		'HU' => array('regionId' => 90,  'siteId' => 138, 'dataArea' => 'EU', 'name' => 'Hungary', 'currency' => 'HUF'),
		'RO' => array('regionId' => 167, 'siteId' => 140, 'dataArea' => 'EU', 'name' => 'Romania', 'currency' => 'RON'),
		'SK' => array('regionId' => 180, 'siteId' => 145, 'dataArea' => 'EU', 'name' => 'Slovakia', 'currency' => 'EUR'),
		'BG' => array('regionId' => 32,  'siteId' => 141, 'dataArea' => 'EU', 'name' => 'Bulgaria', 'currency' => 'BGN'),
		'SI' => array('regionId' => 181, 'siteId' => 147, 'dataArea' => 'EU', 'name' => 'Slovenia', 'currency' => 'EUR'),
		'HR' => array('regionId' => 50,  'siteId' => 146, 'dataArea' => 'EU', 'name' => 'Croatia', 'currency' => 'EUR'),
		// EU Data Area - Other
		'IE' => array('regionId' => 96,  'siteId' => 116, 'dataArea' => 'EU', 'name' => 'Ireland', 'currency' => 'EUR'),
		'CH' => array('regionId' => 192, 'siteId' => 114, 'dataArea' => 'EU', 'name' => 'Switzerland', 'currency' => 'CHF'),
		'TR' => array('regionId' => 203, 'siteId' => 174, 'dataArea' => 'EU', 'name' => 'T&uuml;rkiye', 'currency' => 'TRY'),
		// US Data Area
		'US' => array('regionId' => 211, 'siteId' => 100, 'dataArea' => 'US', 'name' => 'United States', 'currency' => 'USD'),
		// GLOBAL Data Area
		'CA' => array('regionId' => 37,  'siteId' => 101, 'dataArea' => 'GLOBAL', 'name' => 'Canada', 'currency' => 'CAD'),
		'AU' => array('regionId' => 12,  'siteId' => 103, 'dataArea' => 'GLOBAL', 'name' => 'Australia', 'currency' => 'AUD'),
		'NZ' => array('regionId' => 144, 'siteId' => 104, 'dataArea' => 'GLOBAL', 'name' => 'New Zealand', 'currency' => 'NZD'),
		'JP' => array('regionId' => 100, 'siteId' => 118, 'dataArea' => 'GLOBAL', 'name' => 'Japan', 'currency' => 'JPY'),
		'KR' => array('regionId' => 185, 'siteId' => 119, 'dataArea' => 'GLOBAL', 'name' => 'Republic of Korea', 'currency' => 'KRW'),
		'MX' => array('regionId' => 128, 'siteId' => 110, 'dataArea' => 'GLOBAL', 'name' => 'Mexico', 'currency' => 'MXN'),
	);

	protected static $aRegionGroupLabels = array(
		'EU' => 'Europa',
		'US' => 'Nordamerika',
		'GLOBAL' => 'Global',
	);

	protected function needToSetCredential() {
		return false;
	}

	protected function getAuthValuesFromPost() {
		return array();
	}

	protected function getFormFiles() {
		$aFiles = parent::getFormFiles();
		$aFiles[] = 'orderStatus';
		return $aFiles;
	}

	/**
	 * Get regions grouped by data area for dropdown rendering
	 */
	public static function getRegionGroups() {
		$aGroups = array('EU' => array(), 'US' => array(), 'GLOBAL' => array());
		foreach (self::$aTemuRegions as $sCode => $aRegion) {
			$aGroups[$aRegion['dataArea']][$sCode] = $aRegion['name'].' ('.$aRegion['currency'].')';
		}
		return $aGroups;
	}

	/**
	 * Render the Temu token request widget: region dropdown + request token button + auth status
	 */
	public static function TemuGetToken($args, &$value = '') {
		global $_MagnaSession, $_url;

		$mpID = $_MagnaSession['mpID'];
		$sSelectedRegion = getDBConfigValue('temu.region', $mpID, 'DE');

		// Check auth status
		$isAuthed = false;
		$authData = array();
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'IsAuthed',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $mpID,
			), 0); // ttl=0: auth state must stay fresh across requests (no persistent cache)
			if (isset($result['DATA'])) {
				$authData = $result['DATA'];
				$isAuthed = true;
			}
		} catch (MagnaException $e) {
			$isAuthed = false;
		}

		// Check token expiration
		$blExpiringSoon = false;
		$blExpired = false;
		if ($isAuthed && !empty($authData['expiresAt'])) {
			$iExpiresTimestamp = strtotime($authData['expiresAt']);
			$iSecondsRemaining = $iExpiresTimestamp - time();
			if ($iSecondsRemaining <= 0) {
				$blExpired = true;
			} elseif ($iSecondsRemaining <= 7 * 24 * 3600) {
				$blExpiringSoon = true;
			}
		}

		// Determine button label and state
		if ($blExpired) {
			$sButtonLabel = ML_TEMU_BUTTON_TOKEN_RENEW;
			$sButtonClass = 'ml-button mlbtn-action';
			$blDisabled = false;
		} elseif ($blExpiringSoon) {
			$sButtonLabel = ML_TEMU_BUTTON_TOKEN_RENEW;
			$sButtonClass = 'ml-button mlbtn-action';
			$blDisabled = false;
		} elseif ($isAuthed) {
			$sButtonLabel = ML_TEMU_BUTTON_TOKEN_CONNECTED;
			$sButtonClass = 'ml-button';
			$blDisabled = true;
		} else {
			$sButtonLabel = ML_TEMU_BUTTON_TOKEN_NEW;
			$sButtonClass = 'ml-button mlbtn-action';
			$blDisabled = false;
		}

		// Build region dropdown HTML
		$aGroups = self::getRegionGroups();
		$sRegionSelect = '';

		if (!$isAuthed || $blExpired || $blExpiringSoon) {
			$sRegionSelect .= '<div style="margin-bottom:10px;">';
			$sRegionSelect .= '<label for="temuRegionSelect">'.ML_TEMU_LABEL_SELECT_REGION.':</label> ';
			$sRegionSelect .= '<select id="temuRegionSelect" style="min-width:200px;">';
			foreach ($aGroups as $sDataArea => $aOptions) {
				if (empty($aOptions)) continue;
				$sGroupLabel = isset(self::$aRegionGroupLabels[$sDataArea]) ? self::$aRegionGroupLabels[$sDataArea] : $sDataArea;
				$sRegionSelect .= '<optgroup label="'.htmlspecialchars($sGroupLabel).'">';
				foreach ($aOptions as $sCode => $sLabel) {
					$selected = ($sCode === $sSelectedRegion) ? ' selected="selected"' : '';
					$sRegionSelect .= '<option value="'.htmlspecialchars($sCode).'"'.$selected.'>'.htmlspecialchars($sLabel).'</option>';
				}
				$sRegionSelect .= '</optgroup>';
			}
			$sRegionSelect .= '</select>';
			$sRegionSelect .= '<p style="font-size:11px;color:#666;margin-top:5px;">'.ML_TEMU_HINT_SELECT_REGION.'</p>';
			$sRegionSelect .= '</div>';
		}

		// Build auth status HTML
		$sAuthStatus = '';
		if ($isAuthed && !$blExpired) {
			$sAuthStatus .= '<div style="margin-top:15px;padding:10px;background-color:#f8f9fa;border-radius:4px;">';
			$sAuthStatus .= '<div style="color:green;margin-bottom:8px;"><strong>'.ML_TEMU_STATUS_CONNECTED.'</strong></div>';
			$sAuthStatus .= '<table style="font-size:12px;">';
			if (!empty($authData['mallId'])) {
				$sAuthStatus .= '<tr><td style="padding-right:15px;color:#666;">'.ML_TEMU_LABEL_MALL_ID.':</td>';
				$sAuthStatus .= '<td><strong>'.htmlspecialchars($authData['mallId']).'</strong></td></tr>';
			}
			if (!empty($authData['region'])) {
				$sRegionName = $authData['region'];
				if (isset(self::$aTemuRegions[$authData['region']])) {
					$r = self::$aTemuRegions[$authData['region']];
					$sRegionName = $r['name'].' ('.$r['currency'].')';
				}
				$sAuthStatus .= '<tr><td style="padding-right:15px;color:#666;">'.ML_TEMU_LABEL_REGION.':</td>';
				$sAuthStatus .= '<td>'.htmlspecialchars($sRegionName).'</td></tr>';
			}
			if (!empty($authData['expiresAt'])) {
				$sStyle = $blExpiringSoon ? ' style="color:#856404;"' : '';
				$sAuthStatus .= '<tr><td style="padding-right:15px;color:#666;">'.ML_TEMU_LABEL_TOKEN_EXPIRES.':</td>';
				$sAuthStatus .= '<td'.$sStyle.'>'.date('Y-m-d H:i', strtotime($authData['expiresAt'])).'</td></tr>';
			}
			$sAuthStatus .= '</table></div>';
		}

		// Expired/expiring warning
		$sWarning = '';
		if ($blExpired) {
			$sWarning = '<div style="background-color:#fee;border:1px solid #c00;padding:10px;margin-bottom:10px;border-radius:4px;">'
				.'<strong style="color:#c00;">'.ML_TEMU_ERROR_TOKEN_EXPIRED.'</strong></div>';
		} elseif ($blExpiringSoon) {
			$iDays = floor(($iExpiresTimestamp - time()) / 86400);
			$sWarning = '<div style="background-color:#fff3cd;border:1px solid #ffc107;padding:10px;margin-bottom:10px;border-radius:4px;">'
				.'<strong style="color:#856404;">'.sprintf(ML_TEMU_ERROR_TOKEN_EXPIRING, $iDays).'</strong></div>';
		}

		$sDisabled = $blDisabled ? ' disabled="disabled"' : '';
		$ajaxUrl = toURL($_url, array('what' => 'GetTokenCreationLink', 'kind' => 'ajax'), true);

		return $sWarning
			.$sRegionSelect
			.'<input class="'.$sButtonClass.'" type="button" value="'.$sButtonLabel.'" id="requestTemuToken"'.$sDisabled.' />'
			.$sAuthStatus
			.'
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$(\'#requestTemuToken\').click(function() {
		if ($(this).prop(\'disabled\')) return;
		var selectedRegion = $(\'#temuRegionSelect\').length ? $(\'#temuRegionSelect\').val() : '.json_encode($sSelectedRegion).';
		jQuery.blockUI(blockUILoading);
		jQuery.ajax({
			\'method\': \'get\',
			\'url\': \''.$ajaxUrl.'&region=\' + encodeURIComponent(selectedRegion),
			\'success\': function (data) {
				if (data.indexOf(\'<style\') > 0) {
					data = data.substring(0, data.indexOf(\'<style\'));
				}
				jQuery.unblockUI();
				myConsole.log(\'ajax.success\', data);
				try {
					var jsonData = jQuery.parseJSON(data);
					if (jsonData.error + \'\' !== \'\') {
						$(\'<div></div>\')
							.attr(\'title\', '.json_encode(ML_TEMU_ERROR_CREATE_TOKEN_LINK_HEADLINE).')
							.html(jsonData.error)
							.jDialog();
					} else if (jsonData.iframeUrl) {
						var hwin = window.open(jsonData.iframeUrl, "popup", "resizable=yes,scrollbars=yes,width=800,height=700");
						if (hwin && hwin.focus) hwin.focus();
					}
				} catch(e) {
					if (data === \'error\' || data === \'\') {
						$(\'<div></div>\')
							.attr(\'title\', '.json_encode(ML_TEMU_ERROR_CREATE_TOKEN_LINK_HEADLINE).')
							.html('.json_encode(ML_TEMU_ERROR_CREATE_TOKEN_LINK_TEXT).')
							.jDialog();
					} else {
						var hwin = window.open(data, "popup", "resizable=yes,scrollbars=yes,width=800,height=700");
						if (hwin && hwin.focus) hwin.focus();
					}
				}
			}
		});
	});
});
/*]]>*/</script>';
	}

	protected function loadChoiseValues() {
		parent::loadChoiseValues();

		$mpID = $this->mpID;

		// Populate order status dropdowns from shop's order status list
		if (isset($this->form['orderSyncState']['fields']['shippedstatus'])) {
			mlGetOrderStatus($this->form['orderSyncState']['fields']['shippedstatus']);
		}
		if (isset($this->form['orderSyncState']['fields']['cancelstatus'])) {
			mlGetOrderStatus($this->form['orderSyncState']['fields']['cancelstatus']);
			// Temu only supports "Out of Stock" cancellations (cancelOrder hardcodes
			// CancellationReason=1). Replace the generic shared "Cancel Order with" label/help
			// with the Out-of-Stock wording (parity with v3). Temu-only override — the shared
			// orderStatus.form and other marketplaces are untouched.
			$this->form['orderSyncState']['fields']['cancelstatus']['label'] = ML_TEMU_ORDERSTATUS_CANCELLED_LABEL;
			$this->form['orderSyncState']['fields']['cancelstatus']['desc'] = ML_TEMU_ORDERSTATUS_CANCELLED_DESC;
		}

		if (isset($this->form['prepare']['fields']['shippingtype'])) {
			$this->form['prepare']['fields']['shippingtype']['values'] = TemuHelper::GetShippingTypesConfig();
		}

		if (isset($this->form['generalshipment']['fields']['orderstatus.temu.warehouseid'])) {
			$aWarehouses = TemuHelper::getWarehouses($mpID);
			if (!empty($aWarehouses)) {
				$this->form['generalshipment']['fields']['orderstatus.temu.warehouseid']['values'] = $aWarehouses;
			}
		}

		// FEAT-026: the "Carrier" field is a mode-selector (parity with Otto's `send.carrier`). The
		// merchant picks ONE resolution mechanism: a fixed Temu carrier, `shopmatch` (map the order's
		// shop shipping method to a Temu carrier), or `dbmatch` (read the carrier from a DB field).
		// resolveCarrier() switches on this value — see TemuSyncOrderStatus::resolveCarrier().
		if (isset($this->form['selffulfillment']['fields']['orderstatus.sendcarrier.select'])) {
			$aCarriers = TemuHelper::getCarriers($mpID);
			$this->form['selffulfillment']['fields']['orderstatus.sendcarrier.select']['values'] = array(
				'' => ML_LABEL_CHOOSE,
				ML_SELECT_MARKETPLACE_SUGGESTED_CARRIER => is_array($aCarriers) ? $aCarriers : array(),
				ML_ADDITIONAL_OPTIONS => array(
					'shopmatch' => ML_MATCH_TEMU_CARRIER_TO_SHIPPING_MODULE,
					'dbmatch'   => ML_MATCH_CARRIER_TO_DB,
				),
			);
		}

		// Buy Shipping dropdowns
		if (isset($this->form['buyshipping']['fields']['buyshipping_weightunit'])) {
			$this->form['buyshipping']['fields']['buyshipping_weightunit']['values'] = array(
				'KILOGRAM' => 'Kilogram',
				'POUND' => 'Pound',
			);
		}

		if (isset($this->form['buyshipping']['fields']['buyshipping_dimensionunit'])) {
			$this->form['buyshipping']['fields']['buyshipping_dimensionunit']['values'] = array(
				'CENTIMETER' => 'Centimeter',
				'INCH' => 'Inch',
			);
		}

		if (isset($this->form['buyshipping']['fields']['buyshipping_logisticstype'])) {
			$aLogisticsTypes = array('' => '---');
			try {
				$result = MagnaConnector::gi()->submitRequestCached(array(
					'ACTION' => 'GetLogisticsTypes',
					'SUBSYSTEM' => 'Temu',
					'MARKETPLACEID' => $mpID,
				), 30 * 60);
				// API returns the list nested under DATA.LogisticsTypes as objects
				// ({ShipCompanyId, ShippingCompanyName, ShipLogisticsType}). The stored
				// value must be the ShipLogisticsType string (see TemuShipping.php).
				$aList = array();
				if (isset($result['DATA']['LogisticsTypes']) && is_array($result['DATA']['LogisticsTypes'])) {
					$aList = $result['DATA']['LogisticsTypes'];
				} elseif (isset($result['DATA']) && is_array($result['DATA'])) {
					$aList = $result['DATA'];
				}
				foreach ($aList as $type) {
					if (is_array($type)) {
						if (empty($type['ShipLogisticsType'])) {
							continue;
						}
						$sType = $type['ShipLogisticsType'];
						$aLogisticsTypes[$sType] = !empty($type['ShippingCompanyName'])
							? $type['ShippingCompanyName'].' - '.$sType
							: $sType;
					} elseif ($type !== '') {
						$aLogisticsTypes[$type] = $type;
					}
				}
			} catch (MagnaException $e) {
				$aLogisticsTypes['Standard'] = 'Standard';
				$aLogisticsTypes['Economy'] = 'Economy';
				$aLogisticsTypes['Express'] = 'Express';
			}
			$this->form['buyshipping']['fields']['buyshipping_logisticstype']['values'] = $aLogisticsTypes;
		}

		// FEAT-026: reorganise the carrier/tracking config for Temu. Remove the shared free-text
		// "Carrier" default (Temu has its own carrier dropdown), and move "Spediteur Matching" +
		// "Trackingcode Matching" out of the order-status-sync section up into "Self-Fulfillment
		// Settings" so all shipment-carrier config sits together. (These fields come from the shared
		// modules/orderStatus.form; we only re-place them in Temu's rendered form tree.)
		if (isset($this->form['orderSyncState']['fields']['carrier'])) {
			unset($this->form['orderSyncState']['fields']['carrier']);
		}
		if (isset($this->form['selffulfillment']['fields'])) {
			foreach (array('carrierMatch', 'trackingMatch') as $sMoveField) {
				if (isset($this->form['orderSyncState']['fields'][$sMoveField])) {
					$this->form['selffulfillment']['fields'][$sMoveField] = $this->form['orderSyncState']['fields'][$sMoveField];
					unset($this->form['orderSyncState']['fields'][$sMoveField]);
				}
			}
		}
	}

	protected function finalizeForm() {
		parent::finalizeForm();

		// Warn when order status sync is active AND a "shipped" status is mapped but the
		// fulfillment config is incomplete. The requirement only applies once shipping
		// confirmations can actually be produced — a cancellation-only setup (no shipped status)
		// needs none of this config, matching the cron's own pre-check, so it must not warn.
		if (getDBConfigValue('temu.orderstatus.sync', $this->mpID, 'no') == 'auto'
			&& '' != getDBConfigValue('temu.orderstatus.shipped', $this->mpID, '')
		) {
			$aMissing = array();
			if ('' == getDBConfigValue('temu.orderstatus.sendcarrier.select', $this->mpID, '')) {
				$aMissing[] = ML_TEMU_CONFIG_ORDERSTATUS_CARRIER_REQUIRED;
			}
			if ('' == getDBConfigValue('temu.orderstatus.temu.warehouseid', $this->mpID, '')) {
				$aMissing[] = ML_TEMU_CONFIG_ORDERSTATUS_WAREHOUSE_REQUIRED;
			}
			if (('true' === getDBConfigValue('temu.orderstatus.temu.usebuyshipping', $this->mpID, 'false'))
				&& ('' == getDBConfigValue('temu.orderstatus.temu.buyshipping.logisticstype', $this->mpID, ''))
			) {
				$aMissing[] = ML_TEMU_CONFIG_ORDERSTATUS_LOGISTICSTYPE_REQUIRED;
			}
			if (!empty($aMissing)) {
				// div, not p: a <ul> inside <p> makes browsers auto-close the paragraph and
				// the list would render outside the styled errorBox.
				$this->boxes .= '<div class="errorBox">'
					.ML_TEMU_CONFIG_ORDERSTATUS_INCOMPLETE_HEADING
					.'<ul><li>'.implode('</li><li>', $aMissing).'</li></ul>'
					.'</div>';
			}
		}

		if ($this->isAuthed) {
			$mpID = $this->mpID;
			try {
				$result = MagnaConnector::gi()->submitRequestCached(array(
					'ACTION' => 'IsAuthed',
					'SUBSYSTEM' => 'Temu',
					'MARKETPLACEID' => $mpID,
				), 0); // ttl=0: auth state must stay fresh across requests (no persistent cache)
				if (isset($result['DATA'])) {
					$authData = $result['DATA'];
					if (isset($authData['mallId'])) {
						$this->boxes .= '<div class="infoBox">'
							.ML_TEMU_LABEL_MALL_ID.': <strong>'.$authData['mallId'].'</strong>';
						if (isset($authData['region'])) {
							$this->boxes .= ' | '.ML_TEMU_LABEL_REGION.': <strong>'.$authData['region'].'</strong>';
						}
						if (isset($authData['expiresAt'])) {
							$this->boxes .= ' | '.ML_TEMU_LABEL_TOKEN_EXPIRES.': <strong>'.$authData['expiresAt'].'</strong>';
						}
						$this->boxes .= '</div>';
					}
				}
			} catch (MagnaException $e) {
				// silently ignore
			}
		}

		// Buy Shipping toggle: show/hide buyshipping fields based on radio state
		$this->boxes .= '
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	function toggleBuyShippingFields() {
		var isEnabled = $(\'input[name="conf[temu.orderstatus.temu.usebuyshipping]"][value="true"]\').is(\':checked\');
		$(\'.temu-buyshipping-field\').closest(\'tr\').toggle(isEnabled);
	}
	toggleBuyShippingFields();
	$(\'input[name="conf[temu.orderstatus.temu.usebuyshipping]"]\').on(\'change\', function() {
		toggleBuyShippingFields();
	});
});
/*]]>*/</script>';
	}

	public function process() {
		parent::process();
		// FEAT-026: collapse the carrier sub-fields not relevant to the selected mode (parity with
		// Otto's carrierScript). Echoed after the form so the elements exist when the script runs.
		echo self::carrierScript();
	}

	/**
	 * FEAT-026: show only the carrier sub-field that applies to the selected mode (parity with
	 * OttoConfigure::carrierScript). The "Carrier" mode-selector (`sendcarrier.select`) drives it:
	 *   - `shopmatch` → show the Carrier Shop-Matching rows, hide Spediteur (DB-)Matching
	 *   - `dbmatch`   → show Spediteur (DB-)Matching, hide the Shop-Matching rows
	 *   - a fixed carrier / nothing → hide both
	 * Runs on load and on change. Ids are the MLConfigurator-rendered element ids for the Temu carrier
	 * fields (Trackingcode Matching is left untouched — it applies regardless of the carrier mode).
	 */
	public static function carrierScript() {
		ob_start();
		?>
		<script>
		(function ($) {
			$(function () {
				var $sel = $('#config_temu_orderstatus_sendcarrier_select');
				if (!$sel.length) { return; }
				var $shopRow = $('#temu_orderstatus_sendcarrier_tosShopMatching').closest('tr');
				var $dbRow   = $('#config_temu_orderstatus_carrier_dbmatching_table_table').closest('tr');
				function toggle() {
					var v = $sel.val();
					$shopRow.toggle(v === 'shopmatch');
					$dbRow.toggle(v === 'dbmatch');
				}
				toggle();
				$sel.on('change', toggle);
			});
		})(jQuery);
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * FEAT-026: procFunc for the "Carrier Shop-Matching" duplicate field — renders one matching row
	 * (Temu-carrier select + shop-carrier select). Mirrors OttoConfigure::OttoCarrierOttoToShopMatchConfig.
	 */
	public static function TemuCarrierToShopMatchConfig($args) {
		global $_MagnaSession;
		$sHtml = '<table><tr>';
		$form = array();
		$cG = new MLConfigurator($form, $_MagnaSession['mpID'], 'conf_temu');
		foreach ($args['subfields'] as $item) {
			$configValue = getDBConfigValue($item['key'], $_MagnaSession['mpID'], '');
			$value = '';
			if (is_array($configValue) && isset($configValue[$args['currentIndex']])) {
				$value = $configValue[$args['currentIndex']];
			}
			$item['key'] .= '][';
			if (isset($item['params'])) {
				$item['params']['value'] = $value;
			}
			$sHtml .= '<td>'.$cG->renderInput($item, $value).'</td>';
		}
		$sHtml .= '</tr></table>';
		return $sHtml;
	}

	/**
	 * FEAT-026: procFunc for the Temu-carrier <select> inside a Shop-Matching row.
	 * Options = the Temu carrier list (CarrierId => CarrierName).
	 */
	public static function TemuCarriersConfig($args) {
		global $_MagnaSession;
		$aCarriers = TemuHelper::getCarriers($_MagnaSession['mpID']); // CarrierId => CarrierName (cached)
		$sHtml = '<select name="conf['.$args['key'].']">';
		$sHtml .= '<option value="">'.(defined('ML_LABEL_CHOOSE') ? ML_LABEL_CHOOSE : '---').'</option>';
		foreach ($aCarriers as $sId => $sName) {
			$sHtml .= '<option '.(isset($args['value']) && (string)$args['value'] === (string)$sId ? 'selected="selected"' : '').' value="'.$sId.'">'.fixHTMLUTF8Entities($sName).'</option>';
		}
		$sHtml .= '</select>';
		return $sHtml;
	}

	/**
	 * FEAT-026: procFunc for the shop-carrier <select> inside a Carrier Shop-Matching row.
	 * Mirrors OttoConfigure::OttoShopCarriersConfig — options come from mlGetShippingModules()
	 * (the shop's carriers table when present, else its installed shipping modules), keyed by the
	 * same value stored in `orders.shipping_method`, which resolveCarrier() matches against per order.
	 */
	public static function TemuShopCarriersConfig($args) {
		$aForm = array('values' => null);
		mlGetShippingModules($aForm);
		$aServices = is_array($aForm['values']) ? $aForm['values'] : array();
		if (empty($aServices)) {
			$aServices = array('' => '&mdash;');
		}
		$sHtml = '<select name="conf['.$args['key'].']">';
		foreach ($aServices as $sKey => $sName) {
			$sHtml .= '<option '.(isset($args['value']) && (string)$args['value'] === (string)$sKey ? 'selected="selected"' : '').' value="'.$sKey.'">'.fixHTMLUTF8Entities($sName).'</option>';
		}
		$sHtml .= '</select>';
		return $sHtml;
	}
}

// AJAX handler for GetTokenCreationLink
if (isset($_GET['what']) && $_GET['what'] == 'GetTokenCreationLink') {
	$sRegionCode = isset($_GET['region']) ? $_GET['region'] : 'DE';
	$aResponse = array('iframeUrl' => '', 'error' => '');

	$aRegions = TemuConfigure::$aTemuRegions;
	if (!isset($aRegions[$sRegionCode])) {
		$sRegionCode = 'DE';
	}

	$aRegionData = $aRegions[$sRegionCode];
	$iRegionId = $aRegionData['regionId'];
	$iSiteId = $aRegionData['siteId'];
	$sDataArea = $aRegionData['dataArea'];

	try {
		$result = MagnaConnector::gi()->submitRequest(array(
			'ACTION' => 'GetTokenCreationLink',
			'SiteId' => $iSiteId,
			'RegionId' => $iRegionId,
			'DataArea' => $sDataArea,
		));
		$aResponse['iframeUrl'] = isset($result['DATA']['iframeUrl']) ? $result['DATA']['iframeUrl'] : '';
		if (empty($aResponse['iframeUrl'])) {
			$aResponse['error'] = 'No token link returned from API.';
		}
	} catch (MagnaException $e) {
		$aResponse['error'] = $e->getMessage();
	}
	echo json_encode($aResponse);
	exit();
}
