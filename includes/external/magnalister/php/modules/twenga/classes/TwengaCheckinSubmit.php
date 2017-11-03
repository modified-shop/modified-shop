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
 * $Id: TwengaCheckinSubmit.php 2179 2013-12-06 15:48:23Z tim.neumann $
 *
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCheckinSubmit.php');

class TwengaCheckinSubmit extends ComparisonShoppingCheckinSubmit {

	public function getcategoriesname($pID) {
		$catnames = array();
		$i = 0;

		// Maximale Kategorientiefe, bis zu der der Name der Ueberkategorie geholt wird. Kein von Twenga vorgegebener Wert, kann nach
		// persoenlichem Ermessen geaendert werden (aber nicht weglassen wg. moeglichem infinite loop!)
		$maxcatlevel = 4;

		$lang = (string)getDBConfigValue('twenga.lang', $this->mpID, 2);

		$catdata = MagnaDB::gi()->fetchRow('
			SELECT p.categories_id, c.parent_id
			  FROM '.TABLE_PRODUCTS_TO_CATEGORIES.' p
			  JOIN categories c ON p.categories_id = c.categories_id
			 WHERE products_id = '.$pID.'
			 LIMIT 1
		');
		$parentid = $catdata['parent_id'];
		$catnames[] = $catdata['categories_id'];

		while (($parentid != 0) && ($i < $maxcatlevel)) {
			$catdata = MagnaDB::gi()->fetchRow('
				SELECT categories_id, parent_id
				  FROM categories
				 WHERE categories_id = '.$parentid.'
				 LIMIT 1
			');
			$catnames[] = $catdata['categories_id'];
			$parentid = $catdata['parent_id'];
			++$i;
		}
		$catstring = '';
		$catnames = array_reverse($catnames);
		foreach ($catnames as $value) {
			if (!empty($value)) {
				$cName = MagnaDB::gi()->fetchOne('
					SELECT categories_name
					  FROM categories_description
					 WHERE categories_id = '.$value.'
					AND language_id = "'.$lang.'"
					 LIMIT 1
				');
				if (empty($catstring)) {
					$catstring = $cName;
				} else {
					$catstring .= ' > '.$cName;
				}
			}
		}
		return $catstring;
	}

	protected function appendAdditionalData($pID, $product, &$data) {
		parent::appendAdditionalData($pID, $product, $data);
		$catname = $this->getcategoriesname($product['ProductId']);
		if (!empty($catname)) {
			$data['submit']['MerchantCategory'] = $catname;
		}
	}
}
