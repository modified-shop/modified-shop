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
 * $Id: MagnaDB.php 2908 2013-07-11 14:24:33Z MaW $
 *
 * (c) 2010 - 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

define('TABLE_MAGNA_CONFIG', 'magnalister_config');
define('TABLE_MAGNA_SESSION', 'magnalister_session');
define('TABLE_MAGNA_SELECTION', 'magnalister_selection');
define('TABLE_MAGNA_SELECTION_TEMPLATES', 'magnalister_selection_templates');
define('TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES', 'magnalister_selection_template_entries');
define('TABLE_MAGNA_ORDERS', 'magnalister_orders');
define('TABLE_MAGNA_VARIATIONS', 'magnalister_variations');
define('TABLE_MAGNA_AMAZON_PROPERTIES', 'magnalister_amazon_properties');
define('TABLE_MAGNA_AMAZON_ERRORLOG', 'magnalister_amazon_errorlog');
define('TABLE_MAGNA_AMAZON_APPLY', 'magnalister_amazon_apply');
define('TABLE_MAGNA_CS_ERRORLOG', 'magnalister_cs_errorlog');
define('TABLE_MAGNA_CS_DELETEDLOG', 'magnalister_cs_deletedlog');
define('TABLE_MAGNA_YATEGO_CATEGORIES', 'magnalister_yatego_categories');
define('TABLE_MAGNA_YATEGO_CUSTOM_CATEGORIES', 'magnalister_yatego_custom_categories');
define('TABLE_MAGNA_YATEGO_CATEGORYMATCHING', 'magnalister_yatego_categorymatching');
define('TABLE_MAGNA_EBAY_CATEGORIES', 'magnalister_ebay_categories');
define('TABLE_MAGNA_EBAY_PROPERTIES', 'magnalister_ebay_properties');
define('TABLE_MAGNA_EBAY_LISTINGS', 'magnalister_ebay_listings');
define('TABLE_MAGNA_EBAY_ERRORLOG', 'magnalister_ebay_errorlog');
define('TABLE_MAGNA_EBAY_DELETEDLOG', 'magnalister_ebay_deletedlog');
define('TABLE_MAGNA_TECDOC', 'magnalister_tecdoc');
define('TABLE_MAGNA_API_REQUESTS', 'magnalister_api_requests');
define('TABLE_MAGNA_MEINPAKET_CATEGORYMATCHING', 'magnalister_meinpaket_categorymatching');
define('TABLE_MAGNA_MEINPAKET_CATEGORIES', 'magnalister_meinpaket_categories');
define('TABLE_MAGNA_MEINPAKET_ERRORLOG', 'magnalister_meinpaket_errorlog');
define('TABLE_MAGNA_COMPAT_CATEGORYMATCHING', 'magnalister_magnacompat_categorymatching');
define('TABLE_MAGNA_COMPAT_CATEGORIES', 'magnalister_magnacompat_categories');
define('TABLE_MAGNA_COMPAT_ERRORLOG', 'magnalister_magnacompat_errorlog');
define('TABLE_MAGNA_COMPAT_DELETEDLOG', 'magnalister_magnacompat_deletedlog');
define('TABLE_MAGNA_HITMEISTER_PREPARE', 'magnalister_hitmeister_prepare');

define('MAGNADB_ENABLE_LOGGING', MAGNA_DEBUG && false);



class MagnaDB {
	private static $instance = NULL;
	private $mlink;
	private $resourcelink;
	private $selfConnected = false;
	private $destructed = false;

	private $query;
	private $error;
	private $result;

	private $start;
	protected $count;
	private $querytime;
	private $doLogQueryTimes = true;
	private $timePerQuery = array();

	private $availabeTables = array();

	private $escapeStrings;

	private $sessionLifetime;
	
	private $showDebugOutput = MAGNA_DEBUG;
	
	/* Caches */
	private $columnExistsInTableCache = array();

