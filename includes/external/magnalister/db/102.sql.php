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
 * (c) 2010 - 2017 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 *
 *
 * This migration script should deal with all backward compatibility issues by transferring all old prepare data to new AM format.
 * Since it is possible to reformat existing existing data, create missing information from existing and shop data and delete
 * unnecessary data, all backward compatibility issues are solved here leaving new plugin code free of legacy code.
 */

$queries = array();
$functions = array();

/*
2024-01-08 module deleted

require_once(DIR_MAGNALISTER_FS_INCLUDES.'config.php');



function ml_db_update_90_1()
{
	if (!MagnaDB::gi()->columnExistsInTable('ModificationDate', TABLE_MAGNA_MEINPAKET_VARIANTMATCHING)) {
		MagnaDB::gi()->query('ALTER TABLE `' . TABLE_MAGNA_MEINPAKET_VARIANTMATCHING .
			'` ADD COLUMN `ModificationDate` DATETIME NOT NULL DEFAULT  \'0000-00-00 00:00:00\''
		);
	}
}

$functions[] = 'ml_db_update_90_1';

function md_db_update_102_2()
{
	if (!MagnaDB::gi()->columnExistsInTable('CategoryAttributes', TABLE_MAGNA_MEINPAKET_PROPERTIES)){
		MagnaDB::gi()->query('ALTER TABLE `' . TABLE_MAGNA_MEINPAKET_PROPERTIES .
			'` ADD COLUMN `CategoryAttributes` text NOT NULL DEFAULT \'\' AFTER ShippingDetails'
		);
	}
}

$functions[] = 'md_db_update_102_2';

function md_db_update_102_3()
{
	if (!MagnaDB::gi()->columnExistsInTable('variation_theme', TABLE_MAGNA_MEINPAKET_PROPERTIES)) {
		MagnaDB::gi()->query('ALTER TABLE `' . TABLE_MAGNA_MEINPAKET_PROPERTIES .
			'` ADD COLUMN `variation_theme` varchar(400) DEFAULT NULL AFTER `PreparedTS`'
		);
	}
}

$functions[] = 'md_db_update_102_3';

function md_db_update_102_4()
{
	if (!MagnaDB::gi()->columnExistsInTable('Verified', TABLE_MAGNA_MEINPAKET_PROPERTIES)) {
		MagnaDB::gi()->query("ALTER TABLE `" . TABLE_MAGNA_MEINPAKET_PROPERTIES .
			"` ADD COLUMN `Verified` enum('OK','ERROR','OPEN','EMPTY') NOT NULL DEFAULT 'OK' AFTER `PreparedTS`"
		);
	}
}

$functions[] = 'md_db_update_102_4';


function md_db_update_102_5()
{
	$prepareRecordsToUpdate = MagnaDB::gi()->fetchArray("
         SELECT *
         FROM  `" . TABLE_MAGNA_MEINPAKET_PROPERTIES . "`
         WHERE
            NULLIF(CategoryAttributes, '') IS NULL
			AND NULLIF(VariationConfiguration, '') IS NOT NULL  
			AND VariationConfiguration != '0'
     ");

	$dataForReplace = array();
	foreach ($prepareRecordsToUpdate as $prepareData) {
		$prepareData['VariationConfiguration'] = json_decode($prepareData['VariationConfiguration'], true);
		$identifier = $prepareData['VariationConfiguration']['MpIdentifier'];
		$customIdentifier = $prepareData['VariationConfiguration']['CustomIdentifier'];
		$variationMatching = MagnaDB::gi()->fetchArray("
            SELECT MpId, ShopVariation
            FROM  `" . TABLE_MAGNA_MEINPAKET_VARIANTMATCHING . "`
            WHERE 
               MpIdentifier = '" . MagnaDB::gi()->escape($identifier) . "' AND
               CustomIdentifier = '" . MagnaDB::gi()->escape($customIdentifier) . "' AND
               MpId = '" . MagnaDB::gi()->escape($prepareData['mpID']) . "'
            LIMIT 1
        ");

		$shopVariation = array();
		if (!empty($variationMatching)) {
			$shopVariation = getConvertedShopVariationAttributes(
				$variationMatching[0]['MpId'],
				json_decode($variationMatching[0]['ShopVariation'], true)
			);
		}


		// After conversion custom identifier is not needed anymore, since all customizations are in ShopVariation column
		$mpIdentifierDecoded = base64_decode($prepareData['VariationConfiguration']['MpIdentifier']);
		$prepareData['VariationConfiguration'] = $mpIdentifierDecoded;
		arrayEntitiesToUTF8($shopVariation);
		$prepareData['CategoryAttributes'] = json_encode($shopVariation);
		$prepareData['variation_theme'] = json_encode(array(
			$mpIdentifierDecoded => array_keys($shopVariation)
		));
		$dataForReplace[] = $prepareData;
	}

	if (!empty($dataForReplace)) {
		MagnaDB::gi()->batchinsert(TABLE_MAGNA_MEINPAKET_PROPERTIES, $dataForReplace, true);
	}

	// migrate none to splitAll in prepare
	MagnaDB::gi()->query("
		UPDATE `" . TABLE_MAGNA_MEINPAKET_PROPERTIES . "`
		SET `VariationConfiguration`='splitAll',
			`CategoryAttributes`='[]',
			`variation_theme`='{\"splitAll\":[]}'
		WHERE
			NULLIF(VariationConfiguration, '') IS NULL AND
			NULLIF(CategoryAttributes, '') IS NULL
	");

	// All custom identifier specifics are moved to prepare table, so first delete all custom identifier records from variant matching table
	MagnaDB::gi()->query("
		DELETE FROM `" . TABLE_MAGNA_MEINPAKET_VARIANTMATCHING . "`
		WHERE
			 NULLIF(CustomIdentifier, '') IS NOT NULL AND 
			`CustomIdentifier` != '0'
	");

	// Detect old records by searching new DataType property in ShopVariation matching configuration
	$variantMatchingRecordsToUpdate = MagnaDB::gi()->fetchArray("
		SELECT *
		FROM  `" . TABLE_MAGNA_MEINPAKET_VARIANTMATCHING . "`
		WHERE 
			`ShopVariation` NOT LIKE '%DataType%'
	");

	foreach ($variantMatchingRecordsToUpdate as $variantMatchingData) {
		$shopVariation = getConvertedShopVariationAttributes(
			$variantMatchingData['MpId'],
			json_decode($variantMatchingData['ShopVariation'], true)
		);

		arrayEntitiesToUTF8($shopVariation);

		// Method batchinsert can't be used since we must change Identifier and that column is part of unique key
		MagnaDB::gi()->update(
			'magnalister_meinpaket_variantmatching',
			array(
				'MpIdentifier' => base64_decode($variantMatchingData['MpIdentifier']),
				'ShopVariation' => json_encode($shopVariation),
			),
			array(
				'mpID' => $variantMatchingData['MpId'],
				'MpIdentifier' => $variantMatchingData['MpIdentifier'],
			)
		);
	}
}

$functions[] = 'md_db_update_102_5';

function getConvertedShopVariationAttributes($mpId, $oldShopVariation) {
	$shopVariation = array();
	foreach ($oldShopVariation as $mpCode => $matchedAttribute) {
		$mpCode = base64_decode($mpCode);
		$shopVariation[$mpCode] = array(
			'AttributeName' => $mpCode,
			'Code' => $matchedAttribute['Code'],
			'Kind' => $matchedAttribute['Kind'],
			'Required' => true,
			'DataType' => $matchedAttribute['Kind'] === 'Matching' ? 'select' : 'text',
			'Values' => array(),
			'Error' => false,
		);

		$shopValues = array();
		if (!empty($matchedAttribute['Values']) && is_array($matchedAttribute['Values'])) {
			$languageId = getDBConfigValue('meinpaket.lang', $mpId, $_SESSION['languages_id']);
			$productOptions = MagnaDB::gi()->fetchArray('
			    SELECT DISTINCT products_options_values_id AS Id, products_options_values_name As Name
			      FROM '.TABLE_PRODUCTS_OPTIONS_VALUES.'
			     WHERE language_id = "'.$languageId.'"
			           AND products_options_values_id IN ("'.implode('", "', array_keys($matchedAttribute['Values'])).'")
			');

			if (!empty($productOptions)) {
				foreach ($productOptions as $productOption) {
					$shopValues[$productOption['Id']] = $productOption['Name'];
				}
			}
		}

		$index = 1;
		foreach ($matchedAttribute['Values'] as $shopKey => $mpKey) {
			if (empty($mpKey) || ('null' === $mpKey) || !array_key_exists($shopKey, $shopValues)) {
				continue;
			}

			$mpKey = ($matchedAttribute['Kind'] === 'Matching') ? $mpKey : $shopValues[$mpKey];
			$shopVariation[$mpCode]['Values'][$index] = array(
				'Shop' => array(
					'Key' => $shopKey,
					'Value' => $shopValues[$shopKey],
				),
				'Marketplace' => array(
					'Key' => $mpKey,
					'Value' => $mpKey,
					'Info' => "{$mpKey} - (automatisch zugeordnet)",
				),
			);
			$index++;
		}
	}

	return $shopVariation;
}
*/
