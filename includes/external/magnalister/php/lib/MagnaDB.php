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


define('MAGNADB_ENABLE_LOGGING', MAGNA_DEBUG && false);

abstract class MagnaDBDriver {
	protected $charset = '';
	
	abstract public function __construct($access);
	
	abstract public function isConnected();
	abstract public function connect();
	abstract public function close();
	abstract public function getLastErrorMessage();
	abstract public function getLastErrorNumber();
	abstract public function getServerInfo();
	abstract public function setCharset($charset);
	abstract public function query($query);
	abstract public function escape($str);
	abstract public function affectedRows();
	abstract public function getInsertId();
	abstract public function isResult($m);
	abstract public function numRows($result);
	abstract public function fetchArray($result);
	abstract public function freeResult($result);
	
	public function getDriverDetails() {
		$access = $this->access;
		unset($access['user']);
		unset($access['pass']);
		return $access;
	}
	
	/**
	 * mimics mysql_real_escape_string
	 */
	public static function fallbackEscape($str) {
		return str_replace(
			array('\\',   "\0",  "\n",  "\r",  "'",   '"',   "\x1a"),
			array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ),
			$str
		);
	}
}

class MagnaDBDriverMysqli extends MagnaDBDriver {
    /** @var mysqli */
	protected $oInstance = null;
	
	protected $access = array(
		'type' => '', // [pipe|socket|tcpip]
		'host' => '',
		'user' => '',
		'pass' => '',
		'port' => '', // will only be explicitly set for tcpip connections
		'sock' => '', // will only be explicitly set for non tcpip connections, includes windows pipes
		'persistent' => false,
	);
	
	public function __construct($access) {
		$this->access = array_merge($this->access, $access);
		$this->detectConnectionType();

        // Since PHP 8.1+ database errors throw exceptions - this will do everything like before
        mysqli_report(MYSQLI_REPORT_OFF);
	}
	
	protected function detectConnectionType() {
		if (strpos($this->access['host'], '\\') !== false) {
			$this->access['type'] = 'pipe'; // Windows named pipe based connection. e.g. \\.\pipe\MySQL
			$this->access['sock'] = $this->access['host'];
			$this->access['host'] = '.';
		} else if (strpos($this->access['host'], '.sock') !== false) {
			// modified >= 2.0.5.0:
			$blMsock3 = false;
			if (is_file(DIR_FS_INC.'get_database_version.inc.php')) {
				require_once(DIR_FS_INC.'get_database_version.inc.php');
				$modified_shop_version = get_database_version();
				if ($modified_shop_version['plain'] >= '2.0.5.0') {
					$blMsock3 = true;
				}
			}
			$this->access['type'] = 'socket'; // Unix domain sockets use the file system as their address name space.
			$msock = array();
			if (preg_match('/^([^\:]+)\:(.*)$/', $this->access['host'], $msock)) {
				$this->access['host'] = $msock[1];
				$this->access['sock'] = $msock[2];
				if ($blMsock3) {
					$this->access['sock'] = $msock[3];
				}
			} else {
				$this->access['sock'] = $this->access['host'];
				$this->access['host'] = '';
			}
		} else {
			$this->access['type'] = 'tcpip';
			$mport = array();
			if (preg_match('/^[^\:]+\:([0-9]+)$/', $this->access['host'], $mport)) {
				$this->access['port'] = (int)$mport[1];
				$this->access['host'] = str_replace(':'.$this->access['port'], '', $this->access['host']);
			} else {
				$this->access['port'] = (int)ini_get('mysqli.default_port');
			}
			if (empty($this->access['port'])) {
				$this->access['port'] = 3306;
			}
		}
		// for non tcpip connections
		if (empty($this->access['port'])) {
			$this->access['port'] = (int)ini_get('mysqli.default_port');
		}
	}
	