	/**
	 * Class constructor
	 */
	private function __construct($mlink = 'mdb_link') {
		global $$mlink, $_MagnaSession, $_MagnaShopSession;

		$this->link          = &$mlink;
		$this->resourcelink  = &$$mlink;
		$this->start         = microtime(true);
		$this->count         = 0;
		$this->querytime     = 0;
		$this->escapeStrings = get_magic_quotes_gpc();

		// self connected from the beginning or not... that is the question.
		$this->selfConnected = $this->selfConnect();

		$this->availabeTables = $this->fetchArray('SHOW TABLES', true);

		if ($this->tableExists(TABLE_MAGNA_SESSION)) {
			$this->sessionLifetime = (int)ini_get("session.gc_maxlifetime");
			$this->sessionGarbageCollector();

			$_MagnaSession = $this->sessionRead();
			$_MagnaShopSession = $this->shopSessionRead();
		}
		if (MAGNADB_ENABLE_LOGGING) {
			$dbt = @debug_backtrace();
			if (!empty($dbt)) {
				foreach ($dbt as $step) {
					if (strpos($step['file'], 'magnaCallback') !== false) {
						$dbt = true;
						unset($step);
						break;
					}
				}
			}
			if ($dbt !== true) {
				file_put_contents(dirname(__FILE__).'/db_guery.log', "### Query Log ".date("Y-m-d H:i:s")." ###\n\n");
			}
			unset($dbt);
		}
	}
	
	/**
	 * Singleton - gets Instance
	 */
	public static function gi($mlink = 'mdb_link') {
		if (self::$instance == NULL) {
			self::$instance = new self($mlink);
		}
		return self::$instance;
	}

	private function __clone() {}
	
	public function __destruct() {
		if (!is_object($this) || !isset($this->destructed) || $this->destructed) {
			return;
		}
		$this->destructed = true;
		
		if (!defined('MAGNALISTER_PASSPHRASE') && !defined('MAGNALISTER_PLUGIN')) {
			/* Only when this class is instantiated from magnaCallback
			   and the plugin isn't activated yet.
			*/
			$this->closeConnection();
			return;
		}
		
		$this->sessionRefresh();
		
		if (MAGNA_DEBUG && $this->showDebugOutput && function_exists('microtime2human') 
			&& (
				!defined('MAGNA_CALLBACK_MODE') || (MAGNA_CALLBACK_MODE != 'UTILITY')
			) && (stripos($_SERVER['PHP_SELF'].serialize($_GET), 'ajax') === false)
		) {
			echo '<!-- Final Stats :: QC:'.$this->getQueryCount().'; QT:'.microtime2human($this->getRealQueryTime()).'; -->';
		}
		$this->closeConnection();
	}
	
	protected function selfConnect($forceReconnect = false) {
		# Wenn keine Verbindung im klassischen Sinne besteht, selbst eine herstellen.
		if (is_resource($this->resourcelink) && !$forceReconnect) {
			return false;
		}
		
		if (USE_PCONNECT == 'true') {
			$this->resourcelink = mysql_pconnect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
		} else {
			$this->resourcelink = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
		}
		
		if (isset($_GET['MLDEBUG']) && isset($_GET['LEVEL']) && ($_GET['MLDEBUG'] === 'true') && (strtolower($_GET['LEVEL']) == 'high')) {
			echo "\n<<<< MagnaDB :: reconnect >>>>\n";
			var_dump($this->resourcelink);
		}
		
		if (!is_resource($this->resourcelink)) {
			// called in the destructor: Just leave. No need to close connection, it's lost
			if ($this->destructed) exit;
			// die is bad behaviour. But meh...
			die(
				'<span style="color:#000000;font-weight:bold;">
					DB reconnect failed.<br /><br />
					<pre style="font-weight:normal">'.print_r(array_slice(debug_backtrace(true), 4), true).'</pre><br /><br />
					<small style="color:#ff0000;font-weight:bold;">[SQL Error]</small>
				</span>'
			);
		}
		
		$vers = @mysql_get_server_info($this->resourcelink);
		if (substr($vers, 0, 1) > 4) {
			@mysql_query("SET SESSION sql_mode=''", $this->resourcelink);
		}
		mysql_select_db(DB_DATABASE, $this->resourcelink);
		
		// If the db connection was lost in __destruct() we have to close the databse connection
		// at the end of __destruct() ourselves.
		if ($this->destructed) {
			$this->selfConnected = true;
		}
		
		return true;
	}
	
	protected function closeConnection() {
		if ($this->selfConnected && is_resource($this->resourcelink) && !(defined('USE_PCONNECT') && (USE_PCONNECT == 'true'))) {
			mysql_close($this->resourcelink);
		}
	}
	
	private function prepareError() {
		$errNo = mysql_errno($this->resourcelink);
		if ($errNo == 0) {
			return '';
		}
		return mysql_error($this->resourcelink).' ('.$errNo.')';
	}

	public function logQueryTimes($b) {
		$this->doLogQueryTimes = $b;
	}

