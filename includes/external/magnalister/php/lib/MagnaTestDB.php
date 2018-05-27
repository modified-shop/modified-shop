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

class MagnaTestDB extends MagnaDB {
	protected static $instance = null;
	private $plain = false;
	
	protected function __construct() {
		$this->plain = function_exists('_is_plain_text') && _is_plain_text();
	}
	public static function gi($link = 'db_link') {
		if (self::$instance == NULL) {
			self::$instance = new self($link);
		}
		return self::$instance;
	}

	protected function isConnected() {
		return true;
	}

	public function escape($object) {
		if (is_array($object)) {
			$object = array_map(array($this, 'escape'), $object);
		} else if (is_string($object)) {
			$tObject = $this->escapeStrings ? stripslashes($object) : $object;
			$object = MagnaDBDriver::fallbackEscape($tObject);
		}
		return $object;
	}

	/**
	 * dummy, to prevent PHP errors
	 */
	public function affectedRows() {
		return false;
	}

	/**
	 * Send a query
	 */
	public function query($query, $verbose = false) {
		/* Hook "MagnaDBQuery" DO NOT CHANGE FORMAT FOR THIS HOOK */
		if (function_exists('magnaContribVerify') && (($hp = magnaContribVerify('MagnaDBQuery', 1)) !== false)) {
			require($hp);
		}

		$this->query = $query;
		if ($this->plain) {
			echo $this->query."\n";
		} else {
			echo '<pre>'.$this->query."</pre>\n";
		}
		//file_put_contents(dirname(__FILE__).'/db_guery.log', "### ".$this->count."\n".$this->query."\n\n", FILE_APPEND);
		++$this->count;
		return false;
	}

	/**
	 * Get last auto-increment value
	 */
	public function getLastInsertID() {
		return 1;
	}
	
	
	/**
	 * Insert an array of values
	 */
	public function insert($tableName, $data, $replace = false) {
		echo print_m(array (
			'$tableName' => $tableName,
			'$data' => $data,
			'$replace' => trim(var_dump_pre($replace, true)),
		), __METHOD__);
		parent::insert($tableName, $data, $replace);
	}
	
	/**
	 * Insert an array of values
	 */
	public function batchinsert($tableName, $data, $replace = false) {
		echo print_m(array (
			'$tableName' => $tableName,
			'$data' => $data,
			'$replace' => trim(var_dump_pre($replace, true)),
		), __METHOD__);
		parent::batchinsert($tableName, $data, $replace);
	}
	
	/**
	 * Update row(s)
	 */
	public function update($tableName, $data, $wherea = array(), $add = '', $verbose = false) {
		echo print_m(array (
			'$tableName' => $tableName,
			'$data' => $data,
			'$wherea' => $wherea,
			'$add' => $add,
			'$verbose' => trim(var_dump_pre($verbose, true)),
		), __METHOD__);
		parent::update($tableName, $data, $wherea, $add, $verbose);
	}
	
	/**
	 * Delete row(s)
	 */
	public function delete($table, $wherea, $add = null) {
		echo print_m(array (
			'$table' => $table,
			'$wherea' => $wherea,
			'$add' => $add,
		), __METHOD__);
		parent::delete($table, $wherea, $add);
	}
}
