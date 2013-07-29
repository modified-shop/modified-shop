<?php
/**
 * magnalister fuer xt:commerce v3 und gambio
 * Copyright (c) 2010 redgecko GmbH (http://www.redgecko.de/)
 *
 * Licensed under GNU/GPL v2
 *
 * Id: $Id: magnalister.php 69 2010-09-22 09:17:05Z derpapst $
 */
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_MAGNA_TEXT_TITLE', 'magnalister');
define('MODULE_MAGNA_TEXT_DESCRIPTION', '<div style="margin-left: 0.5em;">magnalister - das ultimative Listing-Tool f&uuml;r amazon, yatego, 
	g&uuml;nstiger.de, daparto und viele mehr.<br><br>Weitere Infos unter 
	<a href="http://www.magnalister.com" target="_blank" style="text-decoration:underline">www.magnalister.com</a></div>'
);

class magnalister {
	public $code;
	public $title;
	public $description;
	public $sort_order;
	public $enabled;
	private $_check;

    public function __construct() {
		$this->code = 'magnalister';
		$this->title = MODULE_MAGNA_TEXT_TITLE;
		$this->description = MODULE_MAGNA_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_MAGNA_SORT_ORDER;

		$this->_check = false;
		$columnsQuery = xtc_db_query('SHOW columns FROM `'.TABLE_ADMIN_ACCESS.'`');
		while ($row = xtc_db_fetch_array($columnsQuery)) {
			if ($row['Field'] == $this->code) {
				$this->_check = true;
				break;
			}
		}
		$this->enabled = $this->_check;
	}

	function process($file) {

	}

	function display() {
		return array (
			'text' => xtc_button_link(BUTTON_BACK, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))
		);
    }

    function check() {
		return $this->_check;
	}

	function install() {
		if (!$this->_check) {
			xtc_db_query('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` ADD `'.$this->code.'` INT( 1 ) NOT NULL DEFAULT \'0\';');
		}
		xtc_db_query('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \'1\' LIMIT 1;');
		xtc_db_query('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \''.$_SESSION['customer_id'].'\' LIMIT 1;');
	}

	function remove() {
		xtc_db_query('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` DROP `'.$this->code.'`');
	}

	function keys() {
		return array();
	}
}