	protected function stripObjectsAndResources($a, $lv = 0) {
		if (empty($a) || ($lv >= 10)) return $a;
		//echo print_m($a, trim(var_dump_pre($lv, true)));
		$aa = array();
		foreach ($a as $k => $value) {
			$toString = '';
			// echo var_dump_pre($value, 'value');
			if (!is_object($value)) {
				$toString = $value.'';
			}
			if (is_object($value)) {
				$value = 'OBJECT ('.get_class($value).')';
			} else if (is_resource($value) || (strpos($toString, 'Resource') !== false)) {
				if (is_resource($value)) {
					$value = 'RESOURCE ('.get_resource_type($value).')';
				} else {
					$value = $toString.' (Unknown)';
				}
			} else if (is_array($value)) {
				$value = $this->stripObjectsAndResources($value, $lv + 1);
			} else if (is_string($value)) {
				$value = str_replace(M_API_DIR, '', $value);
			}
			if ($k == 'args') {
				if (is_string($value) && (strlen($value) > 5000)) {
					$value = substr($value, 0, 5000).'[...]';
				}
			}
			if (($value === DB_SERVER_PASSWORD) && (DB_SERVER_PASSWORD != null)) {
				$aa = '*****';
				break;
			}
			$aa[$k] = $value;
		}
		return $aa;
	}

	protected function fatalError($query, $errno, $error) {
		die(
			'<span style="color:#000000;font-weight:bold;">
				' . $errno . ' - ' . $error . '<br /><br />
				<pre>' . $query . '</pre><br /><br />
				<pre style="font-weight:normal">'.print_r($this->stripObjectsAndResources(debug_backtrace(true)), true).'</pre><br /><br />
				<small style="color:#ff0000;font-weight:bold;">[SQL Error]</small>
			</span>'
		);
	}

	protected function execQuery($query) {
		$i = 8;
		
		$errno = 0;
		
		$this->selfConnect();
		
		do {
			$errno = 0;
			$result = mysql_query($query, $this->resourcelink);
			if ($result === false) {
				$errno = mysql_errno($this->resourcelink);
			}
			//if (defined('MAGNALISTER_PLUGIN')) echo 'mmysql_query errorno: '.var_export($errno, true)."\n";
			if (($errno === false) || ($errno == 2006)) {
				mysql_close($this->resourcelink);
				
				usleep(100000);
				
				$this->selfConnect(true);
			}
			# Retry if '2006 MySQL server has gone away'
		} while (($errno == 2006) && (--$i >= 0));
		
		if ($errno != 0) {
			$this->fatalError($query, $errno, mysql_error($this->resourcelink));
		}
	
		return $result;
	}

	/**
	 * Send a query
	 */
	public function query($query, $verbose = false) {
		/* {Hook} "MagnaDB_Query": Enables you to extend, modify or log query that goes to the database	.<br>
		   Variables that can be used: <ul><li>$query: The SQL string</li></ul>
		 */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaDB_Query', 1)) !== false)) {
			require($hp);
		}

		$this->query = $query;
		if ($verbose || false) {
			echo function_exists('print_m') ? print_m($this->query)."\n" : $this->query."\n";
		}
		if (MAGNADB_ENABLE_LOGGING) {
			file_put_contents(dirname(__FILE__).'/db_guery.log', "### ".$this->count."\n".$this->query."\n\n", FILE_APPEND);
		}
		$t = microtime(true);
		$this->result = $this->execQuery($this->query);
		$t = microtime(true) - $t;
		$this->querytime += $t;
		if ($this->doLogQueryTimes) {
			$this->timePerQuery[] = array (
				'query' => $this->query,
				'time' => $t
			);
		}
		++$this->count;
		//echo print_m(debug_backtrace());
		if (!$this->result) {
			$this->error = $this->prepareError();
			return false;
		}

