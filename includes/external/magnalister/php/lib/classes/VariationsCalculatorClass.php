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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
/* Variations-Tabelle aufbauen */

defined('TABLE_PRODUCTS_VARIATIONS') OR define('TABLE_PRODUCTS_VARIATIONS', TABLE_MAGNA_VARIATIONS);


class VariationsCalculator {
	const MAX_PERMUTATIONS = 500;
	
	var $settings = array();
	var $optionsWhitelist = array();
	
	public function __construct($settings = array()) {
		$this->settings = array_merge(
			array (
				'stockmerge' => 'min', // [min, max, add]
				'skudivider' => '#',  //  String
			),
			$settings
		);
		
		if (!isset($GLOBALS['SDB'])) {
			$GLOBALS['SDB'] = MagnaDB::gi();
		}
	}
	
	public function nullToEmptyString(&$val) {
		return ($val === null) ? '' : $val;
	}
	
	public function setOptionsWhitelist($list) {
		if (is_array($list)) {
			$this->optionsWhitelist = $list;
		} else {
			$this->optionsWhitelist = array();
		}
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
		#echo print_m($attrByOptionsID, '$attrByOptionsID');
		
		$dimensions = count($attrByOptionsID);
		#echo print_m($dimensions, '$dimensions')."\n";
		
		$permutationsCount = 1;
		foreach ($attrByOptionsID as $vID => $vector) {
			$permutationsCount *= count($vector);
		}
		
		#echo print_m($permutationsCount, '$permutationsCount')."\n";
		if ($permutationsCount > self::MAX_PERMUTATIONS) {
			return false;
		}

		$fBaseProductPrice = $base['products_price'];
		$fBaseProductWeight = $base['products_weight'];
		unset($base['products_price']);
		unset($base['products_weight']);

		$std = array_merge(array (
			'products_id' => '',
			'products_sku' => '',
			'marketplace_id' => '',
			'marketplace_sku' => '',
			'variation_products_model' => '',
			'variation_ean' => '',
			'variation_attributes' => '|',
			'variation_quantity' => ($this->settings['stockmerge'] == 'min') ? 0xFFFFFF : 0,
			'variation_status' => '1',
			'variation_price' => 0,
			'variation_weight' => 0,
			'variation_shipping_time' => 1,
			'variation_volume' => 0,
			'variation_unit_of_measure' => '',
            'date_added' => date('Y-m-d H:i:s'),
		), $base);
		$permutations = array_fill(0, $permutationsCount, $std);
		//echo print_m($attrByOptionsID, '$attrByOptionsID['.$permutationsCount.']');

		// To avoid database errors since the variations table does not support NULL
		array_walk($base, array($this, 'nullToEmptyString'));
		array_walk($attrByOptionsID, array($this, 'nullToEmptyString'));

		$shift = $permutationsCount;
		foreach ($attrByOptionsID as $oID => $vec) {
			$vecCount = count($vec);
			$offset = 0;
			$subdim = $shift;
			$shift /= $vecCount;

			$attrC = 0;
			foreach ($vec as $vID => $attr) {
				$i = 0;
				for ($j = 0, $js = $permutationsCount / $vecCount; $j < $js; ++$j) {
					if (($j % $shift) == 0) {
						$offset = ($subdim * $i) + ($shift * $attrC) % $permutationsCount;
						++$i;
					}
					$permutations[$offset]['variation_attributes'] .= $oID.','.$vID.'|';
					if ($dimensions === 1 && $attr['price_prefix'] == '=') {
						$permutations[$offset]['variation_price'] += (float)$attr['options_values_price'] - (float)$fBaseProductPrice;
					} else if ('%' == $attr['price_prefix']) {
						 $permutations[$offset]['variation_price'] += (float)(($fBaseProductPrice * $attr['options_values_price']) / 100.0);
					} else {
						$permutations[$offset]['variation_price'] += (float)$attr['options_values_price'] * ($attr['price_prefix'] == '+' ? 1 : -1);
					}

					if (isset($attr['options_values_weight'])) {
						if ($dimensions === 1 && $attr['weight_prefix'] == '=') {
							$permutations[$offset]['variation_weight'] += (float)$attr['options_values_weight'] - (float)$fBaseProductWeight;
						} else {
							$permutations[$offset]['variation_weight'] += (float)$attr['options_values_weight'] * ($attr['weight_prefix'] == '+' ? 1 : -1);
						}
					}
					
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

                    // Check if the base code exists at the beginning of the attributes_model string
                    if (strpos($attr['attributes_model'], $base['marketplace_sku']) === 0) {
                        // Extract the part of the SKU after the base code
                        $tModel = substr($attr['attributes_model'], strlen($base['marketplace_sku']));

                        // Special handling for cases where the extracted part is empty
                        if ($tModel === '') {
                            $tModel = $base['marketplace_sku'];
                        }
                        // Check if the extracted part is numeric and less than x digits
                        elseif (ctype_digit($tModel) && strlen($tModel) < strlen($base['marketplace_sku'])) {
                            $tModel = $base['marketplace_sku'] . $tModel;
                        }
                    } else {
                        // If base code is not found at the beginning, use the entire SKU
                        $tModel = $attr['attributes_model'];
                    }

					$tModel = str_replace(array("\n", "\r", "\t" , ' '), array('', '', '', ''), $tModel);
					if (empty($tModel)) {
						$tModel = '_'.$oID.'.'.$vID;
					}
					$permutations[$offset]['variation_products_model'] .= $tModel;
					$permutations[$offset]['marketplace_sku'] .= $tModel;
					$permutations[$offset]['marketplace_id'] .= '_'.$oID.'.'.$vID;
					
					if (empty($permutations[$offset]['variation_ean'])) {
						$permutations[$offset]['variation_ean'] = isset($attr['attributes_ean'])
							? $attr['attributes_ean']
							: (isset($attr['gm_ean'])
								? $attr['gm_ean']
								: ''
							);
					}
					
					++$offset;
				}
				++$attrC;
			}
		}
		foreach ($permutations as &$permutation) {
			$permutation['marketplace_sku'] = trim($permutation['marketplace_sku'], " _,.");
		}
		unset($permutation);
		
		#echo print_m($permutations, '$permutations');
		
		return $permutations;
	}
	