	public function isConnected() {
		try {
			// there seems to be no other way than to surpress the error message
			// in order to detect that the connection has been closed,
			// which is a shame.
			//*/
			return is_object($this->oInstance) && @$this->oInstance->ping();
			
			/*/
			ob_start();
			$status = is_object($this->oInstance) && $this->oInstance->ping();
			$err = ob_get_clean();
			echo $err;
			
			if (!empty($err)) {
				var_dump($status);
				echo print_m(MagnaDB::gi()->stripObjectsAndResources(debug_backtrace(true)));
				die();
			}
			
			return $status;
			//*/
		} catch (Exception $e) {
			var_dump($e);
			return false;
		}
	}
	
	public function connect() {
		ob_start();
		switch ($this->access['type']) {
			case 'socket':
			case 'pipe': {
				$this->oInstance = new mysqli(
					($this->access['persistent'] ? 'p:' : '').$this->access['host'],
					$this->access['user'], $this->access['pass'], '', (int)$this->access['port'],
					$this->access['sock']
				);
				break;
			}
			case 'tcpip': 
			default: {
				$this->oInstance = new mysqli(
					($this->access['persistent'] ? 'p:' : '').$this->access['host'],
					$this->access['user'], $this->access['pass'], '', (int)$this->access['port']
				);
				break;
			}
		}
		$warn = ob_get_clean();
		
		if (!$this->isConnected()) {
			if (($this->access['type'] == 'tcpip') && ($this->access['host'] == 'localhost')) {
				// Fix for broken estugo php config.
				//
				// From: http://stackoverflow.com/questions/13870362/php-mysql-test-database-server
				//
				// This seems to be a common issue, as googling for it yields quite a few results.
				// I experienced this on my two linux boxes as well (never under Windows though) and
				// at some point I resolved to just use 127.0.0.1 on all dev servers. Basically,
				// localhost makes the connection to the MySQL server use a socket, but your
				// configuration doesn't point to the socket file correctly.
				
				$this->access['host'] = '127.0.0.1';
				return $this->connect();
			}
			echo $warn;
			return;
		}
		
		if (!empty($this->charset)) {
			$this->setCharset($this->charset);
		}
	}
	
	public function close() {
		$success = false;
		if (is_object($this->oInstance) && is_callable(array($this->oInstance, 'close'))) {
			$success = $this->oInstance->close();
		}
		$this->oInstance = null;
		return $success;
	}
	
	public function getLastErrorMessage() {
		if (is_object($this->oInstance) && isset($this->oInstance->error)) {
			return $this->oInstance->error;
		}
		return '';
	}
	
	public function getLastErrorNumber() {
		if (is_object($this->oInstance) && isset($this->oInstance->errno)) {
			return $this->oInstance->errno;
		}
		return 0;
	}
	
	public function getServerInfo() {
		if ($this->isConnected()) {
			return $this->oInstance->server_info;
		}
		return false;
	}
	
	public function setCharset($charset) {
		$this->charset = $charset;
		if ($this->isConnected()) {
			$this->oInstance->set_charset($this->charset);
		}
	}
	
	public function query($query) {
		if ($this->isConnected()) {
			return $this->oInstance->query($query);
		}
		return false;
	}
	
	public function escape($str) {
		if ($this->isConnected()) {
			return $this->oInstance->real_escape_string($str);
		}
		return self::fallbackEscape($str);
	}
	
	public function affectedRows() {
		if (is_object($this->oInstance) && isset($this->oInstance->affected_rows)) {
			return $this->oInstance->affected_rows;
		}
		// re-establishing a connection doesn't make sense here as the new connection
		// can't return the affected row count of the old connection.
		return false;
	}
	
	public function getInsertId() {
		if ($this->isConnected()) {
			return $this->oInstance->insert_id;
		}
		// same reason as in $this->affectedRows();
		return false;
	}
	
	public function isResult($m) {
		return $m instanceof mysqli_result;
	}
	
	public function numRows($result) {
		return $result->num_rows;
	}
	
	public function fetchArray($result) {
		return $result->fetch_array(MYSQLI_ASSOC);
	}
	
	public function freeResult($result) {
		if (!is_object($result)) {
			return;
		}
		return $result->free_result();
	}
}

class MagnaDBDriverMysql extends MagnaDBDriver {
	protected $rLink = null;
	protected $resourceValid = false;
	protected $resourceIsShared = false;
	
