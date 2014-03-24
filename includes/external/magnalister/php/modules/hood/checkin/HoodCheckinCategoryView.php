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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimpleCheckinCategoryView.php');

class HoodCheckinCategoryView extends SimpleCheckinCategoryView {
	protected $priceConfig = array();
	
	public function __construct($cPath = 0, $settings = array(), $sorting = false, $search = '') {
		global $_MagnaSession;
		$settings = array_merge(array(
			'selectionName'   => 'checkin',
			'selectionValues' => array (
				'quantity' => null
			)
		), $settings);
		$sDeletedFilter = '';
		if ((isset($_POST['FilterBy']) && ((int)$_POST['FilterBy'] != 0))
			|| (isset($_GET['FilterBy']) && ((int)$_GET['FilterBy'] != 0))
		) {
			if (isset($_GET['kind']) && ($_GET['kind'] == 'ajax')) {
				try {
					$this->getInventoryDeletedBy($_MagnaSession['mpID'], $_POST['step']=='default'?null:strtoupper($_POST['step']), (int)$_POST['offset'],(int)$_POST['limit'],true);
					echo json_encode(array('success' => true));
				} catch (Exception $oEx) {
					$oInfo=json_decode($oEx->getMessage());
					echo json_encode($oInfo);
				}
				exit();
			} else {
				/* Wenn ENUM mit Strings eine Zahl bekommt wird diese als Index interpretiert, daher werden hier die Indizes verwendet. */
				$sFilterBy = isset($_POST['FilterBy']) ? $_POST['FilterBy'] : $_GET['FilterBy'];
				$this->sFilterBy = $sFilterBy;
				if ($sFilterBy == '-1') {
					$sDeletedFilter = " AND deletedBy !='empty' ";
				} else {
					$sDeletedFilter = ' AND deletedBy='.(int)$sFilterBy.' ';
				}
			}
		}
		$preparedItems = array_unique(array_merge(
			(array)MagnaDB::gi()->fetchArray('
				SELECT DISTINCT '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model'
						: 'products_id'
					).' 
				  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.'
				 WHERE Verified = "OK"
				       AND mpID="'.$_MagnaSession['mpID'].'"'.$sDeletedFilter.'
			', true)
		));
		#echo print_m($preparedItems, '$preparedItems');

		if (!empty($preparedItems)) {
			if (getDBConfigValue('general.keytype', '0') == 'artNr') {
				$filter = array(
					'join' => '',
					'where' => 'p.products_model IN ("'.implode('", "', MagnaDB::gi()->escape($preparedItems)).'")'
				);
			} else {
				$filter = array(
					'join' => '',
					'where' => 'p2c.products_id IN ("'.implode('", "', $preparedItems).'")'
				);
			}
		} else {
			$filter = array(
				'join' => '',
				'where' => '0=1'
			);
		}
		#echo print_m(array($filter),'array($filter)');

		$this->setCat2ProdCacheQueryFilter(array($filter));

		parent::__construct($cPath, $settings, $sorting, $search);
		
		if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
			$this->simplePrice->setCurrency(getCurrencyFromMarketplace($this->_magnasession['mpID']));
		}
		