	function getBaseVariationsArray($pID) {
		$productQuery = '
		    SELECT products_model, products_weight, products_price';
		
		if ($GLOBALS['SDB']->columnExistsInTable('products_vpe_value', TABLE_PRODUCTS)) {
			$productQuery .= ', products_vpe, products_vpe_value';
		}
		$productQuery .= '
		      FROM '.TABLE_PRODUCTS.'
		     WHERE products_id='.$pID;
		
		$product = $GLOBALS['SDB']->fetchArray($productQuery);
		
		if (empty($product[0]['products_model'])) {
			$product[0]['products_model'] = 'ML'.$pID;
		}
		$ret = array (
			'products_id' => $pID,
			'products_sku' => $product[0]['products_model'],
			'marketplace_id' => 'ML'.$pID,
			'marketplace_sku' => $product[0]['products_model'],
			'products_price' => $product[0]['products_price'],
			'products_weight' => $product[0]['products_weight'],
			'variation_products_model' => $product[0]['products_model'],
			'variation_volume' => 0,
			'variation_unit_of_measure' => '',
		);
		if (!empty($product[0]['products_vpe'])) {
			$ret['variation_volume'] = $product[0]['products_vpe_value'];
			$ret['variation_unit_of_measure'] = $product[0]['products_vpe'];
		}
		return $ret;
	}
	
