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
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
require_once DIR_MAGNALISTER_INCLUDES . 'lib/v3fake/Alias/Request.php';

abstract class MLOrderlistAmazonAbstract {

	/**
	 * @var int
	 */
	protected $iRowsPerPage = 25;

	/**
	 * @var array
	 */
	protected $aListConfig = array();
	protected $aMagnaSession = null;
	protected $aMagnaConfig = null;

	/**
	 * @var array
	 */
	protected $aUrl = null;

	/**
	 * @var SimplePrice $oPrice
	 */
	protected $oPrice = null;

	/**
	 * @var ML_Request_Model_Request $oApiRequest
	 */
	protected $oApiRequest = null;
	protected $aDependencys = array();
	protected $aSelectionData = array();

	/**
	 * value of magnalister_selection.selectionname
	 * @return string
	 */
	abstract protected function getSelectionName();

	public function getSelectionTableName() {
		return TABLE_MAGNA_GLOBAL_SELECTION;
	}

	protected function getSelectionKey() {
		return 'AmazonOrderID';
	}

	public function __construct() {
		global $_MagnaSession, $magnaConfig, $_url;
		$this->aMagnaSession = &$_MagnaSession;
		$this->aMagnaConfig = &$magnaConfig;
		$this->aUrl = &$_url;
		$this->oPrice = new SimplePrice();
		$this->oPrice->setCurrency($this->isAjax() ? DEFAULT_CURRENCY : getCurrencyFromMarketplace($this->aMagnaSession['mpID']));
		if (getDBConfigValue(array($this->aMagnaSession['currentPlatform'] . '.exchangerate', 'update'), $this->aMagnaSession['mpID'], false)) {
			$success = false;
			$oSimplePrice = new SimplePrice(null, getCurrencyFromMarketplace($this->aMagnaSession['mpID']));
			$oSimplePrice->updateCurrencyByService($success);
		}
		$this->buildRequest()
			->addRequestSort();
		$this->addDependencies();
//		$this->hookAddOrderlistDependencies();
	}

	protected function addDependencies() {

	}

	/**
	 * @return MLOrderlistAmazonAbstract Description
	 */
	protected function buildRequest() {
		return $this;
	}

	protected function addRequestSort() {
		$sRequestSorting = $this->getRequest('sorting');
		if ($sRequestSorting != '') {
			$aRequestSorting = explode('-', $sRequestSorting);
			if (
				count($aRequestSorting) == 2 && in_array($aRequestSorting[1], array('asc', 'desc'))
			) {
				foreach ($this->aListConfig as $aListConfig) {
					foreach (array('sort', 'altSort') as $sKey) {
						if (
							isset($aListConfig['head'][$sKey]) && $aRequestSorting[0] == $aListConfig['head'][$sKey]['param']
						) {
							$this->oApiRequest->set(array(
								'ORDERBY' => $aListConfig['head'][$sKey]['field'],
								'SORTORDER' => strtoupper($aRequestSorting[1])
							));
						}
					}
				}
			}
		} else {// default
			$this->oApiRequest->set(array(
				'ORDERBY' => 'PurchaseDate',
				'SORTORDER' => 'DESC'
			));
		}
		return $this;
	}

	public function injectDependency($sDependencyName, $aDependencyConfig = array()) {
		return $this->addDependency($sDependencyName, $aDependencyConfig);
	}

	protected function addDependency($sDependencyName, $aDependencyConfig = array()) {

		require_once(DIR_MAGNALISTER_MODULES_AMAZON_ORDERLIST . '/Dependency/' . $sDependencyName . '.php');

		$oDependency = new $sDependencyName;
		$sMd5 = md5(json_encode(array(get_class($oDependency), $aDependencyConfig)));
		$aFilterRequest = $this->getRequest('filter');
		$mFilterRequest = isset($aFilterRequest[$oDependency->getIdent()]) ? $aFilterRequest[$oDependency->getIdent()] : null;
		$aActionRequest = $this->getRequest('action');
		$mActionRequest = isset($aActionRequest[$oDependency->getIdent()]) ? $aActionRequest[$oDependency->getIdent()] : null;
		$oDependency
			->setList($this)
			->setFilterRequest($mFilterRequest)
			->setActionRequest($mActionRequest)
			->setMagnaSession($this->aMagnaSession)
			->setMagnaConfig($this->aMagnaConfig)
			->setConfig($aDependencyConfig)
			->setQuery($this->oApiRequest)
		;
		$this->aDependencys[$sMd5] = $oDependency;
		return $this;
	}

	protected function getDependencies() {
		return $this->aDependencys;
	}