		$this->priceConfig = HoodHelper::loadPriceSettings($this->_magnasession['mpID']);
	}
	
	protected function setupProductsQuery($fields = '', $from = '', $where = '') {
		if ($this->sFilterBy !== null) {
			$sort = $this->getSorting();
		
			if (!empty($this->allowedProductIDs)) {
				$whereProducs = 'p.products_id IN ('.implode(', ', $this->allowedProductIDs).')';
			} else {
				$whereProducs = '(0 = 1)'; // false again... ZOMG
			}
			$this->productsQuery = '
			SELECT p.products_tax_class_id, p.products_id, pd.products_name, p.products_model,
			       p.products_quantity, p.products_image, p.products_price
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			       '.(($from != '') ? (', '.$from) : '').'
			 WHERE p.products_id = pd.products_id AND pd.language_id = "'.(int)$_SESSION['languages_id'].'" AND
			       '.(($this->showOnlyActiveProducts) ? 'p.products_status<>0 AND' : '').'
			       '.$whereProducs.' '.(($where != '') ? ('AND '.$where) : '').' ORDER BY '.$sort['prod'];
		} else {
			return parent::setupProductsQuery($fields = '', $from = '', $where = '');
		}
	}
	
	protected function getInventoryDeletedBy($mpID, $sFilter = null, $offset = 0, $limit = 100, $blForce = false) {
		$sFilter = $sFilter === null ? 'default' : $sFilter;
		if (
			!isset($_SESSION['magna_deletedFilter'][$mpID][$sFilter])
			|| (($_SESSION['magna_deletedFilter'][$mpID][$sFilter] + 1800) < time())
			|| $blForce
		) {
			$iMaxExecutionTime = ini_get('max_execution_time');
			if( $iMaxExecutionTime > 300 || $iMaxExecutionTime == 0){
				$iMaxExecutionTime = 300;
			}
			$iMaxExecutionTime = $iMaxExecutionTime * 0.3;//30%
			$_SESSION['magna_deletedFilter'][$mpID][$sFilter] = time();
			try {
				$request = array(
					'ACTION' => 'GetInventory',
					'SUBSYSTEM' => 'hood',
					'MARKETPLACEID' => $mpID,
					'LIMIT' => $limit,
					'OFFSET' => $offset,
					'ORDERBY' => 'DateAdded',
					'SORTORDER' => 'DESC',
				);
				if ($sFilter != null) {
					$request['FILTER'] = $sFilter;
				} 
				$result = MagnaConnector::gi()->submitRequest($request);
				if (!empty($result['DATA'])) {
					foreach ($result['DATA'] as $item) {
						if (!empty($item['MasterSKU'])) {
							$pID = magnaSKU2pID($item['MasterSKU']);
						} else {
							$pID = magnaSKU2pID($item['SKU']);
						}						
						if ($pID != 0) {//dont exists
							MagnaDB::gi()->update(TABLE_MAGNA_HOOD_PROPERTIES, array(
								'deletedBy' => $item['deletedBy']
									), array(
								'products_id' => $pID,
								'mpID' => $mpID
							));
						}
					}
				}
				$numberofitems = (int)$result['NUMBEROFLISTINGS'];
				if (($numberofitems - $offset - $limit) > 0) { //recursion
					$offset += $limit;
					$limit = (($offset + $limit) >= $numberofitems)
						? $numberofitems - $offset
						: $limit;
					if ( time() >= (int)$_SERVER['REQUEST_TIME'] + $iMaxExecutionTime ){
						throw new Exception(json_encode(array(
							'params' => array(
								'offset' => $offset,
								'limit' => $limit,
							),
							'info'=>array(
								'current' => $offset,
								'total' => $numberofitems,
							)
						)));
					}
					$this->getInventoryDeletedBy( $mpID, $sFilter, $offset, $limit, true );
				}
			} catch (MagnaException $e) {
			}
		}
	}
	
	public function getAdditionalHeadlines() {
		return '
			<td class="lowestprice">'.ML_HOOD_LABEL_HOOD_PRICE.'</td>
			<td class="lowestprice">'.ML_HOOD_LISTING_TYPE.'</td>
			<td class="lowestprice">'.ML_HOOD_DURATION.'</td>';
	}

	public function getAdditionalCategoryInfo($cID, $data = false) {
		return '
			<td>&mdash;</td>
			<td>&mdash;</td>
			<td>&mdash;</td>';
	}

	public function getAdditionalProductInfo($pID, $data = false) {
		$matchRow = MagnaDB::gi()->fetchRow('
			SELECT StartPrice, ListingType, ListingDuration
			  FROM '.TABLE_MAGNA_HOOD_PROPERTIES.' 
			 WHERE '.((getDBConfigValue('general.keytype', '0') == 'artNr')
						? 'products_model="'.MagnaDB::gi()->escape($data['products_model']).'"'
						: 'products_id="'.$pID.'"'
					).'
			       AND mpID="'.$this->_magnasession['mpID'].'"
		');
		
		$listingDefine = 'ML_HOOD_LISTINGTYPE_'.strtoupper($matchRow['ListingType']);
		$textListingType = (defined($listingDefine) ? constant($listingDefine) : $matchRow['ListingType']);
		$durationDefine = 'ML_HOOD_LABEL_LISTINGDURATION_'.strtoupper($matchRow['ListingDuration']);
		$textListingDuration = (defined($durationDefine) ? constant($durationDefine) : $matchRow['ListingDuration']);
		
		if ($matchRow['ListingType'] == 'shopProduct') {
			$textListingDuration = ML_LABEL_UNLIMITED;
		}
		
		$listingType = ($matchRow['ListingType'] == 'classic') ? 'Auction' : 'Fixed';
		
		$textHoodPrice = array();
		if ($listingType == 'Auction') {
			if ((float)$matchRow['StartPrice'] > 0) {
				$price = $this->simplePrice->setPrice($matchRow['StartPrice'])->format();
				$textHoodPrice[] = ML_HOOD_LABEL_STARTPRICE.': '.$price;
			}
			if ($this->priceConfig['Auction']['BuyItNow']['UseBuyItNow']) {
				$price = $this->simplePrice
					->setFinalPriceFromDB($pID, $this->mpID, $this->priceConfig['Auction']['BuyItNow'])
					->format();
				$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
			}
		} else if ($listingType == 'Fixed') {
			$price = $this->simplePrice
				->setFinalPriceFromDB($pID,$this->mpID, $this->priceConfig['Fixed'])
				->format();
			$textHoodPrice[] = ML_HOOD_BUYITNOW.': '.$price;
		}
		
		if (empty($textHoodPrice)) {
			$textHoodPrice = '&mdash;';
		} else {
			$textHoodPrice = implode('<br>', $textHoodPrice);
		}
		
		return '
			<td title="'.ML_HOOD_PRICE_CALCULATED_TOOLTIP.'">'.$textHoodPrice.'</td>
			<td>'.$textListingType.'</td>
			<td>'.$textListingDuration.'</td>';
	}
	
	protected function getEmptyInfoText() {
		if (empty($this->search)) {
			return ML_HOOD_TEXT_NO_MATCHED_PRODUCTS;
		} else {
			return parent::getEmptyInfoText();
		}
	}

	protected function renderDeletedArticlesSelector() {
		$sPropertiesTable = TABLE_MAGNA_HOOD_PROPERTIES;
		global $_MagnaSession;
		$html = '
			<form id="deletedArticlesSelection" name="deletedArticlesSelection" method="POST" action="'.toURL(
				array('mp' => $this->mpID), array('mode' => 'checkin')
			).'">
				<input type="hidden" name="timestamp" value="'.time().'"/>
				<select name="FilterBy">
					 <option value="0">'.ML_OPTION_DELETED_ARTICLES_ENUM_DEFAULT.'</option>
					 <option value="-1"'.($this->sFilterBy=='-1'?' selected="selected"':'').'>'.sprintf(ML_OPTION_DELETED_ARTICLES_ENUM_NOT,constant('ML_MODULE_'.strtoupper($this->_magnasession['currentPlatform']))).'</option>';
		$aEnum = MagnaDB::gi()->fetchArray('SHOW COLUMNS FROM '.$sPropertiesTable.'');
		/* Could be done with WHERE Field="deleteBy" but this doesn't work with MySQL 4. */
		foreach ($aEnum as $aEnumRow) {
			if ($aEnumRow['Field'] == 'deletedBy') {
				$aEnum = $aEnumRow;
				break;
			}
		}
		
		$sEnum = substr($aEnum['Type'], 5, -1);
		foreach (explode(',', $sEnum) as $iKey => $sValue) {
			$sValue = substr($sValue, 1, -1);
			$sConst = 'ML_OPTION_DELETED_ARTICLES_ENUM_'.strtoupper($sValue);
			if (!defined($sConst)) {
				$sValue = '';
			} else {
				$sValue = sprintf(
					constant($sConst),
					constant('ML_MODULE_'.strtoupper($this->_magnasession['currentPlatform']))
				);
			}
			if ($sValue != '') {
				$html .= '
					<option value="'.($iKey + 1).'"'.(
						$this->sFilterBy != null && ($this->sFilterBy == $iKey + 1)
							? ' selected="selected"'
							: ''
					).'>'.$sValue.'</option>';
			}
		}
		$html .= '
				</select>
			</form>';
		if (!isset($_SESSION['magna_deletedFilter'][$_MagnaSession['mpID']]['DELETED']) ||  (($_SESSION['magna_deletedFilter'][$_MagnaSession['mpID']]['DELETED'] + 1800) < time())) {
			$html.='
				<div  style="margin:2em 1em; width:100px" id="deletedArticlesSelectionDialog" class="dialog2" title="'.ML_STATUS_FILTER_SYNC_ITEM .'">
					<div class="progressBarContainer" data-step="deleted" style="margin-bottom:1em">
						<div class="progressBar" style="width: 0;"></div>
						<div class="progressPercent">0%</div>
					</div>
					<div class="progressBarContainer" data-step="default" style="margin-bottom:1em">
						<div class="progressBar" style="width: 0;"></div>
						<div class="progressPercent">0%</div>
					</div>
					<p class="successBoxBlue">'.ML_STATUS_FILTER_SYNC_CONTENT.'</p>
					<p class="successBox" style="display:none">'.STATUS_FILTER_SYNC_SUCCESS.'</p>
				</div>
				<script type="text/javascript">/*<![CDATA[*/
					$(document).ready(function() {
						/**
						 * using interval to emulate synchronous (a)jax - problems with webkit-browser
						 * @see http://bugs.jquery.com/ticket/8819
						 */
						$("form#deletedArticlesSelection").change(function() {
							var iLimit = 100;
							var iStep=0;
							var iOffset = 0;
							var blNext = true;
							var eForm = this;
							var iInterval = 500;
							var eDialog = $("#deletedArticlesSelectionDialog");
//							var fDebug = function(mLog) {console.log(mLog)}
							var fDebug = function() {}
							var fAjax = function() {
								fDebug([iLimit,iOffset,async2sync]);
								if(blNext){
									fDebug("next");
									blNext=false;
									var iTime = new Date().getTime();
									var sCurrentStep="";
									oSteps=eDialog.find(".progressBarContainer .progressPercent");
									oSteps.each(function(){
										if(parseInt($(this).text().replace("%",""))<100&&sCurrentStep==""){
										sCurrentStep=$(this).parent().attr("data-step");
										}
									});
									$.ajax({
										url: $(eForm).attr("action") + "&kind=ajax",
										type: $(eForm).attr("method"),
										data: $(eForm).serialize() + "&limit=" + iLimit + "&offset=" + iOffset+"&step="+sCurrentStep,
										success: function(data) {
											var json = $.parseJSON( data );
											if (typeof json.success != "undefined") {
												var iDuration = 500;
												iOffset=0;
												iLimit=100;
												iStep++;
											}
											if (typeof json.success != "undefined" && iStep==oSteps.length) {
												eDialog.find(".successBoxBlue").css("display", "none");
												eDialog.find(".successBox").css("display", "block");
												window.clearInterval(async2sync);
												jQuery.blockUI(blockUILoading);
												var fPercent=100;
												eForm.submit();
											} else {
												blNext=true;
												if(typeof json.info!=="undefined"){
													var fPercent = (json.info.current/json.info.total)*100;
													iLimit=json.params.limit;
													iOffset=json.params.offset;
												}else{
													var fPercent = 100;
												}
												var iDuration = new Date().getTime() - iTime;
											}
											eDialog.find("[data-step=\""+sCurrentStep+"\"] .progressBar").css({
												width: fPercent + "%",
												transitionDuration: iDuration + "ms"
											});
											eDialog.find("[data-step=\""+sCurrentStep+"\"] .progressPercent").html(Math.round(fPercent) + "%");
										},
										beforeSend: function() {
											if(eDialog.is(":hidden")){
												fDebug("show");
												eDialog.jDialog({
													buttons: {}, 
													height: "auto", 
													width: 400
												});
											}
										}
									});
								}
							}
							var async2sync=window.setInterval(function(){fAjax()},iInterval);
						});
					});
				/*]]>*/</script>
			';
		} else {
			$html.='
				<script type="text/javascript">/*<![CDATA[*/
					$(document).ready(function() {
						$("form#deletedArticlesSelection").change(function() {
							jQuery.blockUI(blockUILoading);
							this.submit();
						});
					});
				/*]]>*/</script>
			';
		}
		return $html;
	}
	
	public function printForm() {
		$this->appendTopHTML('<div class="right">'.$this->renderDeletedArticlesSelector().'</div>');
		return parent::printForm();
	}
	
}