	protected $access = array(
		'host' => '',
		'user' => '',
		'pass' => '',
		'persistent' => false,
	);
	
	public function __construct($access) {
		$this->access = array_merge($this->access, $access);
	}
	
	public function isConnected() {
		return is_resource($this->rLink) && mysql_ping($this->rLink);
	}

	public function connect($force = false) {
		global $db_link;

		$this->rLink = $this->access['persistent']
			? mysql_pconnect($this->access['host'], $this->access['user'], $this->access['pass'])
			: mysql_connect($this->access['host'], $this->access['user'], $this->access['pass']);

		$this->resourceValid = is_resource($this->rLink);

		// Passiert nur beim reconnect.
		if ($this->resourceIsShared && ($db_link !== $this->rLink)) {
			$db_link = $this->rLink;
		}

		// Passiert nur beim initial connect.
		if ($db_link === $this->rLink) {
			$this->resourceIsShared = true;
		}

		if (!empty($this->charset)) {
			$this->setCharset($this->charset);
		}
	}

	public function close() {
		$success = false;
		if (is_resource($this->rLink)) {
			$success = mysql_close($this->rLink);
		}

		$this->resourceValid = false;

		return $success;
	}
	
	public function getLastErrorMessage() {
		if (is_resource($this->rLink)) {
			return mysql_error($this->rLink);
		}
		return '';
	}
	
	public function getLastErrorNumber() {
		if (is_resource($this->rLink)) {
			return mysql_errno($this->rLink);
		}
		return 0;
	}
	
	public function getServerInfo() {
		if ($this->isConnected()) {
			return mysql_get_server_info($this->rLink);
		}
		return false;
	}
	
	public function setCharset($charset) {
		$this->charset = $charset;
		if ($this->isConnected()) {
			if (function_exists('mysql_set_charset')) {
				mysql_set_charset($this->charset, $this->rLink);
			} else {
				$this->query('SET NAMES '.$this->charset);
			}
		}
		return false;
	}
	
	public function query($query) {
		if ($this->isConnected()) {
			return mysql_query($query, $this->rLink);
		}
		return false;
	}
	
	public function escape($str) {
		if ($this->isConnected()) {
			return mysql_real_escape_string($str, $this->rLink);
		}
		return self::fallbackEscape($str);
	}
	
	public function affectedRows() {
		if ($this->isConnected()) {
			mysql_affected_rows($this->rLink);
		}
		// re-establishing a connection doesn't make sense here as the new connection
		// can't return the affected row count of the old connection.
		return false;
	}
	
	public function getInsertId() {
		if ($this->isConnected()) {
			return mysql_insert_id($this->rLink);
		}
		// same reason as in $this->affectedRows();
		return false;
	}
	
	public function isResult($m) {
		return is_resource($m);
	}
	
	public function numRows($result) {
		return mysql_num_rows($result);
	}
	
	public function fetchArray($result) {
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}
	
	public function freeResult($result) {
		return mysql_free_result($result);
	}
}

class MagnaDB {
	protected static $instance = null;
	protected $destructed = false;
	
	protected $driver = null; // instanceof mysqli or mysql driver
	
	protected $access = array (
		'host' => '',
		'user' => '',
		'pass' => '',
		'persistent' => false,
	);
	protected $database = '';
	
	protected $query = '';
	protected $error = '';
	protected $result = null;
	protected $inTransaction = false;
	
	protected $sqlErrors = array();
	
	protected $start = 0;
	protected $count = 0;
	protected $querytime = 0;
	protected $doLogQueryTimes = true;
	protected $timePerQuery = array();

	protected $availabeTables = array();

	protected $escapeStrings = false;

	protected $sessionLifetime;
	
	protected $showDebugOutput = MAGNA_DEBUG;
	
	/* Caches */
	protected $tableColumnsCache = array();
	protected $columnExistsInTableCache = array();
	protected $DescCache = array();

