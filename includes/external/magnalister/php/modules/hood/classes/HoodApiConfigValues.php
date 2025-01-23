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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/classes/MagnaCompatibleApiConfigValues.php');

class HoodApiConfigValues extends MagnaCompatibleApiConfigValues {
	protected static $instance = null;
	
	public static function gi() {
		// get_called_class() would be needed to kill that method and use the parent one.
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function getHasStore() {
		$data = $this->fetchDataFromApi('HasStore');
		return $data;
	}
	
	public function getShippingDetails() {
		$data = $this->fetchDataFromApi('GetShippingServiceDetails');
		return $data;
	}
	
	public function getShippingLocationsList() {
		$shippingDetails = $this->getShippingDetails();
		if (empty($shippingDetails)) {
			return false;
		}
		return $shippingDetails['ShippingLocations'];
	}
	
	public function getShippingLocationsListLocal() {
		$shippingDetails = $this->getShippingDetails();
		if (empty($shippingDetails)) {
			return false;
		}
		$servicesList = array();
		foreach ($shippingDetails['ShippingServices'] as $service => $serviceData) {
			if ('1' == $serviceData['InternationalService']) {
				continue;
			}
			$servicesList[$service] = $serviceData['Description'];
		}
		return $servicesList;
	}
	
	public function getShippingServicesListInternational() {
		$shippingDetails = $this->getShippingDetails();
		if (empty($shippingDetails)) {
			return false;
		}
		$servicesList = array('' => ML_HOOD_LABEL_NO_INTL_SHIPPING);
		foreach ($shippingDetails['ShippingServices'] as $service => $serviceData) {
			if ('0' == $serviceData['InternationalService']) {
				continue;
			}
			$servicesList[$service] = $serviceData['Description'];
		}
		return $servicesList;
	}
	
	public function getPaymentOptions() {
		$data = $this->fetchDataFromApi('GetPaymentOptions');
		return $data['PaymentOptions'];
	}
	
	/**
	 * @deprecated
	 */
	public function getHoodPaymentOptions() {
		return $this->getPaymentOptions();
	}
	
	public function getListingDurations() {
		$data = $this->fetchDataFromApi('GetListingDurations');
		return $data['ListingDurations'];
	}
	
	public function getFskOptions() {
		$data = $this->fetchDataFromApi('GetFskOptions');
		return $data['FskOptions'];
	}
	
	public function getUskOptions() {
		$data = $this->fetchDataFromApi('GetUskOptions');
		return $data['UskOptions'];
	}
    public function getVariantConfigurationDefinition($category, $secondaryCategory = null) {
        $apiCall = 'GetCategoryDetails';

        $requestParams = array(
            'DATA' => array(
                'CategoryID' => $category,
                'Language' => 'de',
            )
        );

        return $this->fetchDataFromApi($apiCall, $requestParams);
    }
}