	protected function renderDependency($oDependency, $sMethod, $sTemplateFolder) {
		$sTemplate = $oDependency->{'get' . $sMethod . 'Template'}();
		if (empty($sTemplate)) {
			$sOut = '';
		} else {
			ob_start();
			$this->renderTemplate(
				'dependency/' . $sTemplateFolder . '/' . $sTemplate, array('oObject' => $oDependency)
			);
			$sOut = ob_get_contents();
			ob_end_clean();
		}

		return $sOut;
	}

	protected function renderDependencyHeader($oDependency) {
		return $this->renderDependency($oDependency, 'header', 'header');
	}

	protected function renderDependencyActionBottomLeft($oDependency) {
		return $this->renderDependency($oDependency, 'actionBottomLeft', 'action');
	}

	protected function renderDependencyActionBottomRight($oDependency) {
		return $this->renderDependency($oDependency, 'actionBottomRight', 'action');
	}

	protected function renderDependencyActionBottomCenter($oDependency) {
		return $this->renderDependency($oDependency, 'actionBottomCenter', 'action');
	}

	protected function renderDependencyFilterLeft($oDependency) {
		return $this->renderDependency($oDependency, 'filterLeft', 'filter');
	}

	protected function renderDependencyFilterRight($oDependency) {
		return $this->renderDependency($oDependency, 'filterRight', 'filter');
	}

	protected function renderDependencyActionTop($oDependency) {
		return $this->renderDependency($oDependency, 'actionTop', 'action');
	}

	protected function init() {
		$aFilterKeyType = array('in' => null, 'notIn' => null);
		foreach ($this->getDependencies() as $oDependency) {
			$oDependency->manipulateQuery();
			$aDependencyFilterKeyTypes = $oDependency->getKeyTypeFilter();
			if (isset($aDependencyFilterKeyTypes['in']) && is_array($aDependencyFilterKeyTypes['in'])) {
				if ($aFilterKeyType['in'] === null) {
					$aFilterKeyType['in'] = $aDependencyFilterKeyTypes['in'];
				} else {
					$aFilterKeyType['in'] = array_intersect($aFilterKeyType['in'], $aDependencyFilterKeyTypes['in']);
				}
			}
			if (isset($aDependencyFilterKeyTypes['notIn']) && is_array($aDependencyFilterKeyTypes['notIn']) && !empty($aDependencyFilterKeyTypes['notIn'])) {
				if ($aFilterKeyType['notIn'] === null) {
					$aFilterKeyType['notIn'] = $aDependencyFilterKeyTypes['notIn'];
				} else {
					$aFilterKeyType['notIn'] = array_unique(array_merge($aDependencyFilterKeyTypes['notIn'], $aFilterKeyType['notIn']));
				}
			}
		}
		foreach ($aFilterKeyType as $sType => $aFilterIdents) {
			if ($aFilterIdents !== null) {
				$this->oApiRequest->where("
					p." . ((getDBConfigValue('general.keytype', '0') == 'artNr') ? 'products_model' : 'products_id') . " " .
					(($sType == 'in') ? "IN" : "NOT IN") . "
					('" . implode("', '", MagnaDB::gi()->escape($aFilterIdents)) . "')"
				);
			}
		}
		foreach ($this->getDependencies() as $oDependency) {
			$oDependency->executeAction();
		}
	}

	public function render() {
		ob_start();
		if ($this->isAjax()) {
			$this->renderAjax();
		} else {
			$this->init();
			$this->renderTemplate($this->getMainTemplateName());
		}
		$sOut = ob_get_contents();
		ob_end_clean();
		return $sOut;
	}

	protected function renderAjax(){
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Thu, 01 Jan 1970 00:00:00 GMT"); // Datum in der Vergangenheit
		header('Content-Type: text/plain');
		$this->init();
		echo '';
	}
	
	abstract protected function getMainTemplateName();

	/**
	 * @param string name of template
	 * @param array assoc for template vars
	 * <code><?php
	 * 	$this->renderTemplate(
	 * 		'path/to/template/sTemplateName',
	 * 		array('foo' => 'bar')
	 * 	);// render template ./Orderlist/template/path/to/template/sTemplateName.php and add var $foo = 'bar'
	 * ?></code>
	 * @return \ProductList
	 */
	public function renderTemplate() {
		if (func_num_args() > 1) {
			extract(func_get_arg(1));
		}

		include DIR_MAGNALISTER_MODULES_AMAZON_ORDERLIST . '/templates/' . func_get_arg(0) . '.php';

		return $this;
	}

	/**
	 * @param bool $blIncludeFilter
	 * @param bool $blIncludePage
	 * @param array $aAdditionalParams
	 * @param bool $blAjax
	 * @return string
	 */
	public function getUrl($blIncludeFilter, $blIncludePage, $blIncludeSorting, $aAdditionalParams = array(), $blAjax = false) {
		$blAjax = $blAjax || isset($aAdditionalParams['kind']) && $aAdditionalParams['kind'] == 'ajax';
		return toURL($this->getUrlParameters($blIncludeFilter, $blIncludePage, $blIncludeSorting), $aAdditionalParams, $blAjax);
	}