		return $this->result;
	}
	
	private function sessionGarbageCollector() {
		if ($this->tableExists(TABLE_MAGNA_SESSION)) {
			$this->query("DELETE FROM ".TABLE_MAGNA_SESSION." WHERE expire < '".(time() - $this->sessionLifetime)."' AND session_id <> '0'");
		}
		if (defined('MAGNALISTER_PLUGIN') && MAGNALISTER_PLUGIN && $this->tableExists(TABLE_MAGNA_SELECTION)) {
			$this->query("DELETE FROM ".TABLE_MAGNA_SELECTION." WHERE expires < '".gmdate('Y-m-d H:i:d', (time() - $this->sessionLifetime))."'");
		}
	}

	private function sessionRead() {
		$result = $this->fetchOne(
			"SELECT data FROM ".TABLE_MAGNA_SESSION." ".
			"WHERE session_id = '".session_id()."' AND expire > '".time()."'",
			true
		);
		if (!empty($result)) {
			return @unserialize($result);
		}
		return array();
	}

	private function shopSessionRead() {
		/* This "Session" is for all Backend users and it _never_ expires! */
		$result = $this->fetchOne(
			"SELECT data FROM ".TABLE_MAGNA_SESSION." ".
			"WHERE session_id = '0'",
			true
		);

		if (!empty($result)) {
			return @unserialize($result);
		}
		return array();
	}

	private function sessionStore($data, $sessionID) {
		if (empty($sessionID) && ($sessionID != '0')) return;
		
		$isPluginContext = defined('MAGNALISTER_PLUGIN') && MAGNALISTER_PLUGIN;
		
		// only update the session if this class was used from the plugin context
		// OR if the dirty bit is set. Avoid session updates otherwise.
		if (!($isPluginContext || (isset($data['__dirty']) && ($data['__dirty'] === true)))) {
			return;
		}
		// remove the dirty bit.
		if (isset($data['__dirty'])) {
			unset($data['__dirty']);
		}
		if ($this->recordExists(TABLE_MAGNA_SESSION, array('session_id' => $sessionID))) {
			$this->update(TABLE_MAGNA_SESSION, array(
					'data' => serialize($data),
					'expire' => (time() + (($sessionID == '0') ? 0 : $this->sessionLifetime))
				), array(
					'session_id' => $sessionID
				)
			);
		} else if (!empty($data)) {
			$this->insert(TABLE_MAGNA_SESSION, array(
				'session_id' => $sessionID,
				'data' => serialize($data),
				'expire' => (time() + (($sessionID == '0') ? 0 : $this->sessionLifetime))
			), true);
		}
	}
	
	protected function sessionRefresh() {
		global $_MagnaSession, $_MagnaShopSession;
		
		if ($this->tableExists(TABLE_MAGNA_SESSION)) {
			$this->sessionStore($_MagnaSession, session_id());
			$this->sessionStore($_MagnaShopSession, '0');
		}
		
		// only refresh selection data in magnalister_selection if this class was used from the plugin context
		if (defined('MAGNALISTER_PLUGIN') && MAGNALISTER_PLUGIN && $this->tableExists(TABLE_MAGNA_SELECTION)) {
			$this->update(
				TABLE_MAGNA_SELECTION, array(
					'expires' => gmdate('Y-m-d H:i:d', (time() + $this->sessionLifetime))
				), array(
					'session_id' => session_id()
				)
			);
		}
	}
	
	public function escape($object) {
		if (is_array($object)) {
			$object = array_map(array($this, 'escape'), $object);
		} else if (is_string($object)) {
			$tObject = $this->escapeStrings ? stripslashes($object) : $object;
			if (!is_resource($this->resourcelink)) {
				// mimic mysql_real_escape_string
				$object = str_replace(
					array('\\',   "\0",  "\n",  "\r",  "'",   '"',   "\x1a"),
					array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ),
					$tObject
				);
			} else {
				$object = mysql_real_escape_string($tObject, $this->resourcelink);
			}
		}
		return $object;
	}

	/**
	 * Get number of rows
	 */
	public function numRows($result = null) {
		if ($result === null) {
			$result = $this->result;
		}

		if ($result === false) {
			return false;
		}

		return mysql_num_rows($result);
	}

	/**
	 * Get number of changed/affected rows
	 */
	public function affectedRows() {
		return mysql_affected_rows($this->resourcelink);
	}

	/**
	 * Get number of found rows
	 */
	public function foundRows() {
		return $this->fetchOne("SELECT FOUND_ROWS()");
	}

	/**
	 * Get a single value
	 */
	public function fetchOne($query) {
		$this->result = $this->query($query);

		if (!$this->result) {
			return false;
		}

		if ($this->numRows($this->result) > 1) {
			$this->error = __METHOD__.' can only return a single value (multiple rows returned).';
			return false;

		} else if ($this->numRows($this->result) < 1) {
			$this->error = __METHOD__.' cannot return a value (zero rows returned).';
			return false;
		}

		$return = $this->fetchNext($this->result);
		if (!is_array($return) || empty($return)) {
			return false;
		}
		$return = array_shift($return);
		if ($return === null) {
			return false;
		}
		return $return;
	}

	/**
	 * Get next row of a result
	 */
	public function fetchNext($result = null) {
		if ($result === null) {
			$result = $this->result;
		}

		if ($this->numRows($result) < 1) {
			return false;
		} else {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			if (!$row) {
				$this->error = $this->prepareError();
				return false;
	 		}
		}

		return $row;
	}

	/**
	 * Fetch a row
	 */
	public function fetchRow($query) {
		$this->result = $this->query($query);

		return $this->fetchNext($this->result);
	}

	public function fetchArray($query, $singleField = false) {
		if (is_resource($query)) {
			$this->result = $query;
		} else if (is_string($query)) {
			$this->result = $this->query($query);
		}

		if (!$this->result) {
			return false;
		}

		$array = array();

		while ($row = $this->fetchNext($this->result)) {
			if ($singleField && (count($row) == 1)) {
				$array[] = array_pop($row);
			} else {
				$array[] = $row;
			}
		}

		return $array;
	}

	public function tableExists($table) {
		/* {Hook} "MagnaDB_TableExists": Enables you to modify the $table variable before the check for existance is performed in
		   case your shop uses a contrib, that messes with the table prefixes.
		 */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaDB_TableExists', 1)) !== false)) {
			require($hp);
		}
		return in_array($table, $this->availabeTables);
	}

	public function getAvailableTables($pattern = '') {
		if (empty($pattern)) return $this->availabeTables;
		$tbls = array();
		foreach ($this->availabeTables as $t) {
			if (preg_match($pattern, $t)) {
				$tbls[] = $t;
			}
		}
		return $tbls;
	}

	public function tableEmpty($table) {
		return ($this->fetchOne('SELECT * FROM '.$table.' LIMIT 1') === false);
	}

	public function mysqlVariableValue($variable) {
		$showVariablesLikeVariable = $this->fetchRow("SHOW VARIABLES LIKE '$variable'");
		if ($showVariablesLikeVariable)
			return $showVariablesLikeVariable['Value'];
		else return null;
		# nicht false zurueckgeben, denn dies koennte ein gueltiger Variablenwert sein
	}
	
	// for testing purposes. But seems useless, special characters turn out broken.
	private function useDatabaseCharset() {
		$databaseCharset = $this->mysqlVariableValue('character_set_database');
		# die Fallbacks sollten nicht noetig sein, aber vorsichtshalber
		if (empty($databaseCharset)) $databaseCharset = $this->mysqlVariableValue('character_set_server');
		if (empty($databaseCharset)) $databaseCharset = $this->mysqlVariableValue('character_set_system');
		if (empty($databaseCharset)) return;
		$this->query("SET names $databaseCharset");
	}
	
	public function mysqlSetHigherTimeout($timeoutToSet = 3600) {
		if ($this->mysqlVariableValue('wait_timeout') < $timeoutToSet) {
			$this->query("SET wait_timeout = $timeoutToSet");
		}
		if ($this->mysqlVariableValue('interactive_timeout') < $timeoutToSet) {
			$this->query("SET interactive_timeout = $timeoutToSet");
		}
	}

	public function tableEncoding($table) {
		$showCreateTable = $this->fetchRow('SHOW CREATE TABLE `'.$table.'`');
		if (preg_match("/CHARSET=([^\s]*).*/", $showCreateTable['Create Table'], $matched)) {
			return $matched[1];
		}
		$charSet = $this->mysqlVariableValue('character_set_database');
		if (empty($charSet)) return false;
		return $charSet;
	}


	public function	columnExistsInTable($column, $table) {
		if (isset($this->columnExistsInTableCache[$table][$column])) {
			return $this->columnExistsInTableCache[$table][$column];
		}
		$columns = $this->fetchArray('DESC  '.$table);
		foreach ($columns as $column_description) {
			if ($column_description['Field'] == $column) {
				$this->columnExistsInTableCache[$table][$column] = true;
				return true;
			}
		}
		$this->columnExistsInTableCache[$table][$column] = false;
		return false;
	}

	public function	columnType($column, $table) {
		$columns = $this->fetchArray('DESC  '.$table);
		foreach($columns as $column_description) {
			if($column_description['Field'] == $column) return $column_description['Type'];
		}
		return false;
	}

	public function recordExists($table, $conditions, $getQuery = false) {
		if (!is_array($conditions) || empty($conditions))
			trigger_error(sprintf("%s: Second parameter has to be an array may not be empty!", __FUNCTION__), E_USER_WARNING);
		$fields = array();
		$values = array();
		foreach ($conditions as $f => $v) {
			$values[] = '`'.$f."` = '".$this->escape($v)."'";
		}
		$q = 'SELECT * FROM `'.$table.'` WHERE '.implode(' AND ', $values);
		if ($getQuery) {
			return $q;	
		}
		$result = $this->query($q);

		if ($result && ($this->numRows($result) > 0)) {
			return true;
		}
		return false;
	}

	public function getProductById($pID, $languages_id = false, $addQuery = '') {
		$lIDs = $this->fetchArray('
			SELECT language_id FROM '.TABLE_PRODUCTS_DESCRIPTION.' WHERE products_id=\''.$pID.'\'
		', true);

		if ($languages_id === false) {
			$languages_id = $_SESSION['languages_id'];
		}
		
		if (!empty($lIDs) && !in_array($languages_id, $lIDs)) {
			$languages_id = array_shift($lIDs);
		}

		if (is_array($pID)) {
			$where = 'p.products_id IN (\''.implode('\', \'',  $pID).'\')';
		} else {
			$where = 'p.products_id = \''.(int) $pID.'\'';
		}

		$products = $this->fetchArray('
			SELECT *, date_format(p.products_date_available, \'%Y-%m-%d\') AS products_date_available 
			  FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd
			 WHERE '.$where.'
			   AND p.products_id = pd.products_id
			   AND pd.language_id = \''.$languages_id.'\'
			   '.$addQuery.'
		');

		if (!is_array($products) || empty($products)) return false;

		$finalProducts = array();
		foreach ($products as &$product) {
			if (SHOPSYSTEM == 'gambio') {
				$product['products_description'] = preg_replace('/\[TAB:[^\]]+\]/', '', $product['products_description']);
			}
			if ($product['products_image']) {
				$product['products_allimages'] = array($product['products_image']);
			} else {
				$product['products_allimages'] = array();
			}
			if ($this->tableExists(TABLE_PRODUCTS_IMAGES)) {
				$cols = $this->getTableCols(TABLE_PRODUCTS_IMAGES);
				$orderBy = (in_array('image_nr', $cols) 
					? 'image_nr' 
					: (in_array('sort_order', $cols) 
						? 'sort_order' 
						: ''
					)
				);
				if (!empty($orderBy)) {
					$orderBy = 'ORDER BY '.$orderBy;
				}
				$colname = (in_array('image', $cols) 
					? 'image' 
					: (in_array('image_name', $cols) 
						? 'image_name' 
						: ''
					)
				);
				if (!empty($colname)) {
					$product['products_allimages'] = array_merge(
						$product['products_allimages'],
						(array)$this->fetchArray('
							SELECT '.$colname.'
							  FROM '.TABLE_PRODUCTS_IMAGES.'
							 WHERE products_id = \''.$product['products_id'].'\'
						  '.$orderBy.'
						', true)
					);
				}
			}
			if (isset($product['products_head_keywords_tag'])) {
				$product['products_meta_keywords'] = $product['products_head_keywords_tag'];
				unset($product['products_head_keywords_tag']);
			}
			if (isset($product['products_head_desc_tag'])) {
				$product['products_meta_description'] = $product['products_head_desc_tag'];
				unset($product['products_head_desc_tag']);
			}
			if (isset($product['products_vpe'])
			    && isset($product['products_vpe_value'])
			    && $this->tableExists(TABLE_PRODUCTS_VPE)
			) {
				$product['products_vpe_name'] = stringToUTF8(MagnaDB::gi()->fetchOne('
				    SELECT products_vpe_name 
				      FROM '.TABLE_PRODUCTS_VPE.'
				     WHERE products_vpe_id = \''.$product['products_vpe'].'\'
				           AND language_id = \''.$languages_id.'\'
				  ORDER BY products_vpe_id, language_id 
				     LIMIT 1
				'));
			}
			$finalProducts[$product['products_id']] = $product;
		}
		if (!is_array($pID)) {
			return $products[0];
		}
		unset($products);
		return $finalProducts;
	}
	
	public function getCategoryPath($id, $for = 'category', &$cPath = array()) {
		if ($for == 'product') {
			$cIDs = $this->fetchArray('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id=\''.$this->escape($id).'\'
			', true);
			if (empty($cIDs)) {
				return array();
			}
			$return = array();
			foreach ($cIDs as $cID) {
				if ((int)$cID == 0) {
					$return[] = array('0');
				} else {
					$cPath = $this->getCategoryPath($cID);
					array_unshift($cPath, $cID);
					$return[] = $cPath;
				}
			}
			return $return;
		} else {
			$meh = $this->fetchOne(
				'SELECT parent_id FROM '.TABLE_CATEGORIES.' WHERE categories_id=\''.$this->escape($id).'\''
			);
			$cPath[] = (int)$meh;
			if ($meh != '0') {
				$this->getCategoryPath($meh, 'category', $cPath);
			}
			return $cPath;
		}
	}

	/* xt:Commerce Nachbildung */
	public function generateCategoryPath($id, $from = 'category', $categories_array = array(), $index = 0, $callCount = 0) {
		if ($from == 'product') {
			$categories_query = $this->query('
				SELECT categories_id FROM '.TABLE_PRODUCTS_TO_CATEGORIES.'
				 WHERE products_id = \''.$id.'\'
			');
			while ($categories = $this->fetchNext($categories_query)) {
				if ($categories['categories_id'] == '0') {
					$categories_array[$index][] = array ('id' => '0', 'text' => ML_LABEL_CATEGORY_TOP);
				} else {
					$category_query = $this->query('
						SELECT cd.categories_name, c.parent_id 
						  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd 
						 WHERE c.categories_id = \''.$categories['categories_id'].'\' 
						       AND c.categories_id = cd.categories_id 
						       AND cd.language_id = \''.$_SESSION['languages_id'].'\'
					');
					$category = $this->fetchNext($category_query);
					$categories_array[$index][] = array (
						'id' => $categories['categories_id'],
						'text' => $category['categories_name']
					);
					if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
						$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index);
					}
				}
				++$index;
			}
		} else if ($from == 'category') {
			$category_query = $this->query('
				SELECT cd.categories_name, c.parent_id 
				  FROM '.TABLE_CATEGORIES.' c, '.TABLE_CATEGORIES_DESCRIPTION.' cd
				 WHERE c.categories_id = \''.$id.'\' 
				       AND c.categories_id = cd.categories_id
				       AND cd.language_id = \''.$_SESSION['languages_id'].'\'
			');
			$category = $this->fetchNext($category_query);
			$categories_array[$index][] = array (
				'id' => $id,
				'text' => $category['categories_name']
			);
			if (($category['parent_id'] != '') && ($category['parent_id'] != '0')) {
				$categories_array = $this->generateCategoryPath($category['parent_id'], 'category', $categories_array, $index, $callCount + 1);
			}
			if ($callCount == 0) {
				$categories_array[$index] = array_reverse($categories_array[$index]);
			}
		}
	
		return $categories_array;
	}

	/**
	 * Insert an array of values
	 */
	public function insert($tableName, $data, $replace = false) {
		if (!is_array($data)) {
			$this->error = __METHOD__.' expects an array as 2nd argument.';
			return false;
		}

		$cols = '(';
		$values = '(';
		foreach ($data as $key => $value) {
			$cols .= "`" . $key . "`, ";

			if ($value === null) {
				$values .= 'NULL, ';
			} else if (is_int($value) || is_float($value) || is_double($value)) {
				$values .= $value . ", ";
			} else if (strtoupper($value) == 'NOW()') {
				$values .= "NOW(), ";
			} else {
				$values .= "'" . $this->escape($value) . "', ";
			}
		}
		$cols = rtrim($cols, ", ") . ")";
		$values = rtrim($values, ", ") . ")";
		#if (function_exists('print_m')) echo print_m(($replace ? 'REPLACE' : 'INSERT').' INTO `'.$tableName.'` '.$cols.' VALUES '.$values);
		return $this->query(($replace ? 'REPLACE' : 'INSERT').' INTO `'.$tableName.'` '.$cols.' VALUES '.$values);
	}

	/**
	 * Insert an array of values
	 */
	public function batchinsert($tableName, $data, $replace = false) {
		if (!is_array($data)) {
			$this->error = __METHOD__.' expects an array as 2nd argument.';
			return false;
		}
		$state = true;

		$cols = '(';
		foreach ($data[0] as $key => $val) {
			$cols .= "`" . $key . "`, ";
		}
		$cols = rtrim($cols, ", ") . ")";

		$block = array_chunk($data, 20);
		
		foreach ($block as $data) {
			$values = '';
			foreach ($data as $subset) {
				$values .= ' (';
				foreach ($subset as $value) {
					if ($value === null) {
						$values .= 'NULL, ';
					} else if (is_int($value) || is_float($value) || is_double($value)) {
						$values .= $value . ", ";
					} else if (strtoupper($value) == 'NOW()') {
						$values .= "NOW(), ";
					} else {
						$values .= "'" . $this->escape($value) . "', ";
					}
				}
				$values = rtrim($values, ", ") . "),\n";
			}
			$values = rtrim($values, ",\n");
	
			//echo ($replace ? 'REPLACE' : 'INSERT').' INTO `'.$tableName.'` '.$cols.' VALUES '.$values;
			$state = $state && $this->query(($replace ? 'REPLACE' : 'INSERT').' INTO `'.$tableName.'` '.$cols.' VALUES '.$values);
		}
		return $state;
	}

	/**
	 * Get last auto-increment value
	 */
	public function getLastInsertID() {
		return mysql_insert_id($this->resourcelink);
	}

	/**
	 * Update row(s)
	 */
	public function update($tableName, $data, $wherea = array(), $add = '', $verbose = false) {
		if (!is_array($data) || !is_array($wherea)) {
			$this->error = __METHOD__.' expects two arrays as 2nd and 3rd arguments.';
			return false;
		}

		$values = "";
		$where = "";

		foreach ($data as $key => $value) {
			$values .= "`" . $key . "` = ";

			if ($value === null) {
				$values .= 'NULL, ';
			} else if (is_int($value) || is_float($value) || is_double($value)) {
				$values .= $value . ", ";
			} else if (strtoupper($value) == 'NOW()') {
				$values .= "NOW(), ";
			} else {
				$values .= "'" . $this->escape($value) . "', ";
			}
		}
		$values = rtrim($values, ", ");

		if (!empty($wherea)) {
			foreach ($wherea as $key => $value) {
				$where .= "`" . $key . "` = ";
	
				if ($value === null) {
					$values .= 'NULL AND ';
				} else if (is_int($value) || is_float($value) || is_double($value)) {
					$where .= $value . " AND ";
				} else if (strtoupper($value) == 'NOW()') {
					$where .= "NOW() AND ";
				} else {
					$where .= "'" . $this->escape($value) . "' AND ";
				}
			}
			$where = rtrim($where, "AND ");
		} else {
			$where = '1=1';
		}
		return $this->query('UPDATE `'.$tableName.'` SET '.$values.' WHERE '.$where.' '.$add, $verbose);
	}

	/**
	 * Delete row(s)
	 */
	public function delete($table, $wherea, $add = null) {
		if (!is_array($wherea)) {
			$this->error = __METHOD__.' expects an array as 2nd argument.';
			return false;
		}

		$where = "";

		foreach ($wherea as $key => $value) {
			$where .= "`" . $key . "` = ";

			if ($value === null) {
				$values .= 'NULL AND ';
			} else if (is_int($value) || is_float($value) || is_double($value)) {
				$where .= $value . " AND ";
			} else {
				$where .= "'" . $this->escape($value) . "' AND ";
			}
		}

		$where = rtrim($where, "AND ");

		$query = "DELETE FROM `".$table."` WHERE ".$where." ".$add;

		return $this->query($query);
	}

	public function freeResult($result = null) {
		if ($result !== null) {
			mysql_free_result($result);
		} else {
			mysql_free_result($this->result);
		}
		return true;
	}

	/**
	 * Unescapes strings / arrays of strings
	 */
	public function unescape($object) {
		return is_array($object) ?
			array_map(array('MySQL', 'unescape'), $object) :
				stripslashes($object);
	}
	
	public function getTableCols($table) {
		$cols = array();
		if (!$this->tableExists($table)) {
			return $cols;
		}
		$colsQuery = $this->query('SHOW COLUMNS FROM `'.$table.'`');
		while ($row = $this->fetchNext($colsQuery))	{
			$cols[] = $row['Field'];
		}
		$this->freeResult($colsQuery);
		return $cols;
	}

	/**
	 * Get last executed query
	 */
	public function getLastQuery() {
		return $this->query;
	}

	/**
	 * Get last error
	 */
	public function getLastError() {
		return $this->error;
	}

	/**
	 * Get time consumed for all queries / operations (milliseconds)
	 */
	public function getQueryTime() {
		return round((microtime(true) - $this->start) * 1000, 2);
	}

	public function getTimePerQuery() {
		return $this->timePerQuery;
	}

	/**
	 * Get number of queries executed
	 */
	public function getQueryCount() {
		return $this->count;
	}
	
	public function getRealQueryTime() {
		return $this->querytime;
	}
	
	public function setShowDebugOutput($b) {
		$this->showDebugOutput = $b;
	}

}
