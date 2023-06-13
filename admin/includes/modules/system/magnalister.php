<?php
/*
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
 * (c) 2010 - 2022 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') OR die('Direct access to this location is not allowed.');

if (!defined('MODULE_MAGNALISTER_TEXT_TITLE'))
    define('MODULE_MAGNALISTER_TEXT_TITLE', 'magnalister');
if (!defined('MODULE_MAGNALISTER_TEXT_DESCRIPTION'))
    define('MODULE_MAGNALISTER_TEXT_DESCRIPTION', '<div style="margin-left: 0.5em;">magnalister - Die ultimative Schnittstelle zu eBay, Amazon & Co.<br><br>Weitere Infos unter 
        <a href="https://www.magnalister.com" target="_blank" style="text-decoration:underline">www.magnalister.com</a></div>'
    );
if (!defined('MODULE_MAGNALISTER_SORT_ORDER'))
    define('MODULE_MAGNALISTER_SORT_ORDER', '1');
if (!defined('MODULE_MAGNALISTER_STATUS_DESC'))
    define('MODULE_MAGNALISTER_STATUS_DESC', 'Modulstatus');
if (!defined('MODULE_MAGNALISTER_STATUS_TITLE'))
    define('MODULE_MAGNALISTER_STATUS_TITLE', 'Status');

class magnalister {
	public $code = '';
	public $title = '';
	public $description = '';
	public $sort_order = '';
	public $enabled = false;
	private $_check = null;
	
	private $foundFnWrapper = '';
	private $fnWrp = array();
	
	public function __construct() {
		$this->code = 'magnalister';
		$this->title = MODULE_MAGNALISTER_TEXT_TITLE;
		$this->description = MODULE_MAGNALISTER_TEXT_DESCRIPTION;
		$this->sort_order = defined('MODULE_MAGNALISTER_SORT_ORDER') ? MODULE_MAGNALISTER_SORT_ORDER : '';
		$this->enabled = defined('MODULE_MAGNALISTER_STATUS') && (MODULE_MAGNALISTER_STATUS == 'True');
		
		$fnWrappers = array('xtc', 'tep');
		$this->foundFnWrapper = '';
		foreach ($fnWrappers as $wrapper) {
			if (function_exists($wrapper.'_db_query')) {
				$this->foundFnWrapper = $wrapper;
				break;
			}
		}
		if (empty($this->foundFnWrapper)) {
			$this->foundFnWrapper = 'xtc';
		}
		
		foreach (array(
			'button', 'button_link', 'href_link', 'db_query', 'db_num_rows', 'db_fetch_array'
		) as $fn) {
			$this->fnWrp[$fn] = $this->foundFnWrapper.'_'.$fn;
		}
		
	}

	function process($file) {
    if (defined('TABLE_SCHEDULED_TASKS')
        && isset($_POST['configuration'])
        && isset($_POST['configuration']['MODULE_MAGNALISTER_STATUS'])
        )
    {
      $this->fnWrp['db_query']("UPDATE ".TABLE_SCHEDULED_TASKS."
                                   SET status = '".(($_POST['configuration']['MODULE_MAGNALISTER_STATUS'] == 'True') ? 1 : 0)."'
                                 WHERE tasks = 'magnalister'");
    }
	}

	function display() {
		if (!defined('BUTTON_SAVE')) {
			define('BUTTON_SAVE', 'Save');
		}
		if (!defined('BUTTON_BACK')) {
			define('BUTTON_BACK', 'Back');
		}
		return array (
			'text' => $this->fnWrp['button'](BUTTON_SAVE) . $this->fnWrp['button_link'](BUTTON_BACK, $this->fnWrp['href_link'](FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code))
		);
	}
	
	function check() {
		if (!isset($this->_check)) {
			if (defined('MODULE_MAGNALISTER_STATUS')) {
				$this->_check = true;
			} else {
				$check_query = $this->fnWrp['db_query']("
					SELECT configuration_value 
					  FROM " . TABLE_CONFIGURATION . " 
					 WHERE configuration_key = 'MODULE_MAGNALISTER_STATUS'
				");
				$this->_check = $this->fnWrp['db_num_rows']($check_query) > 0;
			}
		}
		return $this->_check;
	}

	function install() {
		if (defined('TABLE_ADMIN_ACCESS')) {
			$installed = false;
			$columnsQuery = $this->fnWrp['db_query']('SHOW columns FROM `'.TABLE_ADMIN_ACCESS.'`');
			while ($row = $this->fnWrp['db_fetch_array']($columnsQuery)) {
				if ($row['Field'] == $this->code) {
					$installed = true;
					break;
				}
			}
			if (!$installed) {
				$this->fnWrp['db_query']('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` ADD `'.$this->code.'` INT( 1 ) NOT NULL DEFAULT \'0\';');
			}
			$this->fnWrp['db_query']('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \'1\' LIMIT 1;');
			$this->fnWrp['db_query']('UPDATE `'.TABLE_ADMIN_ACCESS.'` SET `'.$this->code.'` = \'1\' WHERE `customers_id` = \''.$_SESSION['customer_id'].'\' LIMIT 1;');
		}
		$this->fnWrp['db_query']("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_MAGNALISTER_STATUS', 'True',  '6', '1', '".$this->foundFnWrapper."_cfg_select_option(array(\'True\', \'False\'), ', NOW())");

    // check scheduled tasks
    if (defined('TABLE_SCHEDULED_TASKS')) {
      $check_query = $this->fnWrp['db_query']("SELECT *
                                                 FROM ".TABLE_SCHEDULED_TASKS."
                                                WHERE tasks = 'magnalister'");
      if ($this->fnWrp['db_num_rows']($check_query) < 1) {                      
        $this->fnWrp['db_query']("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('15', 'm',  '1', 'magnalister')");
      }
    }
	}

	function remove() {
		if (defined('TABLE_ADMIN_ACCESS')) {
			$this->fnWrp['db_query']('ALTER TABLE `'.TABLE_ADMIN_ACCESS.'` DROP `'.$this->code.'`');
		}
		$this->fnWrp['db_query']("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $this->keys()) . "')");

    // scheduled task
    if (defined('TABLE_SCHEDULED_TASKS')) {
      $this->fnWrp['db_query']("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'magnalister'");
    }
	}

	function keys() {
		return array('MODULE_MAGNALISTER_STATUS');
	}
}
