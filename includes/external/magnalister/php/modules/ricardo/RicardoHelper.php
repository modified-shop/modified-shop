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

class RicardoHelper extends MagnaCompatibleHelper {
	const SUBTITLE_MAX_LENGTH = 60;
	
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
		);
		
		return $config;
	}
	
	public static function loadQuantitySettings($mpId) {
		$mp = magnaGetMarketplaceByID($mpId);
		
		$config = array(
			'Type'  => getDBConfigValue($mp.'.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp.'.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp.'.quantity.maxquantity', $mpId, 999),
		);
		
		return $config;
	}
		
	public static function getTitleAndDescription($lang, &$selection, $mpID) {
		$defaultImagePath = defined('DIR_WS_CATALOG_POPUP_IMAGES')
					? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES
					: HTTP_CATALOG_SERVER.DIR_WS_CATALOG_IMAGES;
		$imagePath = getDBConfigValue('ricardo.imagepath', $mpID);
		if (empty($imagePath)) {
			$imagePath = $defaultImagePath;
		}
		
		$ricardoTemplate = getDBConfigValue('ricardo.template.content', $mpID);
		if (!isset($ricardoTemplate)) {
			$ricardoTemplate = '<p>#TITLE#<br>
				#VARIATIONDETAILS#</p>
				<p>#ARTNR#</p>
				<p>#SHORTDESCRIPTION#</p>
				<p>#PICTURE1#</p>
				<p>#PICTURE2#</p>
				<p>#PICTURE3#</p>
				<p>#DESCRIPTION#</p>';
		} elseif (strpos($ricardoTemplate, '#VARIATIONDETAILS#') === false) {
			$strPosTitle = strpos($ricardoTemplate, '#TITLE#');
			if ($strPosTitle !== false) {
				$ricardoTemplate = substr_replace($ricardoTemplate, '<br>#VARIATIONDETAILS#<br>', $strPosTitle + strlen('#TITLE#'), 0);
			} else {
				$ricardoTemplate = '#VARIATIONDETAILS#<br>' . $ricardoTemplate;
			}
		}
		
		# Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt.
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title' . $lang]),
			'#ARTNR#' => $selection[0]['products_model'],
			'#PID#' => $selection[0]['products_id'],
			'#SKU#' => magnaPID2SKU($selection[0]['products_id']),
			'#SHORTDESCRIPTION#' => $selection[0]['Subtitle' . $lang],
			'#DESCRIPTION#' => stripLocalWindowsLinks($selection[0]['Description' . $lang]),
			'#PICTURE1#' => $imagePath . $selection[0]['PictureUrl'],
		);
		$selection[0]['Description' . $lang] = RicardoHelper::substitutePictures(substituteTemplate(
			$ricardoTemplate, $substitution
		), $selection[0]['products_id'], $imagePath);

		$ricardoTitleTemplate = getDBConfigValue('ricardo.template.name', $mpID, '#TITLE#');
		if (!isset($ricardoTitleTemplate)) {
			$ricardoTitleTemplate = '#TITLE#';
		}
		
		$simplePrice = new SimplePrice(null, getCurrencyFromMarketplace($mpID));
		$productTax = SimplePrice::getTaxByPID($selection[0]['products_id']);
		$taxFromConfig = getDBConfigValue('ricardo.checkin.mwst', $mpID);

		$simplePrice->setFinalPriceFromDB($selection[0]['products_id'], $mpID);
		if (isset($taxFromConfig) && $taxFromConfig !== '') {
			$simplePrice
				->removeTax($productTax)
				->addTax($taxFromConfig);
		}

		$ricardoPrice = $simplePrice
				->roundPrice()
				->getPrice();
		
		# Titel-Template fuellen
		# bei mehreren Artikeln erst beim Speichern fuellen
		# Preis und ggf. VPE wird erst beim Uebermitteln eingesetzt. 
		$substitution = array (
			'#TITLE#' => fixHTMLUTF8Entities($selection[0]['Title' . $lang]),
			'#BASEPRICE#' => $ricardoPrice,
		);
		$selection[0]['Title' . $lang] = substituteTemplate(
			$ricardoTitleTemplate, $substitution
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
	 * Sanitizes subtitle and preparing it for Ricardo because Ricardo doesn't allow html tags.
	 *
	 * @param $sSubtitle
	 * @return mixed
	 */
	public static function ricardoSanitizeSubtitle($sSubtitle){
		$sSubtitle= preg_replace(array('/<\/?font>/','/<\/?div>/','/<\/?li>/','/<\/?p>/','/<\/?h1>/','/<\/?h2>/','/<\/?h3>/','/<\/?h4>/','/<\/?h5>/','/<\/?blockquote>/','/<\/?br>/')," ", $sSubtitle);
		$sSubtitle = preg_replace('/&nbsp;/', " ", $sSubtitle);
		// Replace <br> tags with new lines
		$sSubtitle = preg_replace('/<[h|b]r[^>]*>/i', "\n", $sSubtitle);
		$sSubtitle = trim(strip_tags($sSubtitle));
		// Normalize space
		$sSubtitle = str_replace("\r", "\n", $sSubtitle);
		$sSubtitle = preg_replace("/\n{3,}/", "\n\n", $sSubtitle);

		if (isset($sSubtitle) && mb_strlen($sSubtitle, 'UTF-8') > self::SUBTITLE_MAX_LENGTH) {
			$sSubtitle = mb_substr($sSubtitle, 0, self::SUBTITLE_MAX_LENGTH - 3, 'UTF-8') . '...';
		}

		return $sSubtitle;
	}
}
