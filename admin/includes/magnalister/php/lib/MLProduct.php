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
 * (c) 2010 - 2013 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

class MLProduct {
	private static $instance = null;
	
	private function __construct() {
		
	}
	
	/**
	 * Singleton - gets Instance
	 */
	public static function gi() {
		if (self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function getProductById($pID, $languages_id = false, $addQuery = '') {
		$lIDs = MagnaDB::gi()->fetchArray('
			SELECT language_id FROM '.TABLE_PRODUCTS_DESCRIPTION.' WHERE products_id=\''.$pID.'\'
		', true);

		if ($languages_id === false) {
			$languages_id = $_SESSION['languages_id'];
		}
		
		if (!empty($lIDs) && !in_array($languages_id, $lIDs)) {
			$languages_id = array_shift($lIDs);
		}

		if (is_array($pID)) {
			$where = 'p.products_id IN (\''.implode('\', \'',  $pID).'\')';
		} else {
			$where = 'p.products_id = \''.(int) $pID.'\'';
		}

		$products = MagnaDB::gi()->fetchArray('
			SELECT *, date_format(p.products_date_available, \'%Y-%m-%d\') AS products_date_available 
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE '.$where.'
			       AND p.products_id = pd.products_id
			       AND pd.language_id = \''.$languages_id.'\'
			   '.$addQuery.'
		');

		if (!is_array($products) || empty($products)) return false;

		$finalProducts = array();
		foreach ($products as &$product) {
			if (SHOPSYSTEM == 'gambio') {
				$product['products_description'] = preg_replace('/\[TAB:[^\]]+\]/', '', $product['products_description']);
			}
			if ($product['products_image']) {
				$product['products_allimages'] = array($product['products_image']);
			} else {
				$product['products_allimages'] = array();
			}
			if (MagnaDB::gi()->tableExists(TABLE_PRODUCTS_IMAGES)) {
				$cols = MagnaDB::gi()->getTableCols(TABLE_PRODUCTS_IMAGES);
				$orderBy = (in_array('image_nr', $cols) 
					? 'image_nr' 
					: (in_array('sort_order', $cols) 
						? 'sort_order' 
						: ''
					)
				);
				if (!empty($orderBy)) {
					$orderBy = 'ORDER BY '.$orderBy;
				}
				$colname = (in_array('image', $cols) 
					? 'image' 
					: (in_array('image_name', $cols) 
						? 'image_name' 
						: ''
					)
				);
				if (!empty($colname)) {
					$product['products_allimages'] = array_merge(
						$product['products_allimages'],
						(array)MagnaDB::gi()->fetchArray('
							SELECT '.$colname.'
							  FROM '.TABLE_PRODUCTS_IMAGES.'
							 WHERE products_id = \''.$product['products_id'].'\'
						  '.$orderBy.'
						', true)
					);
				}
			}
			if (isset($product['products_head_keywords_tag'])) {
				$product['products_meta_keywords'] = $product['products_head_keywords_tag'];
				unset($product['products_head_keywords_tag']);
			}
			if (isset($product['products_head_desc_tag'])) {
				$product['products_meta_description'] = $product['products_head_desc_tag'];
				unset($product['products_head_desc_tag']);
			}
			if (isset($product['products_vpe'])
			    && isset($product['products_vpe_value'])
			    && MagnaDB::gi()->tableExists(TABLE_PRODUCTS_VPE)
			) {
				$product['products_vpe_name'] = stringToUTF8(MagnaDB::gi()->fetchOne('
				    SELECT products_vpe_name 
				      FROM '.TABLE_PRODUCTS_VPE.'
				     WHERE products_vpe_id = \''.$product['products_vpe'].'\'
				           AND language_id = \''.$languages_id.'\'
				  ORDER BY products_vpe_id, language_id 
				     LIMIT 1
				'));
			}
			$finalProducts[$product['products_id']] = $product;
		}
		if (!is_array($pID)) {
			return $products[0];
		}
		unset($products);
		return $finalProducts;
	}
	
	public function getCategoryPath($id, $for = 'category', &$cPath = array()) {
		if ($for == 'product') {
			$cIDs = MagnaDB::gi()->fetchArray('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id=\''.MagnaDB::gi()->escape($id).'\'
			', true);
			if (empty($cIDs)) {
				return array();
			}
			$return = array();
			foreach ($cIDs as $cID) {
				if ((int)$cID == 0) {
					$return[] = array('0');
				} else {
					$cPath = $this->getCategoryPath($cID);
					array_unshift($cPath, $cID);
					$return[] = $cPath;
				}
			}
			return $return;
		} else {
			$meh = MagnaDB::gi()->fetchOne(
				'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE categories_id=\''.MagnaDB::gi()->escape($id).'\''
			);
			$cPath[] = (int)$meh;
			if ($meh != '0') {
				$this->getCategoryPath($meh, 'category', $cPath);
			}
			return $cPath;
		}
	}

	/* xt:Commerce Nachbildung */
	public function generateCategoryPath($id, $from = 'category', $categories_array = array(), $index = 0, $callCount = 0) {
		if ($from == 'product') {
			$categories_query = MagnaDB::gi()->query('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = \''.$id.'\'
			');
			while ($categories = MagnaDB::gi()->fetchNext($categories_query)) {
				if ($categories['categories_id'] == '0') {
					$categories_array[$index][] = array ('id' => '0', 'text' => ML_LABEL_CATEGORY_TOP);
				} else {
					$category_query = MagnaDB::gi()->query('
						SELECT cd.categories_name, c.parent_id 
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = \''.$categories['categories_id'].'\' 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = \''.$_SESSION['languages_id'].'\'
					');
					$category = MagnaDB::gi()->fetchNext($category_query);
					$categories_array[$index][] = array (
						'id' => $categories['categories_id'],
						'text' => $category['categories_name']
					);
					if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
						$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = MagnaDB::gi()->query('
				SELECT cd.categories_name, c.parent_id 
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = \''.$id.'\' 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = \''.$_SESSION['languages_id'].'\'
			');
			$category = MagnaDB::gi()->fetchNext($category_query);
			$categories_array[$index][] = array (
				'id' => $id,
				'text' => $category['categories_name']
			);
			if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
				$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
	
		return $categories_array;
	}
}