	function getAttributesByPID($pID) {
		if ($GLOBALS['SDB']->columnExistsInTable('sortorder', TABLE_PRODUCTS_ATTRIBUTES)) {
			$attributesOrderBy = ' a.sortorder, a.options_id, a.options_values_id ';
		} else {
			$attributesOrderBy = ' a.options_id, a.options_values_id ';
		}
		if (!empty ($this->optionsWhitelist)) {
			$optConstr = 'AND a.options_id IN ("'.implode('", "', $this->optionsWhitelist).'")';
		} else {
			$optConstr = '';
		}
		$attributes = $GLOBALS['SDB']->fetchArray('
		    SELECT DISTINCT a.* 
		      FROM '.TABLE_PRODUCTS_ATTRIBUTES.' a
		      INNER JOIN '.TABLE_PRODUCTS_OPTIONS.' o on a.options_id = o.products_options_id
		      INNER JOIN '.TABLE_PRODUCTS_OPTIONS_VALUES.' v ON a.options_values_id = v.products_options_values_id
		     WHERE a.products_id='.$pID.'
		           '.$optConstr.'
		  ORDER BY '.$attributesOrderBy.'
		');
		
		if (!$GLOBALS['SDB']->columnExistsInTable('attributes_stock', TABLE_PRODUCTS_ATTRIBUTES)) {
			# keine Anzahl auf Varianten-Ebene => vom Stammartikel nehmen
			$attributesStock = (int)$GLOBALS['SDB']->fetchOne('
				SELECT products_quantity
				  FROM '.TABLE_PRODUCTS.'
				 WHERE products_id="'.$pID.'"
			');
			foreach ($attributes as &$row) {
				$row['attributes_stock'] = $attributesStock;
			}
		}
			
		$attrByOptionsID = $this->transformAttributes($attributes);
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
		$GLOBALS['SDB']->delete(TABLE_PRODUCTS_VARIATIONS, array (
			'products_id' => $pID
		));
		if (empty($permutations)) {
			return false;
		}
		if ($GLOBALS['SDB']->batchinsert(TABLE_PRODUCTS_VARIATIONS, $permutations, true)) {
			return true;
		}
		return false;
	}

	public function getVariationsByPIDFromDB($pID, $purge = false, $language = false) {
		$q = '
			SELECT * FROM '.TABLE_PRODUCTS_VARIATIONS.'
			 WHERE products_id='.(int)$pID.'
			ORDER BY variation_id
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

		if ($language <> false)  {
			$optionNames      = $this->generateOptionNames($language);
			$optionValueNames = $this->generateOptionValueNames($language);
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
				if (   ($language <> false) 
					 && isset($optionNames[$i[0]])
				     && isset($optionValueNames[$i[1]])
				   ) {
					$attr['variation_attributes_text'][] = array (
						'Group' => $optionNames[$i[0]],
						'Value' => $optionValueNames[$i[1]]
					);
				} else {
					$attr['variation_attributes_text'][] = false;
				}
			}
		}
		return $p;
	}

	public function getProductVariationsTotalQuantity($pID, $minus = 0) {
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

	public function purgeVariationsTable() {
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

	public static function generateOptionNames($language_id) {
		$optionNames = array();
		$options = MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT products_options_id, products_options_name 
		      FROM '.TABLE_PRODUCTS_OPTIONS.'
		     WHERE language_id = "'.$language_id.'"
		  ORDER BY products_options_id
		');
		foreach ($options as $option) {
			$optionNames[$option['products_options_id']] = $option['products_options_name'];
		}
		return $optionNames;
	}

	public static function generateOptionValueNames($language_id) {
		$optionValueNames = array();
		$optionValues = MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT products_options_values_id, products_options_values_name
		      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
		     WHERE language_id = "'.$language_id.'"
		  ORDER BY products_options_values_id
		');
		foreach ($optionValues as $row) {
			$optionValueNames[$row['products_options_values_id']] = $row['products_options_values_name'];
		}
		return $optionValueNames;
	}

	public static function generateVPENames($language_id) {
		if (  (!defined('TABLE_PRODUCTS_VPE'))
		    ||(!MagnaDB::gi()->tableExists(TABLE_PRODUCTS_VPE))) {
			return null;
		}
		$vpes = MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT products_vpe_id, products_vpe_name
		      FROM '.TABLE_PRODUCTS_VPE.'
		     WHERE language_id = "'.$language_id.'"
		  ORDER BY products_vpe_id
		');
		if (empty($vpes)) {
			return;
		}
		$vpeNames = array();
		foreach ($vpes as $vpe) {
			$vpeNames[$vpe['products_vpe_id']] = isset($vpe['products_vpe_name'])
				? $vpe['products_vpe_name']
				: '';
		}
		return $vpeNames;
	}

	public static function generateVariationsAttributesText($attrArr, $language_id, $groupSeparator = '|', $singleSeparator = ',') {
		if (empty($attrArr)) {
			return '';
		}
		
		$attrArr = explode('|', trim($attrArr, '|'));
		
		$opts = array();
		$optVals = array();
		
		foreach ($attrArr as $i => $att) {
			$attrArr[$i] = explode(',', $att);
			$opts[] = $attrArr[$i][0];
			$optVals[] = $attrArr[$i][1];
		}

		$optNames =  MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT products_options_id, products_options_name
		      FROM '.TABLE_PRODUCTS_OPTIONS.'
		     WHERE language_id = "'.$language_id.'"
		      AND products_options_id IN ("'.implode('", "', $opts).'")
		');
		$optN = array();
		foreach ($optNames as $optName) {
			$optN[$optName['products_options_id']] = $optName['products_options_name'];
		}
		
		$optValNames =  MagnaDB::gi()->fetchArray('
		    SELECT DISTINCT products_options_values_id, products_options_values_name
		      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
		     WHERE language_id = "'.$language_id.'"
		      AND products_options_values_id IN ("'.implode('", "', $optVals).'")
		');
		$optVN = array();
		foreach ($optValNames as $optValName) {
			$optVN[$optValName['products_options_values_id']] = $optValName['products_options_values_name'];
		}
		
		$variationAttributesText = '';
		foreach ($attrArr as $att) {
			$variationAttributesText .= $optN[$att[0]]  . $singleSeparator . $optVN[$att[1]] . $groupSeparator;
		}
		
		$variationAttributesText = rtrim($variationAttributesText, $groupSeparator);
		
		return $variationAttributesText;
	}
	
}