	/**
	 * Class constructor
	 */
	protected function __construct() {
		$this->start         = microtime(true);
		$this->count         = 0;
		$this->querytime     = 0;
		$this->setEscapeStrings();
		
		$this->access['host'] = DB_SERVER;
		$this->access['user'] = DB_SERVER_USERNAME;
		$this->access['pass'] = DB_SERVER_PASSWORD;
		$this->access['persistent'] = (defined('USE_PCONNECT') && (strtolower(USE_PCONNECT) == 'true'));
		
		$driverClass = $this->selectDriver();
		$this->driver = new $driverClass($this->access);
		
		$this->database = DB_DATABASE;
		
		$this->timePerQuery[] = array (
			'query' => 'Driver: "'.get_class($this->driver).'" ('.$this->getDriverDetails().')',
			'time' => 0
		);
		
		$this->selfConnect(false, true);
		
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
		
		$this->reloadTables();
		
		$this->initSession();
	}
	
	/**
	 * disables / enables stripslashes in escape()
	 * @param bool $bl
	 * @param null $bl default value
	 * @return \MagnaDB
	 */
	public function setEscapeStrings ($bl = null) {
		// magic quotes are deprecated as of php 5.4
		if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
			$this->escapeStrings = $bl === null ? false : $bl;
		} else {
			$this->escapeStrings = $bl === null ? get_magic_quotes_gpc() : $bl;
		}
		return $this;
	}
	
	protected function selectDriver() {
		// we prefer mysqli only for php 5.3 or greater as this version introduces persistent connections
		$driver = (function_exists('mysqli_query') && defined('PHP_VERSION_ID') && (PHP_VERSION_ID >= 50300))
			? 'MagnaDBDriverMysqli'
			: 'MagnaDBDriverMysql';
		
		// "Modified" specific hack, will be refactored soon!
		if (defined('DB_MYSQL_TYPE') && (DB_MYSQL_TYPE === 'mysql')) {
			$driver = 'MagnaDBDriverMysql';
		}
		return $driver;
	}
	
	protected function getDriverDetails() {
		$data = $this->driver->getDriverDetails();
		$info = '';
		foreach ($data as $key => $value) {
			$info .= '"'.$key.'": "'.$value.'",   ';
		}
		$info = rtrim($info, ', ').'';
		return $info;
	}
	
	/**
	 * @return MagnaDB Singleton - gets Instance
	 */
	public static function gi() {
		if (self::$instance == NULL) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __clone() {}
	
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
	
	public function selectDatabase($db) {
		$this->query('USE `'.$db.'`');
	}
	
	protected function isConnected() {
		return $this->driver->isConnected();
	}
	
	protected function selfConnect($forceReconnect = false, $initialConnect = false) {
		# Wenn keine Verbindung im klassischen Sinne besteht, selbst eine herstellen.
		if ($this->driver->isConnected() && !$forceReconnect) {
			return false;
		}
		
		// Try to connect...
		$error = '';
		$errno = 0;
		
		$attempts = 0;
		$maxAttempts = $initialConnect ? 2 : 100;
		do {
			$this->driver->connect();
			if (!$this->isConnected()) {
				$errno = $this->driver->getLastErrorNumber();
				$error = $this->driver->getLastErrorMessage();
			} else {
				break;
			}
			$this->closeConnection(true);
			
			usleep(100000); // 100ms
			# Retry if '2006 MySQL server has gone away'
		} while (++$attempts < $maxAttempts);
		
		if (!$initialConnect
			&& isset($_GET['MLDEBUG']) && ($_GET['MLDEBUG'] === 'true')
			&& isset($_GET['LEVEL'])   && (strtolower($_GET['LEVEL']) == 'high')
		) {
			echo "\n<<<< MagnaDB :: reconnect >>>>\n";
		}
		
		if (!$this->isConnected()) {
			// called in the destructor: Just leave. No need to close connection, it's lost
			if ($this->destructed) {
				exit();
			}
			
			// die is bad behaviour. But meh..
			die(
				'<span style="color:#000000;font-weight:bold;">
					<small style="color:#ff0000;font-weight:bold;">[SQL Error]</small><br>
					Establishing a connection to the database failed.<br><br>
					<pre style="font-weight:normal">Giving up after '.$attempts.' attempts. Last error message received:'."\n".'('.$errno.') '.$error.'</pre>
					<pre style="font-weight:normal">'.htmlspecialchars(
						print_r($this->stripObjectsAndResources(array_slice(debug_backtrace(true), 4)), true)
					).'</pre>
				</span>'
			);
		}
		$vers = $this->driver->getServerInfo();
		if (substr($vers, 0, 1) > 4) {
			$this->query("SET SESSION sql_mode=''");
		}
		$this->selectDatabase($this->database);
		
		return true;
	}
	
	protected function closeConnection($force = false) {
		if (   $force
			|| ($this->isConnected() && !(defined('USE_PCONNECT') && (strtolower(USE_PCONNECT) == 'true')))
		) {
			if (is_object($this->driver)) {
				$this->driver->close();
			}
		}
	}
	
	protected function prepareError() {
		$errNo = $this->driver->getLastErrorNumber();
		if ($errNo == 0) {
			return '';
		}
		return $this->driver->getLastErrorMessage().' ('.$errNo.')';
	}

	public function logQueryTimes($b) {
		$this->doLogQueryTimes = $b;
	}

	public function stripObjectsAndResources($a, $lv = 0) {
		if (empty($a) || ($lv >= 10)) return $a;
		//echo print_m($a, trim(var_dump_pre($lv, true)));
		$aa = array();
		foreach ($a as $k => $value) {
			$toString = '';
			// echo var_dump_pre($value, 'value');
			if (!is_object($value) && !is_array($value)) {
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
				if (defined('DIR_FS_DOCUMENT_ROOT')) {
					$value = str_replace(dirname(DIR_FS_DOCUMENT_ROOT), '', $value);
				}
			}
			if ($k == 'args') {
				if (is_string($value) && (strlen($value) > 5000)) {
					$value = substr($value, 0, 5000).'[...]';
				}
			}
			if (($value === $this->access['pass']) && ($this->access['pass'] != null)) {
				$aa = '*****';
				break;
			}
			$aa[$k] = $value;
		}
		return $aa;
	}

	protected function fatalError($query, $errno, $error, $fatal = false) {
		$backtrace = $this->stripObjectsAndResources(debug_backtrace(true));
		$this->sqlErrors[] = array (
			'Query' => rtrim(trim($query, "\n")),
			'Error' => $error,
			'ErrNo' => $errno,
			'Backtrace' => $backtrace
		);
		
		if ($fatal) {
			die(
				'<span style="color:#000000;font-weight:bold;">
					' . $errno . ' - ' . $error . '<br /><br />
					<pre>' . $query . '</pre><br /><br />
					<pre style="font-weight:normal">'.htmlspecialchars(
						print_r($backtrace, true)
					).'</pre><br /><br />
					<small style="color:#ff0000;font-weight:bold;">[SQL Error]</small>
				</span>'
			);
		}
	}

	protected function execQuery($query) {
		$i = 8;
		
		$errno = 0;
		
		$this->selfConnect();
		
		do {
			$errno = 0;
			$result = $this->driver->query($query);
			if ($result === false) {
				$errno = $this->driver->getLastErrorNumber();
			}
			//if (defined('MAGNALISTER_PLUGIN')) echo 'mmysql_query errorno: '.var_export($errno, true)."\n";
			if (($errno === false) || ($errno == 2006)) {
				$this->closeConnection(true);
				usleep(100000); // 100ms
				$this->selfConnect(true);
			}
			# Retry if '2006 MySQL server has gone away'
		} while (($errno == 2006) && (--$i >= 0));
		
		if ($errno != 0) {
			$this->fatalError($query, $errno, $this->driver->getLastErrorMessage());
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
	

	/**
	 * Set the isolation level for the next transaction. So call *BEFORE* beginTransation()!
	 * @param $level: Can be either READ UNCOMMITTED, READ COMMITTED, REPEATABLE READ or SERIALIZABLE
	 */
	public function setTransactionLevel($level, $commit = false) {
		try {
			$this->query('SET TRANSACTION ISOLATION LEVEL '.$level);
		} catch (DatabaseException $dbe) {
			if ($dbe->getMySqlErrNo() == 1568) {
				if ($commit) {
					$this->commit();
				} else {
					$this->rollback();
				}
			} else {
				throw $dbe;
			}
		}
	}
	
	public function getTransactionLevel() {
		return $this->fetchOne('SELECT @@session.tx_isolation');
	}
	
	/**
	 * Begins a transaction. The parameter sets the isolation level. If omitted it won't be set for this
	 * transaction. The global isolation level of mysql will be used.
	 */
	public function beginTransaction($level = false, $commit = false) {
		if ($level !== false) {
			$this->setTransactionLevel($level, $commit);
		}
		$this->query('BEGIN');
		$this->inTransaction = true;
	}
	
	public function commit() {
		$this->query('COMMIT');
		$this->inTransaction = false;
	}
	
	public function rollback() {
		$this->query('ROLLBACK');
		$this->inTransaction = false;
	}
	
	public function isInTransaction() {
		return $this->inTransaction;
	}
	
	
	public function setCharset($charset) {
		$this->driver->setCharset($charset);
	}
	
	protected function sessionGarbageCollector() {
		if ($this->tableExists(TABLE_MAGNA_SESSION)) {
			$this->query("DELETE FROM ".TABLE_MAGNA_SESSION." WHERE expire < '".(time() - $this->sessionLifetime)."' AND session_id <> '0'");
		}
		if (defined('MAGNALISTER_PLUGIN') && MAGNALISTER_PLUGIN && $this->tableExists(TABLE_MAGNA_SELECTION)) {
			$this->query("DELETE FROM ".TABLE_MAGNA_SELECTION." WHERE expires < '".gmdate('Y-m-d H:i:d', (time() - $this->sessionLifetime))."'");
		}
	}

	protected function sessionRead() {
		$result = $this->fetchOne('
			SELECT data FROM '.TABLE_MAGNA_SESSION.'
			 WHERE session_id = "'.session_id().'"
			       AND expire > "'.time().'"
		', true);
		if (!empty($result)) {
			return @unserialize($result);
		}
		return array();
	}

	protected function shopSessionRead() {
		/* This "Session" is for all Backend users and it _never_ expires! */
		$result = $this->fetchOne('
			SELECT data FROM '.TABLE_MAGNA_SESSION.'
			 WHERE session_id = "0"
		', true);

		if (!empty($result)) {
			return @unserialize($result);
		}
		return array();
	}
	
	protected function initSession() {
		global $_MagnaSession, $_MagnaShopSession;
		
		if (!defined('TABLE_MAGNA_SESSION')) define('TABLE_MAGNA_SESSION', 'magnalister_session');
		if ($this->tableExists(TABLE_MAGNA_SESSION)) {
			$this->sessionLifetime = (int)ini_get("session.gc_maxlifetime");
			$this->sessionGarbageCollector();

			$_MagnaSession = $this->sessionRead();
			$_MagnaShopSession = $this->shopSessionRead();
		}
	}
	
	protected function sessionStore($data, $sessionID) {
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
			));
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
			$this->update(TABLE_MAGNA_SELECTION, array(
				'expires' => gmdate('Y-m-d H:i:d', (time() + $this->sessionLifetime))
			), array(
				'session_id' => session_id()
			));
		}
	}
	
	public function escape($object) {
		if (is_array($object)) {
			$object = array_map(array($this, 'escape'), $object);
		} else if (is_string($object)) {
			$tObject = $this->escapeStrings ? stripslashes($object) : $object;
			if ($this->isConnected()) {
				$object =  $this->driver->escape($tObject);
			} else {
				$object = MagnaDBDriver::fallbackEscape($tObject);
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
		
		return $this->driver->numRows($result);
	}
	
	/**
	 * Get number of changed/affected rows
	 */
	public function affectedRows() {
		return $this->driver->affectedRows();
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
			$row = $this->driver->fetchArray($result);
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
		if ($this->driver->isResult($query)) {
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

	protected function reloadTables() {
		$this->availabeTables = $this->fetchArray('SHOW TABLES', true);
	}

	public function tableExists($table, $purge = false) {
		if ($purge) {
			$this->reloadTables();
		}
		/* {Hook} "MagnaDB_TableExists": Enables you to modify the $table variable before the check for existance is performed in
		   case your shop uses a contrib, that messes with the table prefixes.
		 */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaDB_TableExists', 1)) !== false)) {
			require($hp);
		}
		return in_array($table, $this->availabeTables);
	}

	public function getAvailableTables($pattern = '', $purge = false) {
		if ($purge) {
			$this->reloadTables();
		}
		if (empty($pattern)) {
			return $this->availabeTables;
		}
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
		if ($showVariablesLikeVariable) {
			return $showVariablesLikeVariable['Value'];
		}
		# nicht false zurueckgeben, denn dies koennte ein gueltiger Variablenwert sein
		return null;
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


	public function	getTableColumns($table) {
		if (isset($this->tableColumnsCache[$table])) {
			return $this->tableColumnsCache[$table];
		}
		$columns = $this->getDesc($table);
		if (!is_array($columns) || empty($columns)) {
			return false;
		}
		$this->tableColumnsCache[$table] = array();
		foreach ($columns as $column_description) {
			$this->tableColumnsCache[$table][] = $column_description['Field'];
		}
		return $this->tableColumnsCache[$table];
	}

	public function	columnExistsInTable($column, $table) {
		if (isset($this->columnExistsInTableCache[$table][$column])) {
			return $this->columnExistsInTableCache[$table][$column];
		}
		$columns = $this->getDesc($table);
		if (!is_array($columns) || empty($columns)) {
			return false;
		}
		foreach ($columns as $column_description) {
			if ($column_description['Field'] == $column) {
				$this->columnExistsInTableCache[$table][$column] = true;
				return true;
			}
		}
		$this->columnExistsInTableCache[$table][$column] = false;
		return false;
	}

	public function isSingleColumnNotPrimaryKey($ckey, $table) {
		$keys = $this->fetchArray('SHOW KEYS FROM '.$table.' WHERE Key_name = Column_name');
		foreach ($keys as $k) {
			if ($k['Column_name'] == $ckey) {
				return true;
			}
		}
		return false;
	}

	public function	columnType($column, $table) {
		$columns = $this->getDesc($table);
		foreach($columns as $column_description) {
			if($column_description['Field'] == $column) return $column_description['Type'];
		}
		return false;
	}

	public function columnNeedsValue($column, $table) {
		$columns = $this->getDesc($table);
		foreach($columns as $column_description) {
			if(    ($column_description['Field']   == $column)
			    && ($column_description['Null']    == 'NO')
			    && ($column_description['Default'] === null)) {
				return true;
			}
		}
		return false;
	}

	private function getDesc($table) {
		if (!isset($this->DescCache[$table])) {
			$this->DescCache[$table] = $this->fetchArray('DESC  '.$table);
		}
		return $this->DescCache[$table];
	}

    /**
     * Will cut off if data is too long for a specific data field
     *  Currently only checks for varchar
     *  @TODO: int,...
     *
     * @param $data
     * @param $table
     * @return void
     */
    public function validateDataLength(&$data, $table) {
        $tableDescription = $this->getDesc($table);
        foreach ($tableDescription as $columnDescription) {
            if (array_key_exists($columnDescription['Field'], $data)) {
                // check if its varchar and cut off at max length
                $pattern = '/varchar\((\d+)\)/';
                if (preg_match($pattern, $columnDescription['Type'], $matches)) {
                    $maxLength = (int)$matches[1];
                    if (mb_strlen($data[$columnDescription['Field']]) > $maxLength) {
                        $data[$columnDescription['Field']] = mb_substr($data[$columnDescription['Field']], 0, $maxLength);
                    }
                }
            }
        }
    }

	/**
	 * @param array $array
	 * @param string $table
	 *
	 *  If entries for non-nullable columns without default value are lacking, add
	 */
	public function addNonNullableEntries(&$data, $table) {
		if (!is_array($data)) return;
		$tabledesc = $this->getDesc($table);
		if (!is_array($tabledesc)) return false;
		/* {Hook} "MagnaDB_addNonNullableEntries": Enables you to add own functionality to this method, or replace the method's functionality.<br>

		   Variables that can be used: <ul><li>$data: The table data</li>
		   <li>$table: The table name (read only)</li></ul>
		   <li>$tabledesc: The desctription of the table (read only)</li></ul>
		 */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaDB_addNonNullableEntries', 1)) !== false)) {
			require($hp);
		}
		// go through all table's columns
		foreach ($tabledesc as $column_description) {
			if (!array_key_exists($column_description['Field'], $data)) {
				if(    ($column_description['Null']    == 'NO')
				    && ($column_description['Default'] === null)) {
					if ($column_description['Type'] == 'date') {
						$data[$column_description['Field']] = date('Y-m-d');
					} else if ($column_description['Type'] == 'time') {
						$data[$column_description['Field']] = date('H:i:s');
					} else if ($column_description['Type'] == 'datetime') {
						$data[$column_description['Field']] = date('Y-m-d H:i:s');
					} else if (    (strpos($column_description['Type'], 'int') !== false)
					            || (strpos($column_description['Type'], 'dec') !== false)
					            || (strpos($column_description['Type'], 'float') !== false)
					            || (strpos($column_description['Type'], 'double') !== false)
					            || (strpos($column_description['Type'], 'numeric') !== false)
					            || (strpos($column_description['Type'], 'fixed') !== false)
					            || (strpos($column_description['Type'], 'number') !== false)
					            || ($column_description['Type'] == 'bit')
					            || ($column_description['Type'] == 'boolean')) {
						$data[$column_description['Field']] = 0;
					} else {
						$data[$column_description['Field']] = '';
					}
				}
			}
		}
	}

	public function recordExists($table, $conditions, $getQuery = false) {
		if (!is_array($conditions) || empty($conditions)) {
			trigger_error(sprintf("%s: Second parameter has to be an array may not be empty!", __FUNCTION__), E_USER_WARNING);
		}
		$fields = array();
		$values = array();
		foreach ($conditions as $f => $v) {
			$values[] = '`'.$f."` = '".$this->escape($v)."'";
		}
		if ($getQuery) {
			$q = 'SELECT * FROM `'.$table.'` WHERE '.implode(' AND ', $values);
			return $q;	
		}else{
			$q = 'SELECT 1 FROM `'.$table.'` WHERE '.implode(' AND ', $values).' LIMIT 1';
		}
		$result = $this->fetchOne($q);
		if ($result !== false) {
			return true;
		}
		return false;
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
		return $this->driver->getInsertId();
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
				$where .= "`" . $key . "` ";
	
				if ($value === null) {
					$where .= 'IS NULL AND ';
				} else if (is_int($value) || is_float($value) || is_double($value)) {
					$where .= '= '.$value . " AND ";
				} else if (strtoupper($value) == 'NOW()') {
					$where .= "= NOW() AND ";
				} else {
					$where .= "= '" . $this->escape($value) . "' AND ";
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
			$where .= "`" . $key . "` ";

			if ($value === null) {
				$where .= 'IS NULL AND ';
			} else if (is_int($value) || is_float($value) || is_double($value)) {
				$where .= '= '.$value . " AND ";
			} else {
				$where .= "= '" . $this->escape($value) . "' AND ";
			}
		}

		$where = rtrim($where, "AND ");

		$query = "DELETE FROM `".$table."` WHERE ".$where." ".$add;
		return $this->query($query);
	}

	public function freeResult($result = null) {
		if ($result === null) {
			$result = $this->result;
		}
		$this->driver->freeResult($result);
		return true;
	}

	/**
	 * Unescapes strings / arrays of strings
	 */
	public function unescape($object) {
		return is_array($object)
			? array_map(array($this, 'unescape'), $object)
			: stripslashes($object);
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
	 * Gets all SQL errors.
	 */
	public function getSqlErrors() {
		return $this->sqlErrors;
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
