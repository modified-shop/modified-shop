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
 * $Id: VariationsCalculator.php 1214 2011-08-29 12:42:46Z MaW $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/* Variations-Tabelle aufbauen */

defined('TABLE_PRODUCTS_VARIATIONS') OR define('TABLE_PRODUCTS_VARIATIONS', TABLE_MAGNA_VARIATIONS);


class VariationsCalculator {
	var $settings = array();
	var $language;
	var $optionNames = array();
	var $optionValueNames = array();
	var $vpeNames = false;

	function __construct($settings = array(), $language = false) {
		$this->settings = array_merge(
			array (
				'stockmerge' => 'min', // [min, max, add]
				'skudivider' => '#',  //  String
				'skubasetype' => 'model', //  [model | id] --> id: options_id + options_values_id
				'skuvartype'  => 'id', //  [model | id] --> id: options_id + options_values_id
			),
			$settings
		);
		
		if (!isset($GLOBALS['SDB'])) {
			$GLOBALS['SDB'] = MagnaDB::gi();
		}
		
		$this->language = $language;
		if ($this->language !== false) {
			$this->language = $GLOBALS['SDB']->fetchOne('
				SELECT languages_id
			      FROM '.TABLE_LANGUAGES.'
			     WHERE code=\''.$GLOBALS['SDB']->escape($this->language).'\'
                 OR languages_id=\''.$GLOBALS['SDB']->escape($this->language).'\'
			');
		}
		if ($this->language === false) {
			if (defined('DEFAULT_LANGUAGE')) {
				$this->language = $GLOBALS['SDB']->fetchOne('
				    SELECT languages_id
				      FROM '.TABLE_LANGUAGES.'
				     WHERE code=\''.DEFAULT_LANGUAGE.'\'
				');
			} else {
				$this->language = $GLOBALS['SDB']->fetchOne('
				    SELECT languages_id
				      FROM '.TABLE_LANGUAGES.' l, '.TABLE_CONFIGURATION.' c
				     WHERE c.configuration_key = \'DEFAULT_LANGUAGE\'
				           AND c.configuration_value = l.code
				');
			}
		}
		if ($this->language === false) {
			$this->language = '2'; # The  u l t i m a t e  fallback.
		}
		
		$this->generateOptionNames();
		$this->generateVPENames();
	}

	function generateOptionNames() {
		$options = $GLOBALS['SDB']->fetchArray('
		    SELECT DISTINCT products_options_id, products_options_name 
		      FROM '.TABLE_PRODUCTS_OPTIONS.'
		     WHERE language_id = \''.$this->language.'\'
		  ORDER BY products_options_id
		');
		foreach ($options as $option) {
			$this->optionNames[$option['products_options_id']] = $option['products_options_name'];
		}
		$optionValues = $GLOBALS['SDB']->fetchArray('
		    SELECT DISTINCT products_options_values_id, products_options_values_name
		      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
		     WHERE language_id = \''.$this->language.'\'
		  ORDER BY products_options_values_id
		');
		foreach ($optionValues as $row) {
			$this->optionValueNames[$row['products_options_values_id']] = $row['products_options_values_name'];
		}
	}

	function generateVPENames() {
		if (!$GLOBALS['SDB']->tableExists(TABLE_PRODUCTS_VPE)) {
			return;
		}
		$vpes = $GLOBALS['SDB']->fetchArray('
		    SELECT DISTINCT products_vpe_id, products_vpe_name
		      FROM '.TABLE_PRODUCTS_VPE.'
		     WHERE language_id = \''.$this->language.'\'
		  ORDER BY products_vpe_id
		');
		if (empty($vpes)) {
			return;
		}
		$this->vpeNames = array();
		foreach ($vpes as $vpe) {
			$this->vpeNames[$vpe['products_vpe_id']] = isset($vpe['products_vpe_name'])?$vpe['products_vpe_name']:'';
		}
	}
	
	function getVPENames() {
		return $this->vpeNames;
	}
	
	function nullToEmptyString(&$val) {
		return ($val === null) ? '' : $val;
	}
	
	function transformAttributes($attributes) {
		if (empty($attributes)) {
			return false;
		}
		$attrByOptionsID = array();
		foreach ($attributes as $attr) {
			$attrByOptionsID[$attr['options_id']][$attr['options_values_id']] = $attr;
		}
		return $attrByOptionsID;
	}

	function generateVariationMatrix($base, $attrByOptionsID) {
		if (empty($attrByOptionsID)) {
			return false;
		}

		$dimension = 1;
		foreach ($attrByOptionsID as $vID => $vector) {
			$dimension *= count($vector);
		}
		$std = array_merge(array (
			'products_id' => '',
			'variation_products_model' => '',
			'variation_ean' => '',
			'variation_attributes' => '',
			'variation_attributes_text' => '',
			'variation_quantity' => ($this->settings['stockmerge'] == 'min') ? 0xFFFFFF : 0,
			'variation_status' => '1',
			'variation_price' => 0,
			'variation_weight' => 0,
			'variation_shipping_time' => 1,
			'variation_volume' => 0,
			'variation_unit_of_measure' => '',
		), $base);
		$permutations = array_fill(0, $dimension, $std);
		//echo mp_print_r($attrByOptionsID, '$attrByOptionsID['.$dimension.']');

		// To avoid database errors since the variations table does not support NULL
		array_walk($base, array($this, 'nullToEmptyString')); 
		array_walk($attrByOptionsID, array($this, 'nullToEmptyString')); 

		$shift = $dimension;
		foreach ($attrByOptionsID as $oID => $vec) {
			$vecCount = count($vec);
			$offset = 0;
			$subdim = $shift;
			$shift /= $vecCount;

			$attrC = 0;
			foreach ($vec as $vID => $attr) {
				$i = 0;
				for ($j = 0, $js = $dimension / $vecCount; $j < $js; ++$j) {
					if (($j % $shift) == 0) {
						$offset = ($subdim * $i) + ($shift * $attrC) % $dimension;
						++$i;
					}
					$permutations[$offset]['variation_attributes'] .= $oID.','.$vID.'|';
					$permutations[$offset]['variation_attributes_text'] .= $this->optionNames[$oID].','.$this->optionValueNames[$vID].'|';
					$permutations[$offset]['variation_price'] += (float)$attr['options_values_price'] * ($attr['price_prefix'] == '+' ? 1 : -1);
					switch ($this->settings['stockmerge']) {
						case 'add': {
							$permutations[$offset]['variation_quantity'] += (int)$attr['attributes_stock'];
							break;
						}
						case 'max': {
							$permutations[$offset]['variation_quantity'] = max($permutations[$offset]['variation_quantity'], (int)$attr['attributes_stock']);
							break;
						}
						case 'min':
						default: {
							$permutations[$offset]['variation_quantity'] = min($permutations[$offset]['variation_quantity'], (int)$attr['attributes_stock']);
							break;
						}
					}
					switch ($this->settings['skuvartype']) {
						case 'model': {
							$tModel = trim(str_replace($base['variation_products_model'], '', $attr['attributes_model']));
							$tModel = trim($tModel, '_,.');
							if (empty($tModel)) {
								$tModel = '_'.$oID.'.'.$vID;
							}
							$permutations[$offset]['variation_products_model'] .= $tModel;
							break;
						}
						case 'id':
						default: {
							$permutations[$offset]['variation_products_model'] .= '_'.$oID.'.'.$vID;
						}
					}
					++$offset;
				}
				++$attrC;
			}
		}
		
		return $permutations;
	}
	
	function getBaseVariationsArray($pID) {
		$productQuery = '
		    SELECT products_model, products_weight';
		
		if ($GLOBALS['SDB']->columnExistsInTable('products_vpe_value', TABLE_PRODUCTS)) {
			$productQuery .= ', products_vpe, products_vpe_value';
		}
		$productQuery .= '
		      FROM '.TABLE_PRODUCTS.'
		     WHERE products_id='.$pID;

		$product = $GLOBALS['SDB']->fetchArray($productQuery);

		if (($this->settings['skubasetype'] == 'id') || empty($product[0]['products_model'])) {
			$product[0]['products_model'] = 'ML'.$pID;
		}
		if (!empty($this->vpeNames) && !empty($product[0]['products_vpe'])) {
			#return array (
            $ret = array (
				'products_id' => $pID,
				'variation_products_model' => $product[0]['products_model'],
				'variation_volume' => $product[0]['products_vpe_value'],
				'variation_unit_of_measure' => $this->vpeNames[$product[0]['products_vpe']],
			);
		} else {
			#return array (
            $ret = array (
				'products_id' => $pID,
				'variation_products_model' => $product[0]['products_model'],
				'variation_volume' => 0,
				'variation_unit_of_measure' => '',
			);
		}
		return $ret;
	}
	
	function getAttributesByPID($pID) {
		if ($GLOBALS['SDB']->columnExistsInTable('sortorder', TABLE_PRODUCTS_ATTRIBUTES)) {
			$attributesOrderBy = ' sortorder, options_id, options_values_id ';
		} else {
			$attributesOrderBy = ' options_id, options_values_id ';
		}
		$attributes = $GLOBALS['SDB']->fetchArray('
		    SELECT * 
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.'
		     WHERE products_id='.$pID.'
		  ORDER BY '.$attributesOrderBy.'
		');

        if (!$GLOBALS['SDB']->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)) {
            # keine Anzahl auf Varianten-Ebene => vom Stammartikel nehmen
            $attributesStock = (int)$GLOBALS['SDB']->fetchOne('
				SELECT products_quantity
				  FROM '.TABLE_PRODUCTS.'
				 WHERE products_id=\''.$pID.'\'
		    ');
            foreach ($attributes as &$row) {
                $row['attributes_stock'] = $attributesStock;
            }
        }

		$attrByOptionsID = $this->transformAttributes($attributes);
		#if (is_array($attrByOptionsID)) $attrByOptionsID = array_values($attrByOptionsID); 
		return $attrByOptionsID;
	}
	
	function getVariationsByPID($pID) {
		$matrix = $this->generateVariationMatrix(
			$this->getBaseVariationsArray($pID),
			$this->getAttributesByPID($pID)
		);
		return $matrix;
	}

	function purgeProductVariations($pID) {
		$permutations = $this->getVariationsByPID($pID);
		if (empty($permutations)) {
			return false;
		}
		$GLOBALS['SDB']->delete(TABLE_PRODUCTS_VARIATIONS, array (
			'products_id' => $pID
		));
		if ($GLOBALS['SDB']->batchinsert(TABLE_PRODUCTS_VARIATIONS, $permutations, true)) {
			return true;
		}
		return false;
	}

	function getVariationsByPIDFromDB($pID, $purge = false) {
		$q = '
			SELECT * FROM '.TABLE_PRODUCTS_VARIATIONS.'
			 WHERE products_id='.(int)$pID.'
		';
		if (!$purge) {
			$p = $GLOBALS['SDB']->fetchArray($q);
		} else {
			$p = false;
		}
		if (!is_array($p)) {
			$this->purgeProductVariations($pID);
		}
		$p = $GLOBALS['SDB']->fetchArray($q);
		if (!is_array($p)) {
			return false;
		}

		foreach ($p as &$attr) {
			unset($attr['products_id']);
			$va = explode('|', trim($attr['variation_attributes'], '|'));
			$attr['variation_attributes'] = array();
			$attr['variation_attributes_text'] = array();
			
			foreach ($va as $i) {
				$i = explode(',', $i);
				$attr['variation_attributes'][] = array(
					'Group' => $i[0],
					'Value' => $i[1],
				);
				if (isset($this->optionNames[$i[0]]) && isset($this->optionValueNames[$i[1]])) {
					$attr['variation_attributes_text'][] = array (
						'Group' => $this->optionNames[$i[0]],
						'Value' => $this->optionValueNames[$i[1]]
					);
				} else {
					$attr['variation_attributes_text'][] = false;
				}
			}
		}
		return $p;
	}

	function getProductVariationsTotalQuantity($pID, $minus = 0) {
		$quantity = 0;
		$permutations = $this->getVariationsByPID($pID);
		if (empty($permutations)) {
			return $quantity;
		}
		foreach ($permutations as $row) {
			$quantity += max(($row['variation_quantity'] - $minus), 0);
		}
		return $quantity;
	}

	function purgeVariationsTable() {
		$pIDs = MagnaDB::gi()->fetchArray('
			SELECT products_id
			  FROM '.TABLE_PRODUCTS.'
		  ORDER BY products_id ASC
		');
		foreach ($pIDs as $pID) {
			/* 60 seconds per product */
			@set_time_limit(60);
			$this->purgeProductVariations($pID);
		}
	}
}
