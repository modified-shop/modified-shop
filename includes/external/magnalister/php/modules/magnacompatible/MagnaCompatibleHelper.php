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

class MagnaCompatibleHelper {
	public static function processCheckinErrors($result, $mpID) {
		$fieldname = 'MARKETPLACEERRORS';
		$dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
		if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
			# means the same for us
			$dbCharSet = 'utf8';
		}
		if ($dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($result[$fieldname]);
		}
		if (!isset($result[$fieldname]) || empty($result[$fieldname])) {
			return;
		}
		foreach ($result[$fieldname] as $err) {
			if (!isset($err['AdditionalData'])) {
				$err['AdditionalData'] = array();
			}
			$err = array (
				'mpID' => $mpID,
				'errormessage' => $err['ErrorMessage'],
				'dateadded' => $err['DateAdded'],
				'additionaldata' => serialize($err['AdditionalData']),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}
	
	public static function checkProductSaveJsonArray($aCheckArray) {
		foreach ($aCheckArray as $sKey => &$sEntry) {
			if (empty($sEntry)) {
				unset($aCheckArray[$sKey]);
			}
		}
		
		if (0 < count($aCheckArray)) {
			return json_encode($aCheckArray);
		} else {
			return '';
		}
	}

	public static function encodeData($mValue) {
		if (is_array($mValue)) {
			$sValue = json_encode($mValue);
		} elseif (is_object($mValue)) {
			$sValue = serialize($mValue);
		} elseif ($mValue !== null) {
			$sValue = (string) $mValue;
		} else {
			$sValue = null;
		}
		return $sValue;
	}
	
}
