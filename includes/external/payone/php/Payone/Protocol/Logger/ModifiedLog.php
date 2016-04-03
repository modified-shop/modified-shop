<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
 	 based on:
	  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
	  (c) 2002-2003 osCommerce - www.oscommerce.com
	  (c) 2001-2003 TheMedia, Dipl.-Ing Thomas Plänkers - http://www.themedia.at & http://www.oscommerce.at
	  (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com
    (c) 2013 Gambio GmbH - http://www.gambio.de
  
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class Payone_Protocol_Logger_ModifiedLog implements Payone_Protocol_Logger_Interface {
	protected $_logger;
	protected $_event_id;
	protected $_logcount;
	protected $_mode;

	public function __construct($config = null) {
		list($msec, $sec) = explode(' ', microtime());
		$this->_event_id = (int)(($sec + $msec) * 1000);
		$this->_logcount = 0;
		$this->_mode = isset($config) && is_array($config) && array_key_exists('mode', $config) && $config['mode'] == 'transactions' ? 'transactions' : 'api';
	}

	public function getKey() {
		return 'modifiedlog';
	}

	public function log($message, $level = 0) {
		$this->_logcount++;
		$table = $this->_mode == 'api' ? 'payone_api_log' : 'payone_transactions_log';
		$sql_data_array = array(
			'event_id' => (int)$this->_event_id,
			'date_created' => 'now()',
			'log_count' => (int)$this->_logcount,
			'log_level' => (int)$level,
			'message' => $message,
			'customers_id' => ((isset($_SESSION['customer_id'])) ? $_SESSION['customer_id'] : '0'),
		);
		xtc_db_perform($table, $sql_data_array);
	}
}
