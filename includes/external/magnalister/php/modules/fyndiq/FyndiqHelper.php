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
 * $Id: DawandaHelper.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/MagnaCompatibleHelper.php');

class FyndiqHelper extends MagnaCompatibleHelper {
	
	public static function loadPriceSettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$currency = getCurrencyFromMarketplace($mpId);
		$convertCurrency = getDBConfigValue(array($mp.'.exchangerate', 'update'), $mpId, false);
		
		$config = array(
			'Price' => array(
				'AddKind' => getDBConfigValue($mp.'.price.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.price.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.price.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.price.group', $mpId, ''),
				'UseSpecialOffer' => getDBConfigValue(array($mp.'.price.usespecialoffer', 'val'), $mpId, false),
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
			),
			'PurchasePrice' => array(
				'AddKind' => getDBConfigValue($mp.'.purchaseprice.addkind', $mpId, 'percent'),
				'Factor'  => (float)getDBConfigValue($mp.'.purchaseprice.factor', $mpId, 0),
				'Signal'  => getDBConfigValue($mp.'.purchaseprice.signal', $mpId, ''),
				'Group'   => getDBConfigValue($mp.'.purchaseprice.group', $mpId, ''),
				'UseSpecialOffer' => false,
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
				'IncludeTax' => false,
			),
		);
		
		return $config;
	}
	
	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Type'  => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp.'.quantity.maxquantity', $mpId, 0),
		);
		
		return $config;
	}

	public static function getTitleAndDescription(&$selection, $mpID) {
		$defaultImagePath = defined('DIR_WS_CATALOG_POPUP_IMAGES')
			? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
			: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
		$imagePath = getDBConfigValue('fyndiq.imagepath', $mpID);
		if (empty($imagePath)) {
			$imagePath = $defaultImagePath;
		}

		$fyndiqTemplate = getDBConfigValue('fyndiq.template.content', $mpID);
		if (!isset($fyndiqTemplate)) {
			$fyndiqTemplate = '<p>#TITLE#</p>
				<p>#ARTNR#</p>
				<p>#PICTURE1#</p>
				<p>#PICTURE2#</p>
				<p>#PICTURE3#</p>
				<p>#DESCRIPTION#</p>';
		}

		# Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title']),
			'#ARTNR#' => $selection[0]['products_model'],
			'#PID#' => $selection[0]['products_id'],
			'#SKU#' => magnaPID2SKU($selection[0]['products_id']),
			'#DESCRIPTION#' => stripLocalWindowsLinks($selection[0]['Description']),
			'#PICTURE1#' => $imagePath . $selection[0]['PictureUrl'],
		);
		$selection[0]['Description'] = FyndiqHelper::substitutePictures(substituteTemplate(
			$fyndiqTemplate, $substitution
		), $selection[0]['products_id'], $imagePath);

		$fyndiqTitleTemplate = getDBConfigValue('fyndiq.template.name', $mpID, '#TITLE#');
		if (!isset($fyndiqTitleTemplate)) {
			$fyndiqTitleTemplate = '#TITLE#';
		}

		$simplePrice = new SimplePrice(null, getCurrencyFromMarketplace($mpID));
		$productTax = SimplePrice::getTaxByPID($selection[0]['products_id']);
		$taxFromConfig = getDBConfigValue('fyndiq.checkin.mwst', $mpID);

		$simplePrice->setFinalPriceFromDB($selection[0]['products_id'], $mpID);
		if (isset($taxFromConfig) && $taxFromConfig !== '') {
			$simplePrice
				->removeTax($productTax)
				->addTax($taxFromConfig);
		}

		$fyndiqPrice = $simplePrice
			->roundPrice()
			->getPrice();

		# Titel-Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title']),
			'#BASEPRICE#' => $fyndiqPrice,
		);
		$selection[0]['Title'] = substituteTemplate(
			$fyndiqTitleTemplate, $substitution
		);
	}

	public static function substitutePictures($tmplStr, $pID, $imagePath) {
		# Tabelle nur bei xtCommerce- und Gambio- Shops vorhanden (nicht OsC)
		if (   defined('TABLE_MEDIA')      && MagnaDB::gi()->tableExists(TABLE_MEDIA)
			&& defined('TABLE_MEDIA_LINK') && MagnaDB::gi()->tableExists(TABLE_MEDIA_LINK)
		) {
			$pics = MagnaDB::gi()->fetchArray('SELECT
				id as image_nr, file as image_name
				FROM '.TABLE_MEDIA.' m, '.TABLE_MEDIA_LINK.' ml
				WHERE m.type=\'images\' AND ml.class=\'product\' AND m.id=ml.m_id AND ml.link_id='.$pID);
			$i = 2;
			# Ersetze #PICTURE2# usw. (#PICTURE1# ist das Hauptbild und wird vorher ersetzt)
			foreach($pics as $pic) {
				$tmplStr = str_replace('#PICTURE'.$i.'#', "<img src=\"".$imagePath.$pic['image_name']."\" style=\"border:0;\" alt=\"\" title=\"\" />",
					preg_replace( '/(src|SRC|href|HREF)(\s*=\s*)(\'|")(#PICTURE'.$i.'#)/', '\1\2\3'.$imagePath.$pic['image_name'], $tmplStr));
				$i++;
			}
			# Uebriggebliebene #PICTUREx# loeschen
			$str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
			#		str_replace($find, $replace, $tmplStr));
		} else {
			$str = preg_replace(	'/#PICTURE\d+#/','', $tmplStr);
		}
		return $str;
	}

	/**
	 * Sanitazes description and preparing it for Fyndiq because Fyndiq doesn't allow html tags.
	 *
	 * @param string $sDescription
	 * @return string $sDescription
	 */
	public static function fyndiqSanitizeDesc($sDescription) {
		# preg_replace could return NULL at 5.2.0 to 5.3.6 - "/(\s*<br[^>]*>\s*)*$/"
		# tested at: http://3v4l.org/WGcod
		if (version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.6', '<=')) {
			@ini_set('pcre.backtrack_limit', '10000000');
			@ini_set('pcre.recursion_limit', '10000000');
		}
		$sDescription = preg_replace("#(<\\?div>|<\\?li>|<\\?p>|<\\?h1>|<\\?h2>|<\\?h3>|<\\?h4>|<\\?h5>|<\\?blockquote>)([^\n])#i", "$1\n$2", $sDescription);
		// Replace <br> tags with new lines
		$sDescription = preg_replace('/<[h|b]r[^>]*>/i', "\n", $sDescription);
		$sDescription = trim(strip_tags($sDescription));
		// Normalize space
		$sDescription = str_replace("\r", "\n", $sDescription);
		$sDescription = preg_replace("/\n{3,}/", "\n\n", $sDescription);
		$sDescription = mb_substr($sDescription,0,4096, 'UTF-8');

		return $sDescription;
	}
}
