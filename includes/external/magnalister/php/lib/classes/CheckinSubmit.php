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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

abstract class CheckinSubmit {
	protected $mpID = 0;
	protected $marketplace = '';
	protected $_magnasession = array();
	protected $_magnashopsession = array();
	protected $magnaConfig = array();
	protected $url = array();
	
	protected $settings = array();
	
	protected $selection = array();
	protected $variationCount = 0;
	protected $badItems = array();
	protected $disabledItems = array();
	
	protected $submitSession = array();
	protected $initSession = array();
	
	protected $ajaxReply = array();
	
	protected $lastRequest = array();
	
	protected $simpleprice = null;
	
	protected $ignoreErrors = false;
	
	private $_timer;

	protected $summaryAddText = ''; # extra Text, je nach Plattform (momentan belegt bei eBay und Hitmeister)

	protected $deleteSelection = true;
	protected $lastResponse = array();

	protected $additionalSplitProducts = array();
	
	public function __construct($settings = array()) {
		global $_MagnaSession, $_MagnaShopSession, $magnaConfig, $_magnaQuery, $_url;
		
		$this->_timer = microtime(true);
		
		$this->mpID = $_MagnaSession['mpID'];
		$this->marketplace = $settings['marketplace'];
		
		$this->settings = array_merge(array(
			'itemsPerBatch'   => 50,
			'selectionName'   => 'checkin',
			'language'        => getDBConfigValue($settings['marketplace'].'.lang', $_MagnaSession['mpID'], $_SESSION['languages_id']),
			'currency'        => DEFAULT_CURRENCY,
			'mlProductsUseLegacy' => true,
		), $settings);

		$this->_magnasession = &$_MagnaSession;
		$this->_magnashopsession = &$_MagnaShopSession;
		$this->magnaConfig = &$magnaConfig;
		$this->url = $_url;
		$this->realUrl = array (
			'mp' => $this->mpID,
			'mode' => (isset($_magnaQuery['mode']) ? $_magnaQuery['mode'] : ''),
			'view' => (isset($_magnaQuery['view']) ? $_magnaQuery['view'] : '')
		);
		
		$this->simpleprice = new SimplePrice();
		/* /!\ Muss in erbenden Klassen entsprechend des Marketplaces gesetzt werden! /!\ */
		$this->simpleprice->setCurrency($this->settings['currency']);
		
		initArrayIfNecessary($this->_magnasession, array($this->mpID, 'submit'));
		$this->submitSession = &$this->_magnasession[$this->mpID]['submit'];
		initArrayIfNecessary($this->_magnasession, array($this->mpID, 'init'));
		$this->initSession = &$this->_magnasession[$this->mpID]['init'];
	}
	
