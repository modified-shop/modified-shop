<?php
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/MarketplaceCategoryMatching.php');

class TemuCategoryMatching extends MarketplaceCategoryMatching {

	protected function getTableName() {
		return TABLE_MAGNA_TEMU_CATEGORIES;
	}

	/**
	 * Override to use flat GetCategories API (v3 pattern) instead of recursive GetChildCategories.
	 *
	 * All categories are imported at once on first load or reload (purge).
	 * Child expansion simply queries the already-imported local DB.
	 */
	protected function getMPCategories($parentID = 0, $purge = false) {
		if ($purge) {
			// Reload button clicked — clear and reimport all
			$this->importAllCategories();
		}

		$validTo = gmdate('Y-m-d H:i:s', time() - $this->getCategoryValidityPeriod());

		$mpCategories = MagnaDB::gi()->fetchArray('
			SELECT DISTINCT CategoryID, CategoryName,
			       ParentID, LeafCategory, Selectable
			  FROM '.$this->getTableName().'
			 WHERE ParentID="'.MagnaDB::gi()->escape($parentID).'"
			       AND mpID="0"
			       AND InsertTimestamp > "'.$validTo.'"
			 ORDER BY CategoryName ASC
		');

		// First load: no categories in DB yet — import once
		if (empty($mpCategories)) {
			$hasAny = MagnaDB::gi()->fetchOne('
				SELECT COUNT(*) FROM '.$this->getTableName().'
				WHERE mpID="0" AND InsertTimestamp > "'.$validTo.'"
			');
			if (empty($hasAny) && $this->importAllCategories()) {
				$mpCategories = MagnaDB::gi()->fetchArray('
					SELECT DISTINCT CategoryID, CategoryName,
					       ParentID, LeafCategory, Selectable
					  FROM '.$this->getTableName().'
					 WHERE ParentID="'.MagnaDB::gi()->escape($parentID).'"
					       AND mpID="0"
					 ORDER BY CategoryName ASC
				');
			}
		}

		return empty($mpCategories) ? false : $mpCategories;
	}

	/**
	 * Import all Temu categories using GetCategories API with pagination (v3 pattern).
	 */
	protected function importAllCategories() {
		MagnaDB::gi()->delete($this->getTableName(), array('mpID' => '0'));

		$offset = 0;
		$limit = 500;
		$now = gmdate('Y-m-d H:i:s');
		$totalImported = 0;

		do {
			try {
				$result = MagnaConnector::gi()->submitRequest(array(
					'ACTION' => 'GetCategories',
					'MODE' => 'GetCategories',
					'OFFSET' => $offset,
					'LIMIT' => $limit,
				));
			} catch (MagnaException $e) {
				return $totalImported > 0;
			}

			if (!isset($result['DATA']) || !is_array($result['DATA']) || empty($result['DATA'])) {
				break;
			}

			$totalCount = isset($result['NUMBEROFLISTINGS']) ? (int)$result['NUMBEROFLISTINGS'] : 0;

			foreach ($result['DATA'] as $cat) {
				$row = array(
					'CategoryID' => isset($cat['CategoryID']) ? $cat['CategoryID'] : '',
					'SiteID' => isset($cat['SiteID']) ? (int)$cat['SiteID'] : 0,
					'CategoryName' => isset($cat['CategoryName']) ? $cat['CategoryName'] : '',
					'CategoryPath' => isset($cat['CategoryPath']) ? $cat['CategoryPath'] : '',
					'CategoryLevel' => isset($cat['CategoryLevel']) ? (int)$cat['CategoryLevel'] : 1,
					'ParentID' => isset($cat['ParentID']) ? $cat['ParentID'] : '0',
					'LeafCategory' => isset($cat['LeafCategory']) ? (int)$cat['LeafCategory'] : 1,
					'Selectable' => isset($cat['LeafCategory']) && $cat['LeafCategory'] == '1' ? 1 : 0,
					'CatType' => isset($cat['CatType']) ? $cat['CatType'] : null,
					'AvailableStatus' => isset($cat['AvailableStatus']) ? $cat['AvailableStatus'] : null,
					'mpID' => 0,
					'InsertTimestamp' => $now,
				);

				if ($row['ParentID'] == $row['CategoryID']) {
					$row['ParentID'] = '0';
				}

				MagnaDB::gi()->insert($this->getTableName(), $row, true);
				$totalImported++;
			}

			$offset += $limit;
		} while ($offset < $totalCount);

		return $totalImported > 0;
	}

	/**
	 * Build full category path by traversing parents.
	 */
	public function getMPCategoryPath($categoryId) {
		$parts = array();
		$currentId = $categoryId;
		$maxDepth = 20;
		while ($currentId && $currentId != '0' && $maxDepth-- > 0) {
			$row = MagnaDB::gi()->fetchRow('
				SELECT CategoryID, CategoryName, ParentID
				  FROM '.$this->getTableName().'
				 WHERE CategoryID = "'.MagnaDB::gi()->escape($currentId).'"
			');
			if (!$row) break;
			array_unshift($parts, $row['CategoryName']);
			$currentId = $row['ParentID'];
		}
		if (empty($parts)) {
			return '<span class="invalid">'.$categoryId.'</span>';
		}
		return implode(' &gt; ', $parts);
	}
}
