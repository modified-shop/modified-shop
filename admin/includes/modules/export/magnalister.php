<?php
/**
 * magnalister fuer xt:commerce v3 und gambio
 * Copyright (c) 2010 redgecko GmbH (http://www.redgecko.de/)
 *
 * Licensed under GNU/GPL v2
 *
 * Id: $Id: magnalister.php 357 2013-09-10 00:17:51Z derpapst $
 */
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_MAGNA_TEXT_TITLE', 'magnalister');
define('MODULE_MAGNA_TEXT_DESCRIPTION', '<div style="margin-left: 0.5em;">magnalister - das ultimative Listing-Tool f&uuml;r amazon, yatego, 
	g&uuml;nstiger.de, daparto und viele mehr.<br><br>Weitere Infos unter 
	<a href="http://www.magnalister.com" target="_blank" style="text-decoration:underline">www.magnalister.com</a></div>'
);
define('MODULE_MAGNA_SORT_ORDER', '1');
define('MODULE_MAGNA_STATUS_DESC', 'Modulstatus');
define('MODULE_MAGNA_STATUS_TITLE', 'Status');

class magnalister {
	public $code = '';
	public $title = '';
	public $description = '';
	public $sort_order = '';
	public $enabled = false;
	private $_check = null;

	public function __construct() {
		$this->code = 'magnalister';
		$this->title = MODULE_MAGNA_TEXT_TITLE;
		$this->description = MODULE_MAGNA_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_MAGNA_SORT_ORDER;
		$this->enabled = defined('MODULE_MAGNA_STATUS') && (MODULE_MAGNA_STATUS == 'True');
	}

	function process($file) {

	}

	function display() {
		return array (
			'text' => xtc_button(BUTTON_SAVE) . xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))
		);
	}
	
	function check() {
		if (!isset($this->_check)) {
			$check_query = xtc_db_query("
				SELECT configuration_value 
				  FROM " . TABLE_CONFIGURATION . " 
				 WHERE configuration_key = 'MODULE_MAGNA_STATUS'
			");
			$this->_check = xtc_db_num_rows($check_query) > 0;
		}
		return $this->_check;
	}

	function install() {
		$installed = false;
		$columnsQuery = xtc_db_query('SHOW columns FROM `'.TABLE_ADMIN_ACCESS.'`');
		while ($row = xtc_db_fetch_array($columnsQuery)) {
			if ($row['Field'] == $this->code) {
				$installed = true;
				break;
			}
		}
		if (!$installed) {
			xtc_db_query('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` ADD `'.$this->code.'` INT( 1 ) NOT NULL DEFAULT \'0\';');
		}
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MAGNA_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', NOW())");
		xtc_db_query('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \'1\' LIMIT 1;');
		xtc_db_query('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \''.$_SESSION['customer_id'].'\' LIMIT 1;');
	}

	function remove() {
		xtc_db_query('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` DROP `'.$this->code.'`');
		xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		return array('MODULE_MAGNA_STATUS');
	}
}