	public function init($mode, $items = -1) {
		if ($items == -1) {
			$items = (int)MagnaDB::gi()->fetchOne('
				SELECT count(*)
				  FROM '.TABLE_MAGNA_SELECTION.'
				 WHERE mpID=\''.$this->mpID.'\' AND
				       selectionname=\''.$this->settings['selectionName'].'\' AND
				       session_id=\''.session_id().'\'
			  GROUP BY selectionname
			');
		}

		/* Init all resources needed */
		$this->_magnasession[$this->mpID]['submit'] = array();
		$this->submitSession = &$this->_magnasession[$this->mpID]['submit'];
		$this->submitSession['state'] = array (
			'total' => $items,
			'submitted' => 0,
			'success' => 0,
			'failed' => 0
		);
		$this->submitSession['proceed'] = true;
		$this->submitSession['mode'] = $mode;
		$this->submitSession['initialmode'] = $mode;
		#echo print_m($this, __METHOD__.'('.__LINE__.')');
	}
	
	abstract public function makeSelectionFromErrorLog();
	
	protected function initSelection($offset, $limit) {
		$newSelectionResult = MagnaDB::gi()->query('
		    SELECT ms.pID, ms.data
		      FROM '.TABLE_MAGNA_SELECTION.' ms
		 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON pd.products_id = ms.pID AND pd.language_id = "'.$this->settings['language'].'"
		     WHERE ms.mpID=\''.$this->mpID.'\'
		           AND ms.selectionname=\''.$this->settings['selectionName'].'\'
		           AND ms.session_id=\''.session_id().'\'
		  ORDER BY pd.products_name ASC
		     LIMIT '.$offset.','.$limit.'
		');
		$this->selection = array();
		while ($row = MagnaDB::gi()->fetchNext($newSelectionResult)) {
			$this->selection[$row['pID']] = unserialize($row['data']);
		}
	}

	protected function deleteSelection() {
		foreach ($this->selection as $pID => &$data) {
			$this->badItems[] = $pID;
		}
		$this->badItems = array_merge(
			$this->badItems,
			$this->disabledItems
		);
		if (!empty($this->badItems)) {
			MagnaDB::gi()->delete(
				TABLE_MAGNA_SELECTION, 
				array(
					'mpID' => $this->mpID,
					'selectionname' => $this->settings['selectionName'],
					'session_id' => session_id()
				),
                'AND pID IN ('.implode(', ', $this->badItems).')'
			);
		}
	}
	
	/**
	 * Verify the data before it is processed. 
	 * Allows fixing of missing data or removing the product before bad things may happen.
	 */
	protected function checkSingleItem($pID, $product, $data) {
		return true;
	}

	protected function getProduct($pID) {
		if ($this->settings['mlProductsUseLegacy']) {
			$product = MLProduct::gi()->getProductByIdOld($pID, $this->settings['language']);
		} else {
			$product = MLProduct::gi()->getProductById($pID);
		}
		return $product;
	}
	
	protected function setUpMLProduct() {
		// reset everything to the defaults
		MLProduct::gi()->resetOptions();
		
		// Set the language
		MLProduct::gi()->setLanguage($this->settings['language']);
		
		// Set a db matching (e.g. 'ManufacturerPartNumber')
		/*
		MLProduct::gi()->setDbMatching('ManufacturerPartNumber', array (
			'Table' => 'products',
			'Column' => 'products_model',
			'Alias' => 'products_id',
		));
		//*/
		
		// Set the list of allowed options_ids.
		//MLProduct::gi()->setVariationDimensionBlacklist(array('1'));
		// or
		//MLProduct::gi()->setVariationDimensionWhitelist(array('1', '2', ...));
		
		// Use multi dimensional variations
		// MLProduct::gi()->useMultiDimensionalVariations(true);
	}
	
	protected function populateSelectionWithData() {
		$this->setUpMLProduct();
		
		foreach ($this->selection as $pID => &$data) {
			if (!isset($data['submit']) || !is_array($data['submit'])) {
				$data['submit'] = array();
			}
			
			$product = $this->getProduct($pID);
			if (!$this->checkSingleItem($pID, $product, $data) || !is_array($product)) {
				$this->badItems[] = $pID;
				unset($this->selection[$pID]);
				continue;
			}

			$mpID = $this->mpID;
			$marketplace = $this->marketplace;

			/* {Hook} "CheckinSubmit_AppendData": Enables you to extend or modify the product data.<br>
			   Variables that can be used: 
			   <ul><li>$pID: The ID of the product (Table <code>products.products_id</code>).</li>
			       <li>$product: The data of the product (Tables <code>products</code>, <code>products_description</code>,
			           <code>products_images</code> and <code>products_vpe</code>).</li>
			       <li>$data: The data of the product from the preparation tables of the marketplace.</li>
			       <li>$mpID: The ID of the marketplace.</li>
			       <li>$marketplace: The name of the marketplace.</li>
			   </ul>
			   <code>$product</code> and <code>$data</code> will be used to generate the <code>AddItems</code> request.
			 */
			if (($hp = magnaContribVerify('CheckinSubmit_AppendData', 1)) !== false) {
				require($hp);
			}

			$this->appendAdditionalData($pID, $product, $data);

			/* {Hook} "CheckinSubmit_PostAppendData": Enables you to extend or modify the product data, after our data processing.<br>
			   Variables that can be used: same as for CheckinSubmit_AppendData.
			 */
			if (($hp = magnaContribVerify('CheckinSubmit_PostAppendData', 1)) !== false) {
				require($hp);
			}
		}
	}

	protected function requirementsMet($product, $requirements, &$failed) {
		if (!is_array($product) || empty($product) || !is_array($requirements) || empty($requirements)) {
			$failed = array();
			return false;
		}
		$failed = array();
		foreach ($requirements as $req => $needed) {
			if (!$needed) continue;
			if (empty($product[$req]) && ($product[$req] !== '0')) {
				if (array_key_exists('Variations', $product)) {
					$blFail = false;
					foreach ($product['Variations'] as $variation) {
						if (empty($variation[$req]) && ($variation[$req] !== '0')) {
							$blFail = true;
							break;
						}
					}
				} else {
					$blFail = true;
				}
				if ($blFail) {
					$failed[] = $req;
				}
			}
		}
		return empty($failed);
	}
	
	abstract protected function appendAdditionalData($pID, $product, &$data);
	abstract protected function filterSelection();

	abstract protected function generateRequestHeader();
	
	abstract protected function generateRedirectURL($state);

	protected function processException($e) {}

	protected function sendRequest($abort = false, $echoRequest = false) {
		$retResponse = array ();
		
		$request = $this->generateRequestHeader();
		$request['SUBSYSTEM'] = MagnaConnector::gi()->getSubSystem();
		$request['DATA'] = array();
		
		foreach ($this->selection as $pID => &$data) {
			$request['DATA'][] = $data['submit'];
		}
		arrayEntitiesToUTF8($request['DATA']);
		
		$this->preSubmit($request);
		
		$this->lastRequest = $request;
		
		$this->ajaxReply['ignoreErrors'] = true;
		
		try {
			/* Hau raus! :D */
			if ($abort || $echoRequest) {
				echo print_m(json_indent(json_encode($request)));
			}
			if ($abort) {
				die();
			}
			#file_put_contents(dirname(__FILE__).'/submit.log', var_dump_pre($request, '$request', true));
			$checkInResult = MagnaConnector::gi()->submitRequest($request);
			#sleep(5);
			#$checkInResult = array ('STATUS' => 'SUCCESS', 'ERRORS' => array());
			//$this->ajaxReply['result'] = $checkInResult;
			
			$this->processSubmitResult($checkInResult);
			if (!array_key_exists('state', $this->submitSession)) {
				$this->submitSession['state'] = array();
			}
			if (!array_key_exists('success', $this->submitSession['state'])) {
				$this->submitSession['state']['success'] = 0;
			}
			if (!array_key_exists('failed', $this->submitSession['state'])) {
				$this->submitSession['state']['failed'] = 0;
			}
			$this->submitSession['state']['success'] += count($this->selection) - $this->variationCount;
			$this->submitSession['state']['failed']  += count($this->badItems);
			
			if (isset($this->submitSession['api'])) {
				unset($this->submitSession['api']);
			}
			$retResponse = $checkInResult;

		} catch (MagnaException $e) {
			$this->submitSession['state']['failed'] += count($this->badItems) + count($this->selection) - $this->variationCount;

			$this->ajaxReply['exception'] = $e->getMessage();
			$this->submitSession['api']['exception'] = $e->getErrorArray();
			
			$subsystem = $e->getSubsystem();
			if (($subsystem != 'Core') && ($subsystem != 'PHP') && ($subsystem != 'Database')) {
				$this->ajaxReply['ignoreErrors'] = $this->ignoreErrors;
			} else {
				$this->ajaxReply['ignoreErrors'] = false;
			}
			
			//$this->ajaxReply['request'] = $this->submitSession['api']['exception']['REQUEST'];
			if (is_array($this->submitSession['api']['exception']) && array_key_exists('REQUEST', $this->submitSession['api']['exception'])) {
				unset($this->submitSession['api']['exception']['REQUEST']);
			}
			$this->ajaxReply['redirect'] = toURL(array(
				'mp' => $this->realUrl['mp'],
				'mode' => $this->realUrl['mode']
			));
			$retResponse = $this->submitSession['api']['exception'];
			
			$this->processException($e);
		}
		return $retResponse;
	}
	
	protected function preSubmit(&$request) {}
	
	abstract protected function postSubmit();
	abstract protected function processSubmitResult($result);

	protected function generateCustomErrorHTML() {
		return false;
	}

	protected function afterPopulateSelectionWithData() {

	}

	public function submit($abort = false) {
		if (isset($_SESSION['magna_deletedFilter'])) {
			// Reset inventory infos. @see CheckinCategoryView
			unset($_SESSION['magna_deletedFilter'][$this->mpID]);
		}
		$this->initSelection(0, $this->settings['itemsPerBatch']);
		$this->ajaxReply['ignoreErrors'] = array_key_exists('ignoreErrors', $this->ajaxReply) ? $this->ajaxReply['ignoreErrors'] : $this->ignoreErrors;
		$this->ajaxReply['itemsPerBatch'] = $this->settings['itemsPerBatch'];
		
		/* Spaetestens beim 2. Durchgang muessen die Artukel hinzugefuegt werden,
		   da sie sonst die Artikel des 1. Durchganges zuvor loeschen wuerden. */
		if ($this->submitSession['state']['submitted'] > 0) {
			$this->submitSession['mode'] = 'ADD';
		}
		
		$this->submitSession['state']['submitted'] += count($this->selection);

		$this->populateSelectionWithData();
		$this->afterPopulateSelectionWithData();
		$this->filterSelection();
		
		/* Wenn Artikel deaktiviert wurden (nicht fehlgeschlagen, z. B. Artikelanzahl == 0), 
		   werden sie nicht mit uebermittelt */
		$this->submitSession['state']['total'] -= count($this->disabledItems);
		$this->submitSession['state']['submitted'] -= count($this->disabledItems);
		/*
		echo print_m($this->selection);
		die();
		*/
		
		if (!empty($this->selection)) {
			MagnaConnector::gi()->setTimeOutInSeconds(600);
			@set_time_limit(600);
			$this->lastResponse = $this->sendRequest($abort || isset($_GET['abort']));
			$this->afterSendRequest();
			MagnaConnector::gi()->resetTimeOut();
		} else {
			$this->submitSession['state']['failed'] += count($this->badItems);
		}
		
		if (isset($this->submitSession['selectionFromErrorLog']) && !empty($this->submitSession['selectionFromErrorLog'])) {
			$this->submitSession['selectionFromErrorLog'] = array_diff($this->submitSession['selectionFromErrorLog'], $this->badItems);
		}

		//$this->ajaxReply['debug'] = print_m($this->submitSession, 'submitSession');
		$this->ajaxReply['state'] = $this->submitSession['state'];

		if (!empty($this->submitSession['api'])) {
			$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = $this->ajaxReply['ignoreErrors'];
			$this->ajaxReply['api'] = $this->submitSession['api'];
			/* Firstly let us process the exceptions. If we analyse them and decide, that some of them are not
			 * critical, they'll not apper... */
			$this->ajaxReply['api']['customhtml'] = $this->generateCustomErrorHTML();
			/* ... in the following list. */
			$this->ajaxReply['api']['html'] = MagnaError::gi()->exceptionsToHTML(false);
			
			#print_r($this->ajaxReply['api']['exception']);
		}

		if (empty($this->submitSession['api']) || $this->ajaxReply['ignoreErrors']) {
			if (!isset($this->ajaxReply['reprocessSelection']) || !$this->ajaxReply['reprocessSelection']) {
				if ($this->deleteSelection === true) {
					$this->deleteSelection();
				}
			}
			if ($this->submitSession['state']['submitted'] >= $this->submitSession['state']['total']) {
				$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = false;
				/* Auswertung... */
				if ($this->submitSession['state']['success'] != $this->submitSession['state']['total']) {
					/* Irgendwelche Fehler sind aufgetreten */
					$this->ajaxReply['redirect'] = $this->generateRedirectURL('fail');
				} else {
					$this->ajaxReply['redirect'] = $this->generateRedirectURL('success');
				}
				
				if ($this->submitSession['state']['success'] > 0) {
					$this->postSubmit();
					if (isset($this->submitSession['selectionFromErrorLog'])) {
						unset($this->submitSession['selectionFromErrorLog']);
					}
				}
				$this->ajaxReply['finaldialogs'] = $this->getFinalDialogs();
			} else {
				$this->ajaxReply['proceed'] = $this->submitSession['proceed'] = true;
			}
		}
		
		$this->ajaxReply['timer'] = microtime2human(microtime(true) -  $this->_timer);
		$this->ajaxReply['memory'] = memory_usage();
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Content-type: application/json');
		return json_indent(json_encode($this->ajaxReply));
	}

	protected function getFinalDialogs() {
		/* Example:
		return array (
			array (
				'headline' => 'Eine Ueberschrift',
				'message' => 'Der Inhalt'
			),
			...
		);
		*/
		return array();
	}
	
	public function getLastRequest() {
		return $this->lastRequest;
	}
	
	public function renderBasicHTMLStructure() {
		//$this->initSelection(0, $this->settings['itemsPerBatch']);
		//$this->populateSelectionWithData();
		
		//$html = print_m($this->selection, '$this->selection').'
		$html = '
			<div id="checkinSubmit">
				<div id="checkinSubmitSubwrap">
					<h1 id="threeDots">
						<span id="headline">'.ML_HEADLINE_SUBMIT_PRODUCTS.'</span><span class="alldots"
							><span class="dot">.</span><span class="dot">.</span><span class="dot">.</span>&nbsp;
						</span>
					</h1>
					<p id="checkinSubmitProductsNotice">'.ML_NOTICE_SUBMIT_PRODUCTS.'</p>
					<div id="apiException" style="display:none;"><p class="errorBox">'.ML_ERROR_SUBMIT_PRODUCTS.'</p></div>
					<div id="uploadprogress" class="progressBarContainer">
						<div class="progressBar"></div>
						<div class="progressPercent"></div>
					</div>
					<br>
					<div id="checkinSubmitStatus" class="paddingBottom"></div>
					<div style="display: none; text-align: left; background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.2); border-radius: 3px 3px 3px 3px; margin-bottom: 1em; padding: 0 7px 7px;" id="checkinSubmitDebug">'.print_m($this->submitSession, 'submitSession').'</div>
				 </div>
			 </div>
			
		';
		
		ob_start();?>
<script type="text/javascript" src="<?php echo DIR_MAGNALISTER_WS; ?>js/classes/CheckinSubmit.js?<?php echo CLIENT_BUILD_VERSION?>"></script>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	var csaj = new GenericCheckinSubmitAjaxController();
	csaj.setTriggerURL('<?php echo toURL($this->realUrl, array('kind' => 'ajax'), true); ?>');
	csaj.addLocalizedMessages({
		'TitleInformation' : <?php echo json_encode(ML_LABEL_INFORMATION); ?>,
		'TitleAjaxError': 'Ajax '+<?php echo json_encode(ML_ERROR_LABEL); ?>,
		'LabelStatus': <?php echo json_encode(ML_GENERIC_STATUS); ?>,
		'LabelError': <?php echo json_encode(ML_ERROR_LABEL); ?>,
		'MessageUploadFinal': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS_SUMMARY.$this->summaryAddText); ?>,
		'MessageUploadStatus': <?php echo json_encode(ML_STATUS_SUBMIT_PRODUCTS); ?>,
		'MessageUploadFatalError': <?php echo json_encode(ML_STATUS_SUBMIT_PHP_ERROR); ?> 
	});
	csaj.setInitialUploadStatus('<?php echo $this->submitSession['state']['total']; ?>');
	csaj.doAbort(<?php echo isset($_GET['abort']) ? 'true' : 'false'; ?>);
	csaj.runSubmitBatch();
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();	
		ob_end_clean();
		return $html;
	}
		
	protected function afterSendRequest() {
		
	}

	/**
	 * Matched attributes array is a few levels deep and this method forms new structure. It is an array which
	 * will have as a key, shop key (name id) of matched shop attribute, and as value array of keys for values which are
	 * matched (Value ids). That way it is easy to check if some attribute or some of its values is matched.
	 *
	 * @param array $allMatchedAttributes
	 * @param array $variationTheme
	 * @param array $variationBlackList
	 * @return array
	 */
	protected function getMatchedVariationAttributesCodeValueId(
		$allMatchedAttributes,
		$variationTheme = array(),
		$variationBlackList = array())
	{
		$matchedAttributeFormatted = array();
		// Go through all matched attributes.
		if (is_array($allMatchedAttributes)) {
			foreach ($allMatchedAttributes as $mpAttributeCode => $matchedAttribute) {
				if (!is_array($matchedAttribute['Values']) ||
					$this->attributeMatchedValueIsLiteral($matchedAttribute) ||
					in_array($mpAttributeCode, $variationBlackList) ||
					(!empty($variationTheme) && !in_array($mpAttributeCode, $variationTheme[key($variationTheme)]))
				) {
					continue;
				}
	
				$matchedAttributeFormatted[$matchedAttribute['Code']][$mpAttributeCode] = array();
	
				// Go through all its values.
				foreach ($matchedAttribute['Values'] as $matchedAttributeValue) {
					// Check if that value is already added. If it is don`t add it again.
					if (!in_array($matchedAttributeValue['Shop']['Key'], $matchedAttributeFormatted[$matchedAttribute['Code']])) {
						// Form new array which will contain final result.
						$matchedAttributeFormatted[$matchedAttribute['Code']][$mpAttributeCode][] = $matchedAttributeValue['Shop']['Key'];
					}
				}
			}
		}

		return $matchedAttributeFormatted;
	}

	private function attributeMatchedValueIsLiteral($attribute)
	{
		return in_array($attribute['Code'], array('freetext', 'attribute_value', 'database_value'));
	}

	/**
	 * Sets all information about variations. Checks for every variation if it should be submitted, skipped or split.
	 *
	 * @param $productVariations
	 * @param $variantForSubmit
	 * @param $variationsForSubmit
	 * @param $matchedAttributesCodeValueId
	 * @param $rawAmConfiguration
	 * @param $masterProductSku
	 * @param $data
	 * @param $product
	 * @param array $variationThemeBlacklist
	 */
	protected function setAllVariationsDataAndMasterProductsSKUs(
		$productVariations,
		&$variantForSubmit,
		&$variationsForSubmit,
		$matchedAttributesCodeValueId,
		$rawAmConfiguration,
		$masterProductSku,
		$data,
		$product,
		$variationThemeBlacklist = array()
	){
		$this->additionalSplitProducts = array();
		$variationTheme = $product['variation_theme'];
		$skipVariation = false;
		$masterProductTitleSuffix = array();
		$variationThemeCode = empty($variationTheme) ? 'null' : $this->getVariationThemeCode($variationTheme);
		$codeForSplitAll = 'splitAll';

		foreach ($productVariations['Variation'] as $varAttribute) {
			// If none of shop variation attributes are matched, send data from shop.
			if (empty($matchedAttributesCodeValueId) && $this->shouldSendShopData()) {
				$this->setProductVariant($variantForSubmit, $varAttribute, $rawAmConfiguration, $productVariations);
				continue;
			}

			if ($this->shouldSkipVariation($matchedAttributesCodeValueId, $variationTheme, $varAttribute)) {
				$skipVariation = true;
				break;
			}

			if ($this->shouldSplitVariation(
				$variationTheme,
				$variationThemeBlacklist,
				$varAttribute,
				$matchedAttributesCodeValueId,
				$varAttribute['NameId'],
				$rawAmConfiguration
			)) {
				if ($variationThemeCode === $codeForSplitAll) {
					$masterProductSku = $this->getVariationSku($productVariations);
				} else {
					// If shop variational attribute is not matched product should be split by that attribute. Set masterProductSku.
					$masterProductSku .= '-' . $varAttribute['Name'] . '-' . $varAttribute['Value'];
				}
				$masterProductTitleSuffix[] = $varAttribute['Name'] . ' - ' . $varAttribute['Value'];
			} else {
				$this->setProductVariant($variantForSubmit, $varAttribute, $rawAmConfiguration, $productVariations);
			}
		}

		if ($skipVariation) {
			return;
		}

		$variantForSubmit = $this->setAdditionalVariantProperties($variantForSubmit, $data, $product, $productVariations);

		// If product has new ItemTitle, set it.
		if (!empty($masterProductTitleSuffix)) {
			if (!isset($variantForSubmit['ItemTitle'])) {
				$variantForSubmit['ItemTitle'] = $variantForSubmit['Title'];
			}
			$variantForSubmit['ItemTitle'] .= ' : ' . join(', ', $masterProductTitleSuffix);
		}
		
		$variationsForSubmit[$masterProductSku][] = $variantForSubmit;
	}
	
	protected function shouldSendShopData() 
	{
		return false;
	}

	protected function getVariationSku($productVariations)
	{
		return $productVariations['MarketplaceSku'];
	}

	protected function setAdditionalVariantProperties($variantForSubmit, $data, $product, $productVariations)
	{
		return $variantForSubmit;
	}

	protected function isVariationInBlacklist($variationThemeBlacklist, $varAttribute, $rawAmConfiguration)
	{
		return false;
	}

	/**
	 * Gets variations that should not be skipped.
	 *
	 * @param $matchedAttributesCodeValueId
	 * @param $variationTheme
	 * @param $variationAttribute
	 * @return array
	 * @internal param $rawAmConfiguration
	 */
	protected function shouldSkipVariation($matchedAttributesCodeValueId, $variationTheme, $variationAttribute)
	{
		$matchedValueIds = array();

		foreach ($matchedAttributesCodeValueId as $matchedAttributeKey => $matchedValuesForMpAttribute) {
			foreach ($matchedValuesForMpAttribute as $valueIds) {
				if (empty($matchedValueIds[$matchedAttributeKey])) {
					$matchedValueIds[$matchedAttributeKey] = $valueIds;
				} else {
					$matchedValueIds[$matchedAttributeKey] = array_intersect($matchedValueIds[$matchedAttributeKey], $valueIds);
				}
			}
		}

		// Go through all variation definitions
		$attributeCode = $variationAttribute['NameId'];
		// Check if attributes that make dimension are matched and if their values are matched
		if ((!empty($variationTheme) && !$this->isShopVariationValueMatched(
					$variationTheme, $variationAttribute['ValueId'], $matchedAttributesCodeValueId, $attributeCode)
			) ||
			(empty($variationTheme) && isset($matchedAttributesCodeValueId[$attributeCode]) &&
				!in_array($variationAttribute['ValueId'], $matchedValueIds[$attributeCode])
			)) {
			// If any value that makes variation definition is not matched that variation should be skipped.
			// $allValuesMatched flag will be used for skipping.
			return true;
		}

		return false;
	}


	protected function isShopVariationValueMatched(
		$variationTheme,
		$variationValueId,
		$matchedAttributesCodeValueId,
		$attributeCode
	) {
		$variationThemeCode = key($variationTheme);
		$codeForSplitAll = 'splitAll';

		if (empty($variationTheme) || $variationThemeCode === $codeForSplitAll) {
			return true;
		}

		foreach ($variationTheme[$variationThemeCode] as $mpKey) {
			if (isset($matchedAttributesCodeValueId[$attributeCode][$mpKey]) &&
				!in_array($variationValueId, $matchedAttributesCodeValueId[$attributeCode][$mpKey])
			) {
				return false;
			}
		}

		return true;
	}

	private function getVariationThemeCode($variationTheme)
	{
		return key($variationTheme);
	}

	protected function shouldSplitVariation(
		$variationTheme,
		$variationThemeBlacklist,
		$varAttribute,
		$matchedAttributesCodeValueId,
		$matchedShopVariationCode,
		$rawAmConfiguration
	) {
		if (!isset($matchedAttributesCodeValueId[$matchedShopVariationCode]) ||
			$this->isVariationInBlacklist($variationThemeBlacklist, $varAttribute, $rawAmConfiguration)
		) {
			return true;
		}

		if (empty($variationTheme)) {
			return false;
		}

		$variationThemeCode = $this->getVariationThemeCode($variationTheme);
		$codeForSplitAll = 'splitAll';

		if ($variationThemeCode === $codeForSplitAll || !isset($matchedAttributesCodeValueId[$matchedShopVariationCode])
		) {
			return true;
		}

		$variationThemeAttributes = $variationTheme[$variationThemeCode];

		foreach ($matchedAttributesCodeValueId[$matchedShopVariationCode] as $mpKey => $matchedValues) {
			if (!in_array($mpKey, $variationThemeAttributes)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Standard way for setting product variants for request.
	 *
	 * @param $productVariant
	 * @param $varAttribute
	 * @param $variations
	 */
	protected function setProductVariant(&$productVariant, $varAttribute, $rawAmConfiguration, $variations)
	{
		$productVariant['Variation'][$varAttribute['Name']] = $varAttribute['Value'];
	}

	/**
	 * Prepares data for request based on the checking if product is split or not.
	 *
	 * @param $variations
	 * @param $data
	 */
	protected function prepareVariationDataForSubmitRequest($variations, &$data)
	{
		$data['submit']['Variations'] = array();
		$itemTitle = $data['submit']['ItemTitle'];

		foreach ($variations as $variationMasterSku => $dimensions) {
			// If product is not split, it will have the same SKU as master product. Then send it on standard way.
			if ($variationMasterSku === $data['submit']['SKU']) {
				$data['submit']['Variations'] = $dimensions;
				continue;
			}

			if (isset($dimensions[0]['ItemTitle'])) {
				$itemTitle = $dimensions[0]['ItemTitle'];
			}

			// If product is not split form new master product with its variations and add it to array which will be sent.
			$masterProduct = $this->createVariantMasterProduct($dimensions, $variationMasterSku, $itemTitle, $data['submit']);
			$this->additionalSplitProducts[] = $masterProduct;
		}
	}

	/**
	 * When product is split it is necessary to create new master product which will have its own variations.
	 *
	 * @param $dimensions
	 * @param $variationMasterSku
	 * @param $itemTitle
	 * @param $productToClone
	 * @return mixed
	 */
	protected function createVariantMasterProduct($dimensions, $variationMasterSku, $itemTitle, $productToClone)
	{
		if (count($dimensions) === 1 && isset($dimensions[0]['Variation']) && $dimensions[0]['Variation'] == array()) {
			// If everything is split and there are no variation dimensions variation product should be sent as master product.
			$masterProduct = array_merge($productToClone, $dimensions[0]);
			$masterProduct['IsSplit'] = 1;
			$masterProduct['ItemTitle']  = $itemTitle;
			unset($masterProduct['Variations']);
			unset($masterProduct['Variation']);
			return $masterProduct;
		}

		// Basic case is that new master product will be the same as old master product just with a new SKU and
		// its own variations.
		$masterProduct = $productToClone;
		$masterProduct['SKU'] = $variationMasterSku;
		$masterProduct['ItemTitle'] = $itemTitle;
		$masterProduct['Variations'] = $dimensions;
		// If product is split add flag for product.
		$masterProduct['IsSplit'] = intval($variationMasterSku != $productToClone['SKU']);
		return $masterProduct;
	}

}
