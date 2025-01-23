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
 * $Id: magnacompatible.php 1174 2011-07-30 17:49:04Z derpapst $
 *
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class MagnaCompatMarketplace {
	const GENERICRESOURCE = 'MagnaCompatible';
	
	protected $marketplace = '';
	protected $mpID = 0;
	protected $moduleConf = array();
	
	protected $resources = array();
	
	protected $authConfigKeys = array();
	protected $isAuthed = false;
	
	protected $isAjax = false;
	
	protected $requiredConfigKeys = array();
	protected $missingConfigKeys = array();
	
	protected $specificResource = false;
	
	protected $pages = array();
	
	public function __construct($marketplace) {
		global $_magnaQuery, $_MagnaSession, $_modules, $_url;
		
		$this->marketplace = $marketplace;
		
		// $this->specificResource can be set by the inheriting class!
		if ($this->specificResource === false) {
			$this->specificResource = strtolower($this->marketplace);
		}
		$this->resources = array (
			'query' => &$_magnaQuery,
			'session' => &$_MagnaSession,
			'url' => &$_url,
		);
		$this->mpID = $this->resources['session']['mpID'];
		
		$this->moduleConf = $_modules[$this->marketplace];
		$this->mpConf = $this->getResourcePath('config');
		if ($this->mpConf !== false) {
			require($this->mpConf);
			$this->mpConf = $mpconfig;
		} else {
			$this->mpConf = array();
		}

		MagnaConnector::gi()->setSubsystem($this->moduleConf['settings']['subsystem']);
		MagnaConnector::gi()->setAddRequestsProps(array(
		 	'MARKETPLACEID' => $this->mpID
		));
		
		loadDBConfig($this->mpID);

		$this->resources['query']['mode'] = getCurrentModulePage();
		if (isset($_GET['debugmode'])
			&& preg_match('/^[A-Za-z0-9]+$/', $_GET['debugmode'])
			&& file_exists(DIR_MAGNALISTER_MODULES.$this->specificResource.'/'.$_GET['debugmode'].'.php')
		) {
			$this->resources['query']['debugmode'] = $_GET['debugmode'];
		}
		$this->resources['query']['messages'] = array();
		
		$this->isAjax = isset($_GET['kind']) && ($_GET['kind'] == 'ajax');
		
		require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');
		require_once(DIR_MAGNALISTER_MODULES.$this->specificResource.'/'.ucfirst($this->marketplace).'Helper.php');
		require_once(DIR_MAGNALISTER_MODULES.strtolower(self::GENERICRESOURCE).'/MagnaCompatibleBase.php');
		
		$this->authed = $this->verifyAuth();
		
		$this->initApiConfigValuesClass();
		
		$this->extraChecks();
		
		$this->loadRequiredConfigKeys();
		if (!allRequiredConfigKeysAvailable(
			$this->requiredConfigKeys, $this->mpID, false, $this->missingConfigKeys
		)) {
			$this->resources['query']['mode'] = 'conf';
		} else {
			/* Einstellen aus ErrorLog * /
			if (isset($_POST['errIDs']) && isset($_POST['action']) && ($_POST['action'] == 'retry') &&
				($_SESSION['post_timestamp'] != $_POST['timestamp'])
			) {
				$_SESSION['post_timestamp'] = $_POST['timestamp'];
				require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/ComparisonShopping/ComparisonShoppingCheckinSubmit.php');
				$cS = new ComparisonShoppingCheckinSubmit(array(
					'marketplace' => $_Marketplace
				));
				if ($cS->makeSelectionFromErrorLog()) {
					$_magnaQuery['mode'] = 'checkin';
					$_magnaQuery['view'] = 'submit';
				}
			}
			*/
		}

        $this->checkCurrency();

        $this->prepareAvailablePages();
		$this->determineView();
		$this->loadPage();
	}
	
	protected function initApiConfigValuesClass() {
		$class = ucfirst($this->marketplace).'ApiConfigValues';
		$file = DIR_MAGNALISTER_MODULES.$this->specificResource.'/classes/'.$class.'.php';
		if (file_exists($file)) {
			require_once($file);
			# http://3v4l.org/am7HB
			if (version_compare(PHP_VERSION, '5.2.2', '>=')) {
				$instance = call_user_func_array($class.'::gi', array());
			} else {
				$instance = call_user_func_array(array($class, 'gi'), array());
			}
			$r = $instance->init($this->resources['session']);
		}
	}
	
	protected function getResourcePath($resource) {
		if (is_string($this->specificResource) && !empty($this->specificResource)) {
			$lpath = DIR_MAGNALISTER_MODULES.$this->specificResource.'/'.$resource.'.php';
			if (file_exists($lpath)) {
				return $lpath;
			}
		}
		$lpath = DIR_MAGNALISTER_MODULES.strtolower(self::GENERICRESOURCE).'/'.$resource.'.php';
		if (file_exists($lpath)) {
			return $lpath;
		}
		return false;
	}
	
	protected function loadResource($resource) {
		$lpath = $this->getResourcePath($resource);
		if ($lpath !== false) {
			require_once($lpath);
			return true;
		}
		return false;
	}
	
	protected function loadAuthKeys() {
		if (!isset($this->mpConf['auth']['authkeys']) || !is_array($this->mpConf['auth']['authkeys'])) {
			$this->authConfigKeys = array(
				$this->marketplace.'.username',
				$this->marketplace.'.password',
			);
			return;
		}
		$this->authConfigKeys = array();
		foreach ($this->mpConf['auth']['authkeys'] as $key) {
			$this->authConfigKeys[] = $this->marketplace.'.'.$key;
		}
	}
	
	protected function verifyAuth() {
		$this->loadAuthKeys();
		
		if (!(
			array_key_exists('conf', $_POST) && 
			allRequiredConfigKeysAvailable($this->authConfigKeys, $this->mpID, $_POST['conf'])
		)) {
			$authed = getDBConfigValue($this->marketplace.'.authed', $this->mpID);
			if (!is_array($authed)) {
				$authed = array('state' => false, 'expire' => 0);
			}
		
			if (!$authed['state'] || ($authed['expire'] <= time())) {
				try {
					$r = MagnaConnector::gi()->submitRequest(array(
						'ACTION' => 'IsAuthed',
					));
					$authState = true;
				} catch (MagnaException $e) {
					$authState = false;
		
					if ($e->getCode() != MagnaException::UNKNOWN_ERROR) {
						$e->setCriticalStatus(false);
					}
					$authError = $e->getErrorArray();
					setDBConfigValue($this->marketplace.'.autherror', $this->mpID, $authError, false);
					$_GET['mode'] = $this->resources['query']['mode'] = 'conf';
				}
				$authed = array (
					'state' => $authState,
					'expire' => time() + 60 * 30 // 30 Min
				);
				setDBConfigValue($this->marketplace.'.authed', $this->mpID, $authed, true);
			}
			return $authed;
		}
		return false;
	}
	
	protected function loadRequiredConfigKeys() {
		$this->requiredConfigKeys = $this->moduleConf['requiredConfigKeys'];
	}
	
	protected function determineView() {
		if (!is_array($this->moduleConf['pages'][$this->resources['query']['mode']])) {
			return;
		}
		global $_shitHappend;
		
		$views = $this->moduleConf['pages'][$this->resources['query']['mode']]['views'];
		if (isset($_GET['view']) && array_key_exists($_GET['view'], $views)) {
			$this->resources['query']['view'] = $_GET['view'];
		} else {
			$this->resources['query']['view'] = array_first(array_keys($views));
		}
		
		if (isset($_shitHappend) && $_shitHappend && ($this->resources['query']['mode'] == 'listings')) {
			$this->resources['query']['view'] = 'failed';
		}
	}
	
	protected function prepareAvailablePages() {
		if (array_key_exists('pages', $this->mpConf)) {
			$this->pages = $this->mpConf['pages'];
		}
		if (!array_key_exists('conf', $this->pages)) {
			$this->pages['conf'] = array (
				'resource' => 'configure',
				'class' => 'MagnaCompatConfigure',
				'params' => array ('authConfigKeys', 'missingConfigKeys'),
			);
		}
	}
	
	protected function pagebegin() {
		if ($this->isAjax) {
			return;
		}
		include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
	
		if (!empty($this->resources['query']['messages'])) {
			foreach ($this->resources['query']['messages'] as $message) {
				echo $message;
			}
		}
		
		/* DEBUG * /
		if (isset($checkInResult)) {
			echo '<textarea class="debugBox" wrap="off">checkInResult :: '.print_r($checkInResult, true).'</textarea>';
		} */
	
		if (isset($magnaExceptionOccured)) {
			echo $magnaExceptionOccured;
		}
	}
	
	protected function pageexit() {
		if ($this->isAjax) {
			exit();
		}
		
		include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
		require(DIR_WS_INCLUDES . 'application_bottom.php');
		exit();
	}
	
	protected function hacks() {
		setDBConfigValue(array($this->marketplace.'.catmatch.mpshopcats', 'val'), $this->mpID, true);
	}
	
	protected function loadPage() {
		$this->hacks();
		$this->pagebegin();
		
		if (isset($this->resources['query']['debugmode'])) {
			require(DIR_MAGNALISTER_MODULES.$this->specificResource.'/'.$this->resources['query']['debugmode'].'.php');
			$this->pageexit();
			return;
		}
		
		if (array_key_exists($this->resources['query']['mode'], $this->pages)) {
			$page = $this->pages[$this->resources['query']['mode']];
		} else {
			$page = $this->pages['conf'];
		}
		if ($this->loadResource($page['resource']) && class_exists($page['class'])) {
			$params = array();
			if (!isset($page['params']) || !is_array($page['params'])) {
				$page['params'] = array();
			}
			$page['params'][] = 'specificResource';
			$page['params'][] = 'marketplace';
			$page['params'][] = 'mpID';
			$page['params'][] = 'isAjax';
			$page['params'][] = 'resources';
			
			foreach ($page['params'] as $p) {
				if (isset($this->$p)) {
					$params[$p] = &$this->$p;
				}
			}
			$c = new $page['class']($params);
			$c->process();
		} else {
			if ($this->isAjax) {
				echo '{"error": "This is not supported"}';
			} else {
				echo 'This is not supported';
			}
		}
		$this->pageexit();
	}

	# extra checks for a platform, if required
	protected function extraChecks() { }


    protected function checkCurrency() {
        if (!MagnaDB::gi()->recordExists(TABLE_CURRENCIES, array(
                'code' => getCurrencyFromMarketplace($this->mpID)
            )) && $this->marketplace !== 'googleshopping') {
            $this->resources['query']['mode'] = 'conf';
            $this->resources['query']['messages'][] = '<p class="errorBox">' . sprintf(
                    ML_GENERIC_ERROR_CURRENCY_NOT_IN_SHOP,
                    getCurrencyFromMarketplace($this->mpID)
                ) . '</p>';
        }
    }

}