	/**
	 * @param bool $blIncludeFilter
	 * @param bool $blIncludePage
	 * @return string
	 */
	public function getUrlParameters($blIncludeFilter, $blIncludeSorting, $blIncludePage) {
		$aUrl = $this->aUrl;
		if ($blIncludeFilter) {
			$aFilter = $this->getRequest('filter');
			foreach ($this->getDependencies() as $oFilter) {
				$sRequest = isset($aFilter[$oFilter->getIdent()]) ? $aFilter[$oFilter->getIdent()] : null;
				if (!empty($sRequest)) {
					$aUrl['filter[' . $oFilter->getIdent() . ']'] = urlencode($sRequest);
				}
			}
		}
		if ($blIncludePage) {
			$aUrl['page'] = $this->getCurrentPage();
		}
		if ($blIncludeSorting) {
			$aUrl['sorting'] = $this->getRequest('sorting');
		}
		return $aUrl;
	}

	/**
	 * @return int
	 */
	protected function getCurrentPage() {
		return ($this->getRequest('page') === null) ? 1 : $this->getRequest('page');
	}

	/**
	 * @return int
	 */
	protected function getPageCount() {
		$iPages = $this->oApiRequest->getCount() / $this->iRowsPerPage;
		if ((int) $iPages != $iPages) {
			++$iPages;
		}
		return (int) $iPages;
	}

	/**
	 * @return SimplePrice
	 */
	protected function getPrice() {
		return $this->oPrice;
	}

	/**
	 * @return array sql-result
	 */
	protected function getOrders() {
		$aResult = $this->oApiRequest->getResult();
//		echo print_m($aResult, 'result');
		return $aResult;
	}

	/**
	 * @param string $sName key of request
	 * @return null $sName not found
	 * @return mixed request
	 */
	protected function getRequest($sName) {
		if (isset($_POST[$sName])) {
			$mRequest = $_POST[$sName];
		} elseif (isset($_GET[$sName])) {
			$mRequest = $_GET[$sName];
		} else {
			$mRequest = null;
		}
		if ($sName == 'filter') {
			if (
				empty($mRequest) && isset($_SESSION['orderlistfilter']) && isset($_SESSION['orderlistfilter']['name']) && $_SESSION['orderlistfilter']['name'] == get_class($this) && isset($_SESSION['orderlistfilter']['values'])
			) {
				$mRequest = $_SESSION['orderlistfilter']['values'];
			}
			$_SESSION['orderlistfilter'] = array(
				'name' => get_class($this),
				'values' => $mRequest,
			);
		}
		return $mRequest;
	}

	/**
	 * @return bool
	 */
	public function isAjax() {
		return $this->getRequest('kind') == 'ajax';
	}

	protected function getSelectionData($aRow = null, $sFieldName = null) {
		if($aRow !== null) {
			$sSelectionKey = $this->getSelectionKey();
			if (!isset($this->aSelectionData[$aRow[$sSelectionKey]])) {
				$this->aSelectionData[$aRow[$sSelectionKey]] = MagnaDB::gi()->fetchRow("
					SELECT *
					FROM " . $this->getSelectionTableName() . "
					WHERE
						`element_id` = '" . $aRow[$sSelectionKey] . "'
						AND `session_id` = '" . session_id() . "'
						AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
						AND `selectionname` = '" . $this->getSelectionName() . "'
				");
			}
			$this->aSelectionData[$aRow[$sSelectionKey]]['data'] = json_decode($this->aSelectionData[$aRow[$sSelectionKey]]['data'], true);
			if ($sFieldName === null) {
				return $this->aSelectionData[$aRow[$sSelectionKey]];
			} else {
				return isset($this->aSelectionData[$aRow[$sSelectionKey]][$sFieldName]) ? $this->aSelectionData[$aRow[$sSelectionKey]][$sFieldName] : null;
			}
		}  else {			
			$aData = MagnaDB::gi()->fetchArray("
				SELECT *
				FROM " . $this->getSelectionTableName() . "
				WHERE
					`session_id` = '" . session_id() . "'
					AND `mpID` = '" . $this->aMagnaSession['mpID'] . "'
					AND `selectionname` = '" . $this->getSelectionName() . "'
				");
			foreach($aData as $sKey => $aOrder){
				$aData[$sKey]['data'] = json_decode($aOrder['data'], true);
			}
			return $aData;
		}
	}

	protected function isInSelection($aRow) {
		return $this->getSelectionData($aRow, 'element_id') !== null;
	}

}
