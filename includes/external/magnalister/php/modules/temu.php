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
 * (c) 2010 - 2026 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once('magnacompatible.php');

class TemuMarketplace extends MagnaCompatMarketplace {

	protected static $aRegionalUnits = array(
		'105' => array('currency' => 'EUR', 'weightUnit' => 'g', 'volumeUnit' => 'cm'),
		'100' => array('currency' => 'USD', 'weightUnit' => 'lb', 'volumeUnit' => 'in'),
		'102' => array('currency' => 'GBP', 'weightUnit' => 'g', 'volumeUnit' => 'cm'),
		'108' => array('currency' => 'JPY', 'weightUnit' => 'g', 'volumeUnit' => 'cm'),
		'109' => array('currency' => 'KRW', 'weightUnit' => 'g', 'volumeUnit' => 'cm'),
	);

	public function checkCurrency() {
		$mpID = $this->mpID;
		$authInfo = $this->getAuthInfo($mpID);

		// Map SiteId to currency (from v3 temu_regions)
		$aSiteIdCurrency = array(
			105 => 'EUR', 106 => 'EUR', 107 => 'EUR', 109 => 'EUR', 108 => 'EUR',  // DE, FR, IT, ES, NL
			142 => 'EUR', 143 => 'EUR', 111 => 'EUR', 144 => 'EUR', 115 => 'EUR',  // BE, AT, PT, FI, GR
			145 => 'EUR', 147 => 'EUR', 146 => 'EUR', 116 => 'EUR',                 // SK, SI, HR, IE
			102 => 'GBP', 112 => 'PLN', 113 => 'SEK', 139 => 'DKK', 124 => 'NOK',  // UK, PL, SE, DK, NO
			137 => 'CZK', 138 => 'HUF', 140 => 'RON', 141 => 'BGN', 114 => 'CHF',  // CZ, HU, RO, BG, CH
			174 => 'TRY',                                                              // TR
			100 => 'USD',                                                              // US
			101 => 'CAD', 103 => 'AUD', 104 => 'NZD', 118 => 'JPY', 119 => 'KRW',  // CA, AU, NZ, JP, KR
			110 => 'MXN',                                                              // MX
		);

		$currency = 'EUR';
		if (isset($authInfo['SiteId']) && isset($aSiteIdCurrency[$authInfo['SiteId']])) {
			$currency = $aSiteIdCurrency[$authInfo['SiteId']];
		} elseif (isset($authInfo['regionId']) && isset(self::$aRegionalUnits[$authInfo['regionId']])) {
			$currency = self::$aRegionalUnits[$authInfo['regionId']]['currency'];
		}

		$_SESSION['magna_' . $mpID . '_currency'] = $currency;
		setDBConfigValue('temu.currency', $mpID, $currency, true);
		return $currency;
	}

	protected function extraChecks() {
		$mpID = $this->mpID;
		$authInfo = $this->getAuthInfo($mpID);
		if (isset($authInfo['expiresAt']) && !empty($authInfo['expiresAt'])) {
			$expiresAt = strtotime($authInfo['expiresAt']);
			$daysLeft = floor(($expiresAt - time()) / 86400);
			if ($daysLeft <= 0) {
				$this->boxes .= '<p class="errorBox">' . ML_TEMU_ERROR_TOKEN_EXPIRED . '</p>';
			} elseif ($daysLeft <= 7) {
				$this->boxes .= '<p class="noticeBox">' . sprintf(ML_TEMU_ERROR_TOKEN_EXPIRING, $daysLeft) . '</p>';
			}
		}
	}

	protected function getAuthInfo($mpID) {
		static $cache = array();
		if (isset($cache[$mpID])) {
			return $cache[$mpID];
		}
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'IsAuthed',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $mpID,
			), 30 * 60);
			$cache[$mpID] = isset($result['DATA']) ? $result['DATA'] : array();
		} catch (MagnaException $e) {
			$cache[$mpID] = array();
		}
		return $cache[$mpID];
	}

	public static function getRegionalUnits($mpID) {
		try {
			$result = MagnaConnector::gi()->submitRequestCached(array(
				'ACTION' => 'IsAuthed',
				'SUBSYSTEM' => 'Temu',
				'MARKETPLACEID' => $mpID,
			), 30 * 60);
			$regionId = isset($result['DATA']['regionId']) ? $result['DATA']['regionId'] : '105';
		} catch (MagnaException $e) {
			$regionId = '105';
		}
		if (isset(self::$aRegionalUnits[$regionId])) {
			return self::$aRegionalUnits[$regionId];
		}
		return self::$aRegionalUnits['105'];
	}
}

$obj = new TemuMarketplace($_MagnaSession['currentPlatform']);
