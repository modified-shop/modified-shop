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

/**
 * Reads and writes the per-product Temu attribute-matching JSON.
 *
 * The value is stored once per distinct content in magnalister_temu_longtext, addressed
 * by the SHA256 of that content, and the per-product prepare row holds the reference.
 * Products that share a category template therefore share a single row instead of each
 * carrying a full copy.
 *
 * Reading falls back to the legacy inline columns of magnalister_temu_prepare_longtext
 * whenever a product has no reference yet, so no data has to be converted up front; a
 * product moves over the next time it is written. This mirrors the mechanism the Amazon
 * tables use (magnalister_amazon_apply.DataId, see db/154.sql.php).
 */
class TemuLongtextStore {

	/** Category-dependent matching: variation dimensions and category attributes. */
	const FIELD_SHOP_VARIATION = 'ShopVariation';

	/** Category-independent matching, edited by its own React root. */
	const FIELD_CATEGORY_INDEPENDENT = 'CategoryIndependentShopVariation';

	/**
	 * Every Temu prepare row is written with PrepareType 'Apply'; the type exists on the
	 * key but is never varied. The reference columns live on that row, so the reads and
	 * writes here have to name it.
	 */
	const PREPARE_TYPE = 'Apply';

	/** Cached result of storeReady(); null until first checked. */
	protected static $bStoreReady = null;

	/**
	 * @param string $sField one of the FIELD_* constants
	 * @return string reference column on TABLE_MAGNA_TEMU_PREPARE
	 */
	protected static function referenceColumn($sField) {
		return ($sField === self::FIELD_CATEGORY_INDEPENDENT)
			? 'CategoryIndependentTextId'
			: 'ShopVariationTextId';
	}

	/**
	 * @param string $sField one of the FIELD_* constants
	 * @return string legacy inline column on TABLE_MAGNA_TEMU_PREPARE_LONGTEXT
	 */
	protected static function legacyColumn($sField) {
		return ($sField === self::FIELD_CATEGORY_INDEPENDENT)
			? 'CategoryIndependentShopVariationId'
			: 'ShopVariationId';
	}

	/**
	 * Whether the schema from db/163.sql.php is actually present.
	 *
	 * The plugin's files can arrive without the database update ever running - init.php only
	 * calls MagnaUpdater::updateDatabase() when the plugin's own updater just succeeded, in
	 * safe mode, on ?dbupdate=true, or when magnalister_config is missing. A shop updated by
	 * rsync or FTP therefore runs this code against the old schema, where every statement
	 * against the store fails silently (MagnaDB::query() returns false and does not throw).
	 * Without this guard the only statement that still succeeds is the one that CLEARS the
	 * legacy column, so a single read destroys the merchant's matching.
	 *
	 * @return bool
	 */
	protected static function storeReady() {
		if (self::$bStoreReady === null) {
			$oDB = MagnaDB::gi();
			self::$bStoreReady = $oDB->tableExists(TABLE_MAGNA_TEMU_LONGTEXT)
				&& $oDB->columnExistsInTable('ShopVariationTextId', TABLE_MAGNA_TEMU_PREPARE)
				&& $oDB->columnExistsInTable('CategoryIndependentTextId', TABLE_MAGNA_TEMU_PREPARE);
		}
		return self::$bStoreReady;
	}

