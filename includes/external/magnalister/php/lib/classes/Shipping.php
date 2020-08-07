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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 *
 * Made compartible with osCommerce. Usable in admin area.
 *
 */

/* -----------------------------------------------------------------------------------------
   $ shipping.php 1305 2005-10-14 10:30:03Z mz $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(shipping.php,v 1.22 2003/05/08); www.oscommerce.com 
   (c) 2003	 nextcommerce (shipping.php,v 1.9 2003/08/17); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

defined('DIR_MAGNA_LANGUAGES') or define('DIR_MAGNA_LANGUAGES', DIR_FS_LANGUAGES);
class FakeOrder {
	public $delivery = array();
	
	public function __construct($countryID) {
		$this->delivery['country'] = MagnaDB::gi()->fetchRow(
			'SELECT countries_id AS id, 
					countries_name AS title,
					countries_iso_code_2 AS iso_code_2, 
					countries_iso_code_3 AS iso_code_3 
			   FROM '.TABLE_COUNTRIES.' 
			  WHERE countries_id=\''.MagnaDB::gi()->escape($countryID).'\''
		);
	}
}

class Shipping {
	private $modules;
	private $instances = array();
	private $modules_info;

	private $settings = array();
	
	// class constructor
	public function __construct($module = '') {
		
		if (SHOPSYSTEM == 'oscommerce') {
			global $language;
			if (defined('DIR_FS_CATALOG_LANGUAGES'))
				$langPath = DIR_FS_CATALOG_LANGUAGES . $language . '/modules/shipping/';
			else
				$langPath = DIR_MAGNA_LANGUAGES . $language . '/modules/shipping/';
		} else {
			# Kann passieren, dass einige Shipping-Module Preisberechnungen machen und dafuer die xtcPrice-Klasse verwenden.
			global $xtPrice;
			if ($xtPrice == null) {
				if (!class_exists('xtcPrice') && file_exists(DIR_FS_DOCUMENT_ROOT.DIR_WS_CLASSES.'xtcPrice.php')) {
					$this->requireOnceOB(DIR_FS_DOCUMENT_ROOT.DIR_WS_CLASSES.'xtcPrice.php');
				}
				if (class_exists('xtcPrice')) {
					$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
				}
			}
			if (defined('DIR_FS_LANGUAGES'))
				$langPath = DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/';
			else
				$langPath = DIR_MAGNA_LANGUAGES . $_SESSION['language'] . '/modules/shipping/';
		}
		if (defined('DIR_FS_CATALOG_MODULES'))
			$modulePath = DIR_FS_CATALOG_MODULES . 'shipping/';
		else
			$modulePath = DIR_MAGNA_MODULES . 'shipping/';
		
		//echo var_dump_pre($order);
		if (defined('MODULE_SHIPPING_INSTALLED') && (MODULE_SHIPPING_INSTALLED != '')) {
			$this->modules = explode(';', MODULE_SHIPPING_INSTALLED);
			$include_modules = array();

			foreach ($this->modules as $value) {
				$class = substr($value, 0, strrpos($value, '.'));
				$include_modules[] = array(
					'class' => $class,
					'file' => $value
				);
			}

			for ($i = 0, $n = sizeof($include_modules); $i < $n; $i++) {
				// check if zone is alowed to see module
				$const = 'MODULE_SHIPPING_'.strtoupper(str_replace('.php', '', $include_modules[$i]['file'])).'_ALLOWED';
				if (defined($const) && constant($const) != '') {
					$allowed_zones = explode(',', constant($const));
				} else {
					$allowed_zones = array();
				}
				if ((array_key_exists('delivery_zone', $_SESSION) && in_array($_SESSION['delivery_zone'], $allowed_zones))
					|| (count($allowed_zones) == 0)
				) {
					if (!class_exists($include_modules[$i]['class'])) {
						mlLoadModuleLanguageDefines($langPath.$include_modules[$i]['file']);
						$this->includeOB($modulePath . $include_modules[$i]['file']);
					}
					if (class_exists($include_modules[$i]['class'])) {
						$this->instances[$include_modules[$i]['class']] = new $include_modules[$i]['class'];
					}
				}
			}
		}

		if (!empty($this->instances)) {
			foreach ($this->instances as $module) {
				$this->modules_info[] = array(
					'code' => $module->code,
					'title' => $module->title,
					'description' => $module->description,
					'status' => $module->check(),
					'signature' => (isset($module->signature) ? $module->signature : null)
				);
			}
		}
	}
	
	private function includeOB($path) {
		if (!file_exists($path)) return;
		ob_start();
		include($path);
		ob_end_clean();
	}
	
	private function requireOnceOB($path) {
		if (!file_exists($path)) return;
		ob_start();
		require_once($path);
		ob_end_clean();
	}
	
	public function getShippingMethods() {
		return $this->modules_info;
	}
	
	public function methodExists($method) {
		if (empty($this->modules_info)) return false;
		foreach ($this->modules_info as $mod) {
			if ($mod['code'] == $method) {
				return true;
			}
		}
		return false;
	}
	
	public function configure($settings) {
		global $order; /* shop */

		$this->settings = $settings;

		$order = new FakeOrder($this->settings['shippingCountry']);
	}

	public function getShippingCost($weight, $shippingDefault = false) {
		global $total_weight; /* shop */
		$total_weight = $weight;
		
		if ($this->settings['prefferedMethod'] == '__ml_lump') {
			if (isset($this->settings['fallback'])) {
				return $this->settings['fallback'];
			}
			return false;
		
		} else if ($this->settings['prefferedMethod'] == '__ml_gambio') {
			return (float)$shippingDefault;
		
		} else if ($this->settings['prefferedMethod'] == '__ml_weight') {
			return (float)$weight;
			
		}
		
		$this->quote($this->settings['prefferedMethod']);
		$quotes = $this->instances[$this->settings['prefferedMethod']]->quotes;
		
		$cheapest = false;
		if (is_array($quotes) && array_key_exists('methods', $quotes)) {
			foreach ($quotes['methods'] as $rate) {
				if (is_array($cheapest)) {
					if ($rate['cost'] < $cheapest['cost']) {
						$cheapest = $rate;
					}
				} else {
					$cheapest = $rate;
				}
			}
		}
		if (is_array($cheapest)) {
			if ($cheapest['id'] == 'gambioultra') {
				return (float)$shippingDefault;
			}
			return $cheapest['cost'];
		}
		if (isset($this->settings['fallback'])) {
			return $this->settings['fallback'];
		}
		return false;

	}
	
	public function quote($method = '', $module = '') {
		global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;
		
		$quotes_array = array();
		
		if (is_array($this->modules)) {
			$shipping_quoted    = '';
			$shipping_num_boxes = 1;
			$shipping_weight    = $total_weight;
			
			if (SHIPPING_BOX_WEIGHT >= $shipping_weight * SHIPPING_BOX_PADDING / 100) {
				$shipping_weight = $shipping_weight + SHIPPING_BOX_WEIGHT;
			} else {
				$shipping_weight = $shipping_weight + ($shipping_weight * SHIPPING_BOX_PADDING / 100);
			}
			
			if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes
				$shipping_num_boxes = ceil($shipping_weight / SHIPPING_MAX_WEIGHT);
				$shipping_weight    = $shipping_weight / $shipping_num_boxes;
			}
			
			$include_quotes = array();

            foreach ($this->modules as $value) {
				$class = substr($value, 0, strrpos($value, '.'));
				if (!empty($module)) {
					if (($module == $class) && ($this->instances[$class]->enabled)) {
						$include_quotes[] = $class;
					}
				} else if ($this->instances[$class]->enabled) {
					$include_quotes[] = $class;
				}
			}
			
			$size = sizeof($include_quotes);
			for ($i = 0; $i < $size; $i++) {
				$quotes = $this->instances[$include_quotes[$i]]->quote($method);
				if (is_array($quotes))
					$quotes_array[] = $quotes;
			}
		}
		
		return $quotes_array;
	}
	
	public function cheapest() {
		if (!empty($this->instances)) {
			foreach ($this->instances as $module) {
				if ($module->enabled) {
					$quotes = $module->quotes;
					$size   = sizeof($quotes['methods']);
					for ($i = 0; $i < $size; $i++) {
						if (array_key_exists("cost", $quotes['methods'][$i])) {
							$rates[] = array(
								'id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
								'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
								'cost' => $quotes['methods'][$i]['cost']
							);
							// echo $quotes['methods'][$i]['cost'];
							
						}
					}
				}
			}
			$cheapest = false;
			$size = sizeof($rates);
			for ($i = 0; $i < $size; $i++) {
				if (is_array($cheapest)) {
					if ($rates[$i]['cost'] < $cheapest['cost']) {
						$cheapest = $rates[$i];
					}
				} else {
					$cheapest = $rates[$i];
				}
			}
			return $cheapest;
		}
		return false;
	}
}
