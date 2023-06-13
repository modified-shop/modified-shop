<?php
/*
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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/AttributesMatchingHelper.php');

class CdiscountHelper extends AttributesMatchingHelper {
	const TITLE_MAX_LENGTH = 132;
	const SUBTITLE_MAX_LENGTH = 132;
	const DESC_MAX_LENGTH = 420;

	private static $instance;

    /**
     * Create an Instance of CdiscountHelper and return it
     *
     * @return CdiscountHelper
     */
    public static function gi() {
        if (self::$instance === null) {
            self::$instance = new CdiscountHelper();
        }

        return self::$instance;
    }

	public static function processCheckinErrors($result, $mpID)
	{
		$fieldname = 'MARKETPLACEERRORS';
		$dbCharSet = MagnaDB::gi()->mysqlVariableValue('character_set_connection');
		if (('utf8mb3' == $dbCharSet) || ('utf8mb4' == $dbCharSet)) {
			# means the same for us
			$dbCharSet = 'utf8';
		}
		if ($dbCharSet != 'utf8') {
			arrayEntitiesToLatin1($result[$fieldname]);
		}
		$supportedFields = array('ErrorMessage', 'DateAdded', 'AdditionalData');
		if (!isset($result[$fieldname]) || empty($result[$fieldname])) {
			return;
		}
		foreach ($result[$fieldname] as $err) {
			if (!isset($err['AdditionalData'])) {
				$err['AdditionalData'] = array();
			}
			foreach ($err as $key => $value) {
				if (!in_array($key, $supportedFields)) {
					$err['AdditionalData'][$key] = $value;
					unset($err[$key]);
				}
			}
			$err = array(
				'mpID' => $mpID,
				'errormessage' => $err['ErrorMessage'],
				'dateadded' => $err['DateAdded'],
				'additionaldata' => serialize($err['AdditionalData']),
			);
			MagnaDB::gi()->insert(TABLE_MAGNA_COMPAT_ERRORLOG, $err);
		}
	}

	public static function loadPriceSettings($mpId)
	{
		$mp = magnaGetMarketplaceByID($mpId);

		$currency = getCurrencyFromMarketplace($mpId);
		$convertCurrency = getDBConfigValue(array($mp . '.exchangerate', 'update'), $mpId, false);

		$config = array(
			'Price' => array(
				'AddKind' => getDBConfigValue($mp . '.price.addkind', $mpId, 'percent'),
				'Factor' => (float)getDBConfigValue($mp . '.price.factor', $mpId, 0),
				'Signal' => getDBConfigValue($mp . '.price.signal', $mpId, ''),
				'Group' => getDBConfigValue($mp . '.price.group', $mpId, ''),
				'UseSpecialOffer' => getDBConfigValue(array($mp . '.price.usespecialoffer', 'val'), $mpId, false),
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
			),
			'PurchasePrice' => array(
				'AddKind' => getDBConfigValue($mp . '.purchaseprice.addkind', $mpId, 'percent'),
				'Factor' => (float)getDBConfigValue($mp . '.purchaseprice.factor', $mpId, 0),
				'Signal' => getDBConfigValue($mp . '.purchaseprice.signal', $mpId, ''),
				'Group' => getDBConfigValue($mp . '.purchaseprice.group', $mpId, ''),
				'UseSpecialOffer' => false,
				'Currency' => $currency,
				'ConvertCurrency' => $convertCurrency,
				'IncludeTax' => false,
			),
		);

		return $config;
	}

	public static function loadQuantitySettings($mpId)
	{
		$mp = magnaGetMarketplaceByID($mpId);

		$config = array(
			'Type' => getDBConfigValue($mp . '.quantity.type', $mpId, 'lump'),
			'Value' => (int)getDBConfigValue($mp . '.quantity.value', $mpId, 0),
			'MaxQuantity' => (int)getDBConfigValue($mp . '.quantity.maxquantity', $mpId, 0),
		);

		return $config;
	}

	public static function GetConditionTypes()
	{
		global $_MagnaSession;

		$mpID = $_MagnaSession['mpID'];

		$types['values'] = array();

		if (isset($_MagnaSession[$mpID]['ConditionTypes'])
			&& !empty($_MagnaSession[$mpID]['ConditionTypes'])
		) {
			return $_MagnaSession[$mpID]['ConditionTypes'];
		}
		try {
			$typesData = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetOfferCondition'
			));
		} catch (MagnaException $e) {
			$typesData = array(
				'DATA' => false
			);
		}
		if (!is_array($typesData) || !isset($typesData['DATA'])) {
			return false;
		}
		$_MagnaSession[$mpID]['ConditionTypes'] = $typesData['DATA'];
		return $typesData['DATA'];
	}

	public static function GetConditionTypesConfig(&$types)
	{
		$types['values'] = self::GetConditionTypes();
	}

	public static function SearchOnCdiscount($search = '', $searchBy = 'EAN')
	{
		try {
			$data = MagnaConnector::gi()->submitRequest(array(
				'ACTION' => 'GetItemsFromMarketplace',
				'DATA' => array(
					$searchBy => $search
				)
			));
		} catch (MagnaException $e) {
			$data = array(
				'DATA' => false
			);
		}

		if (!is_array($data) || !isset($data['DATA']) || empty($data['DATA'])) {
			return false;
		}

		return $data['DATA'];
	}

	public static function GetWeightFromShop($itemId)
	{
		$result = MagnaDB::gi()->fetchOne('
			SELECT products_weight
			FROM ' . TABLE_PRODUCTS . '
			WHERE products_id = "' . $itemId . '"
		');

		if ($result && (int)$result > 0) {
			$weight = round($result, 2);
			return $weight . 'kg';
		}

		return '';
	}

	/**
	 * Sanitazes description and preparing it for Cdiscount because Cdiscount doesn't allow html tags.
	 *
	 * @param string $sDescription
	 * @return string $sDescription
	 */
	public static function cdiscountSanitizeDesc($sDescription) {
		# preg_replace could return NULL at 5.2.0 to 5.3.6 - "/(\s*<br[^>]*>\s*)*$/"
		# tested at: http://3v4l.org/WGcod
		if (version_compare(PHP_VERSION, '5.2.0', '>=') && version_compare(PHP_VERSION, '5.3.6', '<=')) {
			@ini_set('pcre.backtrack_limit', '10000000');
			@ini_set('pcre.recursion_limit', '10000000');
		}
		$sDescription = preg_replace("#(<\\?div>|<\\?li>|<\\?p>|<\\?h1>|<\\?h2>|<\\?h3>|<\\?h4>|<\\?h5>|<\\?blockquote>)([^\n])#i", "$1\n$2", $sDescription);
		$sDescription = preg_replace('/&nbsp;/', " ", $sDescription);
		// Replace <br> tags with new lines
		$sDescription = preg_replace('/<[h|b]r[^>]*>/i', "\n", $sDescription);
		$sDescription = trim(strip_tags($sDescription));
		// Normalize space
		$sDescription = str_replace("\r", "\n", $sDescription);
		$sDescription = preg_replace("/\n{3,}/", "\n\n", $sDescription);

		// replace all umlauts with real chars
		$sDescription = html_entity_decode($sDescription, ENT_COMPAT, 'UTF-8');

		if (strlen($sDescription) > self::DESC_MAX_LENGTH) {
			$sDescription = mb_substr($sDescription, 0, self::DESC_MAX_LENGTH - 3, 'UTF-8') . '...';
		} else {
			$sDescription = mb_substr($sDescription, 0, self::DESC_MAX_LENGTH, 'UTF-8');
		}

		return $sDescription;
	}

	/**
	 * Sanitizes subtitle and preparing it for Cdiscount because Cdiscount doesn't allow html tags.
	 *
	 * @param $sSubtitle
	 * @return mixed
	 */
	public static function cdiscountSanitizeSubtitle($sSubtitle)
	{
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

	/**
	 * Check length of the title slice it and adds dots if is needed.
	 *
	 * @param string $sTitle
	 * @return mixed
	 */
	public static function cdiscountSanitizeTitle($sTitle)
	{
		if (isset($sTitle) && mb_strlen($sTitle, 'UTF-8') > self::TITLE_MAX_LENGTH) {
			$sTitle = mb_substr($sTitle, 0, self::TITLE_MAX_LENGTH - 3, 'UTF-8') . '...';
		}

		return $sTitle;
	}

	public static function setDescriptionAndMarketingDescription($pID, $productDescription, &$description, &$marketingDescription){
		global $_MagnaSession;
		$mpID = $_MagnaSession['mpID'];
		$marketplace = $_MagnaSession['currentPlatform'];

		$sLanguageCode = getDBConfigValue($marketplace . '.lang', $mpID);

		$standardDescriptionConfigTable = getDBConfigValue($marketplace . '.prepare.standarddescription.dbmatching.table', $mpID);
		$standardDescriptionConfigAlias = getDBConfigValue($marketplace . '.prepare.standarddescription.dbmatching.alias', $mpID);

		$marketingDescriptionConfigTable = getDBConfigValue($marketplace . '.prepare.marketingdescription.dbmatching.table', $mpID);
		$marketingDescriptionConfigAlias = getDBConfigValue($marketplace . '.prepare.marketingdescription.dbmatching.alias', $mpID);

		if (!empty($marketingDescriptionConfigTable) && !empty($marketingDescriptionConfigAlias)) {
			$marketingDescription = self::getDescriptionFromConfig($pID, $marketingDescriptionConfigTable, $marketingDescriptionConfigAlias, $sLanguageCode);
		} else {
			$marketingDescription = isset($productDescription) ? $productDescription : '';
		}

		if (!empty($standardDescriptionConfigTable) && !empty($standardDescriptionConfigAlias)) {
			$description = self::getDescriptionFromConfig($pID, $standardDescriptionConfigTable, $standardDescriptionConfigAlias, $sLanguageCode);
		} else {
			$description = isset($productDescription) ? $productDescription : '';
		}
	}

	public static function getDescriptionFromConfig($productID, $table, $alias, $languageCode) {
		if (!isset($table['table'])
			|| empty($table['table'])
			|| empty($table['column'])
		) {
			return false;
		}

		if ($alias) {
			$alias = 'products_id';
		}

		if(MagnaDB::gi()->columnExistsInTable('language_id', $table['table'])) {
			$languageQuery = 'AND `language_id` = ' . $languageCode;
		} else {
			$languageQuery = '';
		}

		return (string)MagnaDB::gi()->fetchOne('
			SELECT `' . $table['column'] . '` 
			FROM `' . $table['table'] . '` 
			WHERE `' . $alias . '` = ' . MagnaDB::gi()->escape($productID) . '
				' . $languageQuery . '
				AND `' . $table['column'] . '` <> \'\'
		');
	}

	protected function isProductPrepared($category, $prepare = false)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sKeyType = 'products_model';
		} else {
			$sKeyType = 'products_id';
		}
		
		return MagnaDB::gi()->recordExists(TABLE_MAGNA_CDISCOUNT_PREPARE, array(
			'MpId' => $this->mpId,
			$sKeyType => $prepare,
			'PrimaryCategory' => $category,
		));
	}

	protected function getPreparedData($category, $prepare = false, $customIdentifier = '')
	{
		$availableCustomConfigs = array();

		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.$prepare.'"';
		} else {
			$sSQLAnd = ' AND products_id = "'. $prepare . '"';
		}

		if ($prepare) {
		    $query = eecho('
				SELECT CategoryAttributes
				FROM ' . TABLE_MAGNA_CDISCOUNT_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
					AND PrimaryCategory = "' . $category . '"
					' . $sSQLAnd . '
			');

			$availableCustomConfigs = json_decode(MagnaDB::gi()->fetchOne($query), true);
		}

		return !$availableCustomConfigs ? array() : $availableCustomConfigs;
	}

	protected function getSavedVariationThemeCode($category, $prepare = false)
	{
		if (getDBConfigValue('general.keytype', '0') == 'artNr') {
			$sSQLAnd = ' AND products_model = "'.$prepare. '"';
		} else {
			$sSQLAnd = ' AND products_id = "'. $prepare . '"';
		}

		$variationTheme = null;
		if ($prepare) {
			$variationTheme = MagnaDB::gi()->fetchOne(eecho('
				SELECT variation_theme
				FROM ' . TABLE_MAGNA_CDISCOUNT_PREPARE . '
				WHERE MpId = ' . $this->mpId . '
						AND PrimaryCategory = "' . $category . '"
						'. $sSQLAnd
				)
			);
		}

		$variationTheme = json_decode($variationTheme, true);

		return is_array($variationTheme) ? key($variationTheme) : '';
	}

    /**
     * Gets prepared attributes data for products prepared for given category.
     *
     * @param string $category
     * @param string $customIdentifier
     * @return array|null
     */
	protected function getPreparedProductsData($category, $customIdentifier = '')
	{
		$dataFromDB = MagnaDB::gi()->fetchArray(eecho('
				SELECT `CategoryAttributes`
				FROM ' . TABLE_MAGNA_CDISCOUNT_PREPARE . '
				WHERE mpID = ' . $this->mpId . '
					AND PrimaryCategory = "' . $category . '"
			', false), true);

		if ($dataFromDB) {
			$result = array();
			foreach ($dataFromDB as $preparedData) {
				if ($preparedData) {
					$result[] = json_decode($preparedData, true);
				}
			}

			return $result;
		}

		return null;
	}

	protected function getAttributesFromMP($category, $additionalData = null, $customIdentifier = '')
    {
        $data = CdiscountApiConfigValues::gi()->getVariantConfigurationDefinition($category);
        if (!is_array($data) || !isset($data['attributes'])) {
            $data = array();
        }

        $attributes = array();
		if (!empty($data['attributes'])) {
			foreach ($data['attributes'] as $value) {
				$attributes[$value['name']] = $value;
			}
        } else {
            $data['attributes'] = array();
		}

		$data['attributes'] = $attributes;

		return $data;
    }

	public function renderMatchingTable($url, $categoryOptions, $addCategoryPick = true, $displayCategory = true, $customIdentifierHtml = '')
	{
		$mpTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_TITLE);
		$mpAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_CDISCOUNT_VARMATCH_MP_ATTRIBUTE);
		$mpOptionalAttributeTitle = str_replace('%marketplace%', ucfirst($this->marketplace), ML_GENERAL_VARMATCH_MP_OPTIONAL_ATTRIBUTE);

		ob_start();
		?>
		<form method="post" id="matchingForm" action="<?php echo toURL($url, array(), true); ?>">
			<table id="variationMatcher" class="attributesTable">
				<tbody>
				<tr class="headline">
					<td colspan="3"><h4><?php echo $mpTitle ?></h4></td>
				</tr>
				<tr id="mpVariationSelector">
					<th><?php echo ML_LABEL_MAINCATEGORY ?></th>
					<td class="input">
						<table class="inner middle fullwidth categorySelect">
							<tbody>
							<tr>
								<td>
									<div class="hoodCatVisual" id="PrimaryCategoryVisual">
										<select id="PrimaryCategory" name="PrimaryCategory" style="width:100%">
											<?php echo $categoryOptions ?>
										</select>
									</div>
								</td>
								<?php if ($addCategoryPick) { ?>
									<td class="buttons">
										<input class="fullWidth ml-button smallmargin mlbtn-action" type="button"
											   value="<?php echo ML_GENERIC_CATEGORIES_CHOOSE ?>" id="selectPrimaryCategory"/>
									</td>
								<?php } ?>
							</tr>
							</tbody>
						</table>
					</td>
					<td class="info"></td>
				</tr>
				<tr class="spacer">
					<td colspan="3">&nbsp;</td>
				</tr>
				</tbody>
				<tbody id="tbodyDynamicMatchingHeadline" style="display:none;">
				<tr class="headline">
					<td colspan="1"><h4><?php echo $mpAttributeTitle ?></h4></td>
					<td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
				</tr>
				</tbody>
				<tbody id="tbodyDynamicMatchingInput" style="display:none;">
				<tr>
					<th></th>
					<td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
					<td class="info"></td>
				</tr>
				</tbody>
				<tbody id="tbodyDynamicMatchingOptionalHeadline" style="display:none;">
					<tr class="headline">
						<td colspan="1"><h4><?php echo $mpOptionalAttributeTitle ?></h4></td>
						<td colspan="2"><h4><?php echo ML_GENERAL_VARMATCH_MY_WEBSHOP_ATTRIB ?></h4></td>
					</tr>
				</tbody>
				<tbody id="tbodyDynamicMatchingOptionalInput" style="display:none;">
					<tr>
						<th></th>
						<td class="input"><?php echo ML_GENERAL_VARMATCH_SELECT_CATEGORY ?></td>
						<td class="info"></td>
					</tr>
				</tbody>
			</table>
			<p id="categoryInfo" style="display: none"><?php echo ML_GENERAL_VARMATCH_CATEGORY_INFO ?></p>
			<table class="actions">
				<thead>
				<tr>
					<th><?php echo ML_LABEL_ACTIONS ?></th>
				</tr>
				</thead>
				<tbody>
				<tr class="firstChild">
					<td>
						<table>
							<tbody>
							<tr>
								<td class="firstChild">
									<button type="button" class="ml-button ml-reset-matching">
										<?php echo ML_GENERAL_VARMATCH_RESET_MATCHING ?></button>
								</td>
								<td></td>
								<td class="lastChild">
									<input type="submit" value="<?php echo ML_GENERAL_VARMATCH_SAVE_BUTTON ?>"
										   class="ml-button mlbtn-action">
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</form>
		<?php
		return ob_get_clean();
	}

    public function renderDuplicateField($item, $idKey, $blAjax = false)
    {
        // run js to filter our already selected dropdown values in duplicate
        $this->selectFieldOptionRemoverScript();
        global $magnaConfig;
        $config = &$magnaConfig['db'][$this->mpID];
        $idKey = str_replace('.', '_', $idKey);

        $html = '';
        ob_start();
        if ($blAjax) {
            $aValue = array('defaults' => array(''));
        } elseif (!isset($config[$item['key']]['defaults'])) {
            $aValue = array('defaults' => array('1'));
        } else {
            if (!is_array($item['params']['subfields']['method']['selectedValues'])) {
                $aValue = array('defaults' => json_decode($item['params']['subfields']['method']['selectedValues']));
            } else {
                $aValue = $config[$item['key']];
            }
        }

        $cssClasses = !empty($item['cssClasses']) ? implode(' ', $item['cssClasses']) : '';
        ?>
    <div id="<?php echo $idKey ?>">
        <table class="<?php echo $idKey ?> nostyle nowrap valigntop <?php echo $cssClasses ?>" width="100%">
            <tbody>
            <?php
            if (isset($aValue['defaults'])) {
                for ($i = 0; $i < count($aValue['defaults']); $i++) { ?>
                    <tr class="row1 bottomDashed">
                        <td>
                            <?php
                            $field = $item;
                            $field['type'] = $item['subtype'];
                            if (isset($field['params'])) {
                                $field['params']['currentIndex'] = $i;
                            }

                            unset($field['subtype']);
                            $field['key'] = $item['key'].'][values][';
                            $value = null;
                            if (isset($aValue['values']) && isset($aValue['values'][$i])) {
                                $value = $aValue['values'][$i];
                            }

                            echo $this->renderInput($field, $value);
                            ?>
                        </td>
                        <td>
                            <input value="<?php echo $aValue['defaults'][$i]; ?>"
                                   name="<?php echo $item['key'].'[defaults][]' ?>" type="hidden"
                                   class="<?php echo $idKey ?>"/>
                            <input type="button" value="+" class="ml-button plus">
                            <input type="button" value="&#8211;" class="ml-button minus">
                        </td>
                    </tr>
                <?php }
            } ?>
            </tbody>
        </table>
        <?php if (!$blAjax) { ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function () {
                $('#<?php echo $idKey; ?>').on('click', 'input.ml-button.plus', function () {
                    var $tableBox = $('#<?php echo $idKey; ?>');
                    if ($tableBox.parent('td').find('table').length == 1) {
                        $tableBox.find('input.ml-button.minus').fadeIn(0);
                    }
                    myConsole.log();
                    jQuery.blockUI(blockUILoading);
                    jQuery.ajax({
                        type: 'POST',
                        url: '<?php echo toURL($this->url, array('kind' => 'ajax'), true); ?>',
                        data: <?php echo json_encode(array_merge(
                            $item,
                            array(
                                'action' => 'duplicate',
                                'kind' => 'ajax',
                            )
                        )); ?>,
                        success: function (data) {
                            jQuery.unblockUI();
                            $tableBox.append(data);
                        },
                        error: function () {
                            jQuery.unblockUI();
                        },
                        dataType: 'html'
                    });
                });
                $('#<?php echo $idKey; ?>').on('click', 'input.ml-button.minus', function () {
                    $(this).closest('tr').remove();
                });
            });
            /*]]>*/</script></div><?php
    }

        $html .= ob_get_clean();

        return $html;
    }

    private function renderInput($item, $value = null) {
        # echo print_m($item);
        global $magnaConfig;
        $config = &$magnaConfig['db'][$this->mpID];

        if (!isset($item['key'])) {
            $item['key'] = '';
        }
        if($value === null){
            $value = '';
            if (array_key_exists($item['key'], $config)) {
                $value = $config[$item['key']];
                if (is_array($value) && isset($item['default']) && is_array($item['default'])) {
                    //echo print_m($item['default'], 'default'); echo print_m($value, 'config');
                    //var_dump(isNumericArray($item['default']), isNumericArray($value));
                    if (isNumericArray($item['default']) && isNumericArray($value)) {
                        foreach ($item['default'] as $k => $v) {
                            if (array_key_exists($k, $value)) continue;
                            $value[$k] = $item['default'][$k];
                        }
                    } else {
                        $value = array_merge($item['default'], $value);
                    }
                }
            } else if (isset($item['default'])) {
                $value = $item['default'];
            }
        }

        $item['__value'] = $value;

        $idkey = str_replace('.', '_', $item['key']);

        $parameters = '';
        if (isset($item['parameters'])) {
            foreach ($item['parameters'] as $key => $val) {
                $parameters .= ' '.$key.'="'.$val.'"';
            }
        }
        if (array_key_exists('ajaxlinkto', $item)) {
            $item['ajaxlinkto']['from'] = $item['key'];
            $item['ajaxlinkto']['fromid'] = 'config_'.$idkey;
            if (array_key_exists('key', $item['ajaxlinkto'])) {
                $item['ajaxlinkto']['toid'] = 'config_'.str_replace('.', '_', $item['ajaxlinkto']['key']);
                $ajaxUpdateFuncs[] = $item['ajaxlinkto'];
            } else { # mehrere ajaxlinkto eintraege
                foreach ($item['ajaxlinkto'] as $aLiTo) {
                    if (!is_array($aLiTo) || !array_key_exists('key', $aLiTo)) continue;
                    $aLiTo['toid'] = 'config_'.str_replace('.', '_', $aLiTo['key']);
                    $ajaxUpdateFuncs[] = $aLiTo;
                }
            }
        }

        if (!isset($item['cssClasses'])) {
            $item['cssClasses'] = array();
        }

        if (isset($item['cssStyles']) && is_array($item['cssStyles'])) {
            $style = ' style="'.implode(';', $item['cssStyles']).'" ';
        } else {
            $style = '';
        }

        $html = '';
        if(!isset($item['type'])){
            return $html;
        }
        switch ($item['type']) {
            case 'extern': {
                if (!is_callable($item['procFunc'])) {
                    if (is_array($item['procFunc'])) {
                        $item['procFunc'] = get_class($item['procFunc'][0]).'->'.$item['procFunc'][1];
                    }
                    $html .= 'Function <span class="tt">\''.$item['procFunc'].'\'</span> does not exists.';
                    break;
                }
                $html .= call_user_func($item['procFunc'], array_merge($item['params'], array('key' => $item['key'])));
                break;
            }
        }
        return $html;
    }

    public function selectFieldOptionRemoverScript() {
        ?>
        <script>
            $(document).ready(function() {
                const hideUsedDeliveryModes = function () {
                    let allSelectedOptions = $("*#config_cdiscount_shippingprofile_name\\]\\[").find('option:selected').toArray();
                    let selectedDeliveryModes = $.map(allSelectedOptions, function (option, i) {
                        return $(option).val();
                    });

                    // iterate over dropdowns
                    $("*#config_cdiscount_shippingprofile_name\\]\\[").each(function () {
                        // selected option from dropdown
                        let selectedOption = $(this).find('option:selected')[0];

                        // iterate over options of each dropdown
                        $(this).find('option').each(function(i, option) {
                            if (option === selectedOption) {
                                return true;
                            }
                            if (in_array($(option).val(), selectedDeliveryModes)){
                                $(option).hide();
                            } else {
                                $(option).show();
                            }
                        });
                    });
                };

                hideUsedDeliveryModes();
                $(document).on('click', '.ml-button.minus, .ml-button.plus', function(){
                    hideUsedDeliveryModes();
                });

                $('select[id="config_cdiscount_shippingprofile_name\\]\\["]').on('change', function () {
                    hideUsedDeliveryModes();
                });

            })
        </script>
        <?php
    }
}