	/**
	 * Copies a product's existing references into a prepare row that is about to be written.
	 *
	 * The prepare row is the ONLY place a reference lives, and both prepare paths replace it
	 * with DELETE + INSERT. A row rebuilt without these two columns detaches the stored
	 * matching: the reference is gone and the legacy column was already cleared when the value
	 * moved into the store, so the product reads as empty and the category template is seeded
	 * over the merchant's own matching. Call this before replacing the row.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sPrepareType PrepareType of the row being replaced
	 * @param array  $aRow         the insert data
	 * @return array the same data with any existing references carried over
	 */
	public static function carryReferences($mpID, $pID, $sPrepareType, $aRow) {
		$oDB = MagnaDB::gi();

		if (!self::storeReady()) {
			return $aRow;
		}

		$aExisting = $oDB->fetchRow("
			SELECT ShopVariationTextId, CategoryIndependentTextId
			  FROM ".TABLE_MAGNA_TEMU_PREPARE."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			       AND PrepareType = '".$oDB->escape($sPrepareType)."'
		");
		if (empty($aExisting)) {
			return $aRow;
		}

		// Only carry a reference the caller has not deliberately set itself.
		if (!isset($aRow['ShopVariationTextId']) && !empty($aExisting['ShopVariationTextId'])) {
			$aRow['ShopVariationTextId'] = $aExisting['ShopVariationTextId'];
		}
		if (!isset($aRow['CategoryIndependentTextId']) && !empty($aExisting['CategoryIndependentTextId'])) {
			$aRow['CategoryIndependentTextId'] = $aExisting['CategoryIndependentTextId'];
		}

		return $aRow;
	}

	/**
	 * Reads one field for one product, preferring the content store and falling back to
	 * the legacy inline column while the product carries no reference.
	 *
	 * NOT side-effect free: a value found in the legacy column is migrated into the store
	 * before it is returned, so reading is what converts the long tail of products nobody
	 * edits any more. The returned value is identical either way.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sField one of the FIELD_* constants
	 * @return string JSON, or '' when the product has nothing stored
	 */
	public static function read($mpID, $pID, $sField) {
		$oDB = MagnaDB::gi();

		$sTextId = !self::storeReady() ? '' : (string)$oDB->fetchOne("
			SELECT ".self::referenceColumn($sField)."
			  FROM ".TABLE_MAGNA_TEMU_PREPARE."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			       AND PrepareType = '".self::PREPARE_TYPE."'
		");

		if ($sTextId !== '') {
			$sValue = (string)$oDB->fetchOne("
				SELECT Value
				  FROM ".TABLE_MAGNA_TEMU_LONGTEXT."
				 WHERE TextId = '".$oDB->escape($sTextId)."'
				       AND ReferenceFieldName = '".$oDB->escape($sField)."'
			");
			if ($sValue !== '') {
				return $sValue;
			}
			// A reference that resolves to nothing means the store lost the row. Fall
			// through to the legacy column rather than reporting the product as empty.
		}

		// fetchArray, not fetchOne: idx_mp_product is not unique, so a product can already
		// carry duplicate legacy rows, and MagnaDB::fetchOne() returns false the moment a
		// query matches more than one — the product would read as empty. Take the first
		// non-empty value, in a deterministic order.
		$aLegacy = $oDB->fetchArray("
			SELECT ".self::legacyColumn($sField)."
			  FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			 ORDER BY id
		", true);
		$sLegacy = '';
		if (is_array($aLegacy)) {
			foreach ($aLegacy as $sCandidate) {
				if ((string)$sCandidate !== '') {
					$sLegacy = (string)$sCandidate;
					break;
				}
			}
		}

		// Reading from the legacy column is the trigger to move this field across. Products
		// that are never edited again would otherwise stay on the old table forever and the
		// table could never be dropped.
		if ($sLegacy !== '' && self::storeReady()) {
			self::migrateLegacyValue($mpID, $pID, $sField, $sLegacy);
		}

		return $sLegacy;
	}

	/**
	 * Moves one already-read legacy value into the content store and points the product's
	 * prepare row at it. The value is passed in rather than re-read, so this costs no extra
	 * SELECT on the read path.
	 *
	 * A product with no prepare row is left alone: the reference has nowhere to live, so its
	 * legacy column stays authoritative. Concurrent callers are harmless — the store INSERT
	 * is an INSERT IGNORE on the content hash and the reference UPDATE writes the same hash.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sField one of the FIELD_* constants
	 * @param string $sValue the value just read from the legacy column, non-empty
	 * @return void
	 */
	protected static function migrateLegacyValue($mpID, $pID, $sField, $sValue) {
		if (!self::prepareRowExists($mpID, $pID)) {
			return;
		}

		$oDB     = MagnaDB::gi();
		$sTextId = self::put($sValue, $sField);
		if ($sTextId === false) {
			return; // the value is not in the store, so the legacy column must stay
		}

		// Fill the reference ONLY while it is still empty. A save that ran between the read
		// above and this statement has already stored a newer value; overwriting its
		// reference with the hash of the stale one would silently revert the merchant's edit.
		$sColumn = self::referenceColumn($sField);
		$mResult = $oDB->query("
			UPDATE ".TABLE_MAGNA_TEMU_PREPARE."
			   SET ".$sColumn." = '".$oDB->escape($sTextId)."'
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			       AND PrepareType = '".self::PREPARE_TYPE."'
			       AND (".$sColumn." IS NULL OR ".$sColumn." = '')
		");
		if ($mResult === false || (int)$oDB->affectedRows() < 1) {
			return; // lost the race, or the statement failed - leave the legacy column alone
		}

		self::writeLegacy($mpID, $pID, $sField, '');
	}

	/**
	 * Reads both fields for one product in the same shape the legacy row had, so callers
	 * that used to SELECT * keep working.
	 *
	 * @param int $mpID
	 * @param int $pID
	 * @return array ShopVariationId and CategoryIndependentShopVariationId, both strings
	 */
	public static function readRow($mpID, $pID) {
		return array(
			'ShopVariationId'                    => self::read($mpID, $pID, self::FIELD_SHOP_VARIATION),
			'CategoryIndependentShopVariationId' => self::read($mpID, $pID, self::FIELD_CATEGORY_INDEPENDENT),
		);
	}

	/**
	 * Writes one field for one product into the content store and points the product's
	 * prepare row at it.
	 *
	 * The legacy inline column is cleared afterwards so the same value is not kept in two
	 * places and the old table stops growing. When the product has no prepare row — which
	 * happens, the longtext row is written unconditionally while the prepare row is not —
	 * there is nowhere to put the reference, so the legacy column stays authoritative for
	 * that product.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sField one of the FIELD_* constants
	 * @param string $sValue JSON, or '' to clear the field
	 * @return void
	 */
	public static function write($mpID, $pID, $sField, $sValue) {
		$oDB   = MagnaDB::gi();
		$sValue = (string)$sValue;

		if (!self::storeReady() || !self::prepareRowExists($mpID, $pID)) {
			self::writeLegacy($mpID, $pID, $sField, $sValue);
			return;
		}

		$sTextId = ($sValue === '') ? '' : self::put($sValue, $sField);
		if ($sTextId === false) {
			// The store rejected the value. Fall back to the legacy column so the save is
			// still persisted somewhere rather than being lost.
			self::writeLegacy($mpID, $pID, $sField, $sValue);
			return;
		}

		$mResult = $oDB->query("
			UPDATE ".TABLE_MAGNA_TEMU_PREPARE."
			   SET ".self::referenceColumn($sField)." = ".($sTextId === '' ? 'NULL' : "'".$oDB->escape($sTextId)."'")."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			       AND PrepareType = '".self::PREPARE_TYPE."'
		");
		if ($mResult === false) {
			self::writeLegacy($mpID, $pID, $sField, $sValue);
			return;
		}

		// The value now lives in the store; keeping the inline copy would defeat the
		// deduplication and leave two sources that can drift apart.
		self::writeLegacy($mpID, $pID, $sField, '');
	}

	/**
	 * Drops everything stored for one product: both references and the legacy row.
	 *
	 * The rows in the content store are shared between products and are therefore left
	 * alone; they are unreferenced garbage at worst, never another product's data.
	 *
	 * @param int $mpID
	 * @param int $pID
	 * @return void
	 */
	public static function remove($mpID, $pID) {
		$oDB = MagnaDB::gi();

		if (self::storeReady()) {
		$oDB->query("
			UPDATE ".TABLE_MAGNA_TEMU_PREPARE."
			   SET ShopVariationTextId = NULL,
			       CategoryIndependentTextId = NULL
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
		");
		}

		$oDB->delete(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
			'mpID'        => (int)$mpID,
			'products_id' => (int)$pID,
		));
	}

	/**
	 * Clears one field for one product, leaving the other one intact.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sField one of the FIELD_* constants
	 * @return void
	 */
	public static function clear($mpID, $pID, $sField) {
		self::write($mpID, $pID, $sField, '');
	}

	/**
	 * Stores a value under the hash of its content and returns that hash. Storing the
	 * same content again is a no-op, which is where the deduplication comes from.
	 *
	 * @param string $sValue
	 * @param string $sField one of the FIELD_* constants
	 * @return string TextId
	 */
	protected static function put($sValue, $sField) {
		$oDB     = MagnaDB::gi();
		$sTextId = hash('sha256', $sValue);

		$mResult = $oDB->query("
			INSERT IGNORE INTO ".TABLE_MAGNA_TEMU_LONGTEXT."
			       (TextId, ReferenceFieldName, Value, CreatedAt)
			VALUES ('".$oDB->escape($sTextId)."',
			        '".$oDB->escape($sField)."',
			        '".$oDB->escape($sValue)."',
			        NOW())
		");

		// MagnaDB reports a failed statement only by returning false - it neither throws nor
		// dies. Returning the hash regardless would point the prepare row at a row that does
		// not exist, and the caller would then clear the legacy column on top of it.
		if ($mResult === false) {
			return false;
		}

		return $sTextId;
	}

	/**
	 * @param int $mpID
	 * @param int $pID
	 * @return bool whether the product has a prepare row that can hold a reference
	 */
	protected static function prepareRowExists($mpID, $pID) {
		return (int)MagnaDB::gi()->fetchOne("
			SELECT COUNT(*)
			  FROM ".TABLE_MAGNA_TEMU_PREPARE."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
			       AND PrepareType = '".self::PREPARE_TYPE."'
		") > 0;
	}

	/**
	 * Writes one legacy inline column, creating the row when it is missing and leaving the
	 * other column untouched — it belongs to the other editor.
	 *
	 * @param int    $mpID
	 * @param int    $pID
	 * @param string $sField one of the FIELD_* constants
	 * @param string $sValue
	 * @return void
	 */
	protected static function writeLegacy($mpID, $pID, $sField, $sValue) {
		$oDB     = MagnaDB::gi();
		$sColumn = self::legacyColumn($sField);

		$bExists = (int)$oDB->fetchOne("
			SELECT COUNT(*)
			  FROM ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
		") > 0;

		if (!$bExists) {
			if ($sValue === '') {
				return; // nothing to store and nothing to clear
			}
			$oDB->insert(TABLE_MAGNA_TEMU_PREPARE_LONGTEXT, array(
				'mpID'        => (int)$mpID,
				'products_id' => (int)$pID,
				$sColumn      => $sValue,
			));
			return;
		}

		$oDB->query("
			UPDATE ".TABLE_MAGNA_TEMU_PREPARE_LONGTEXT."
			   SET ".$sColumn." = '".$oDB->escape($sValue)."'
			 WHERE mpID = '".(int)$mpID."'
			       AND products_id = '".(int)$pID."'
		");
	}
}
