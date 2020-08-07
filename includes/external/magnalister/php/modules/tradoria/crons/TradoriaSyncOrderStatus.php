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
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleSyncOrderStatus.php');

class TradoriaSyncOrderStatus extends MagnaCompatibleSyncOrderStatus {
	public function __construct($mpID, $marketplace) { 
		parent::__construct($mpID, $marketplace);
		$this->confirmationResponseField = 'CONFIRMATIONS';
	}
	
	protected function addToErrorLog($error) {
		/*
		    [ERRORLEVEL] => FATAL
		    [SUBSYSTEM] => Tradoria
		    [APIACTION] => ConfirmShipment
		    [ERRORMESSAGE] => Der Parameter "order_no" besitzt einen ungültigen Wert
		    [DETAILS] => Array
		        (
		            [ErrorCode] => 41
		            [ErrorMessage] => Der Parameter "order_no" besitzt einen ungültigen Wert
		            [MOrderID] => 281-508-10X
		            [DateAdded] => 2013-07-21 09:50:00
		        )
		*/
		$add = $error['DETAILS'];
		$dateAdded = $add['DateAdded'];
		unset($add['ErrorMessage']);
		unset($add['DateAdded']);
		$add['TradoriaMethod'] = $error['APIACTION'];
		
		MagnaDB::gi()->insert(
			TABLE_MAGNA_COMPAT_ERRORLOG,
			array (
				'mpID' => $this->mpID,
				'errormessage' => $error['ERRORMESSAGE'],
				'dateadded' => $dateAdded,
				'additionaldata' => serialize($add)
			)
		);
	}

	/**
	 * Checks whether the status of the current order should be synchronized with
	 * the marketplace.
	 * @return bool
	 */
	protected function isProcessable() {
		return (in_array($this->oOrder['orders_status_shop'], $this->config['StatusShipped']) 
			|| ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']));
	}
	
	/**
	 * Builds an element for the ConfirmShipment request.
	 * @return array
	 */
	protected function confirmShipment($date) {
		$cfirm = array (
			'MOrderID' => $this->oOrder['special'],
			'ShippingDate' => localTimeToMagnaTime($date),
		);
		$this->oOrder['data']['ML_LABEL_SHIPPING_DATE'] = $cfirm['ShippingDate'];
		
		$trackercode = $this->getTrackingCode($this->oOrder['orders_id']);
		$carrier = $this->getCarrier($this->oOrder['orders_id']);
		if (false != $carrier) {
			$this->oOrder['data']['ML_LABEL_CARRIER'] = $cfirm['Carrier'] = $carrier;
		}
		if (false != $trackercode) {
			$this->oOrder['data']['ML_LABEL_TRACKINGCODE'] = $cfirm['TrackingCode'] = $trackercode;
		}

		// Place of Departure: Country, City, Street, Zip
		$iStatusNumber = array_search($this->oOrder['orders_status_shop'], $this->config['StatusShipped']);
		$aStatusCountries = getDBConfigValue('tradoria.orderstatus.address.country', $this->mpID, array());
		$aStatusCities    = getDBConfigValue('tradoria.orderstatus.address.city', $this->mpID, array());
		$aStatusStreets   = getDBConfigValue('tradoria.orderstatus.address.streetandnr', $this->mpID, array());
		$aStatusZips      = getDBConfigValue('tradoria.orderstatus.address.zip', $this->mpID, array());
		if (    array_key_exists($iStatusNumber, $aStatusCountries)
		     && !empty($aStatusCountries[$iStatusNumber])) {
			$this->oOrder['data']['Country'] = $cfirm['Country'] = $aStatusCountries[$iStatusNumber];
		}
		if (    array_key_exists($iStatusNumber, $aStatusCities)
		     && !empty($aStatusCities[$iStatusNumber])) {
			$this->oOrder['data']['City'] = $cfirm['City'] = $aStatusCities[$iStatusNumber];
		}
		if (    array_key_exists($iStatusNumber, $aStatusStreets)
		     && !empty($aStatusStreets[$iStatusNumber])) {
			$this->oOrder['data']['Street'] = $cfirm['Street'] = $aStatusStreets[$iStatusNumber];
		}
		if (    array_key_exists($iStatusNumber, $aStatusZips)
		     && !empty($aStatusZips[$iStatusNumber])) {
			$this->oOrder['data']['Zip'] = $cfirm['Zip'] = $aStatusZips[$iStatusNumber];
		}
		
		// flag order as dirty, meaning that it has to be saved.
		$this->oOrder['__dirty'] = true;
		return $cfirm;
	}
	
	/**
	 * Tries to get the timestamp of the first status change for Shipped status, else last status change.
	 * Returns now if it can not be determined.
	 * @return string
	 *   A mysql datetime
	 */
	protected function getStatusChangeTimestamp() {
		if (in_array($this->oOrder['orders_status_shop'], $this->config['StatusShipped'])) {
			$sSortOrder = 'ASC';
		} else {
			$sSortOrder = 'DESC';
		}
		$date = MagnaDB::gi()->fetchOne('
		    SELECT date_added
		      FROM `'.TABLE_ORDERS_STATUS_HISTORY.'`
		     WHERE orders_id='.$this->oOrder['orders_id'].'
		           AND orders_status_id = '.$this->oOrder['orders_status_shop'].'
		  ORDER BY date_added '.$sSortOrder.'
		     LIMIT 1
		');
		if ($date === false) {
			$date = date('Y-m-d H:i:s');
		}
		return $date;
	}
	
	/**
	 * Processes the current order.
	 * @return void
	 */
	protected function prepareSingleOrder($date) {
		if (in_array($this->oOrder['orders_status_shop'], $this->config['StatusShipped'])) {
			$this->confirmations[] = $this->confirmShipment($date);
		} else if ($this->oOrder['orders_status_shop'] == $this->config['StatusCancelled']) {
			$this->cancellations[] = $this->cancelOrder($date);
		}
		# Redmine Enhancement #834 !Hack!
		$this->oOrder['__dirty'] = false;
	}
}
