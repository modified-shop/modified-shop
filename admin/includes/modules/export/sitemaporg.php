<?php
/* -----------------------------------------------------------------------------------------
   $Id$   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005	xt-commerce (sitemaporg.php,v 1.6 2003/08/24); www.xt-commerce.com
   (c) 2006	hendrik.koch@gmx.de

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');


// include needed classes
require_once(DIR_FS_EXTERNAL . 'sitemap/sitemap.php');

  // include needed functions
require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');


class sitemaporg extends sitemap {

  var $code;
  var $title;
  var $description;
  var $sort_order;
  var $enabled;
  var $properties;
  var $_check;

  function __construct() {

    $this->code = 'sitemaporg';
    $this->title = MODULE_SITEMAPORG_TEXT_TITLE;
    $this->description = MODULE_SITEMAPORG_TEXT_DESCRIPTION;
    $this->sort_order = ((defined('MODULE_SITEMAPORG_SORT_ORDER')) ? MODULE_SITEMAPORG_SORT_ORDER : '');
    $this->enabled = ((defined('MODULE_SITEMAPORG_STATUS') && MODULE_SITEMAPORG_STATUS == 'True') ? true : false);
    
    $this->properties['button_update'] = '<a class="button btnbox" onclick="this.blur();" href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=export&module=' . $this->code . '&action=update') . '">' . BUTTON_UPDATE. '</a>';
  
    parent::__construct();
  }
    
  function update() {
    $this->process(MODULE_SITEMAPORG_FILE);
    return MODULE_SITEMAPORG_EXPORTED;
  }
  
  function process($file) {
    global $messageStack;
    
    @xtc_set_time_limit(0);
    
    if (isset($_POST['configuration']['MODULE_SITEMAPORG_FILE'])
        && is_array($_POST['configuration']['MODULE_SITEMAPORG_FILE'])
        && count(array_unique($_POST['configuration']['MODULE_SITEMAPORG_FILE'])) != count($_POST['configuration']['MODULE_SITEMAPORG_FILE'])
        )
    {
      $messageStack->add_session(MODULE_SITEMAPORG_ERROR_FILENAME);
      xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=export&module=' . $this->code . '&action=edit'));
    }
    
    $this->export();
  }

  function display() {
    return array('text' => '<br />' . xtc_button(BUTTON_EXPORT) .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=sitemaporg')));
  }

  function check() {
    if (!isset($this->_check)) {
      if (defined('MODULE_SITEMAPORG_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SITEMAPORG_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SITEMAPORG_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_SITEMAPORG_FILE', 'sitemap.xml',  '6', '1', 'xtc_cfg_input_email_language;MODULE_SITEMAPORG_FILE', 'xtc_get_email_language_names', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_SITEMAPORG_CUSTOMERS_STATUS', '1',  '6', '1', 'xtc_cfg_pull_down_customers_status_list(', 'xtc_get_customers_status_name', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SITEMAPORG_ROOT', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SITEMAPORG_GZIP', 'no',  '6', '1', 'xtc_cfg_select_option(array(\'yes\', \'no\'), ', now())");

    // scheduled task
    xtc_db_query("INSERT INTO " . TABLE_SCHEDULED_TASKS . " (time_regularity, time_unit, status, tasks) VALUES ('1', 'd',  '0', 'export_sitemap')");
  }

  function remove() {
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_SITEMAPORG_%'");

    // scheduled task
    xtc_db_query("DELETE FROM " . TABLE_SCHEDULED_TASKS . " WHERE tasks = 'export_sitemap'");
  }

  function keys() {
    $keys = array(
      'MODULE_SITEMAPORG_STATUS',
      'MODULE_SITEMAPORG_FILE',
      'MODULE_SITEMAPORG_CUSTOMERS_STATUS',
      'MODULE_SITEMAPORG_ROOT',
      'MODULE_SITEMAPORG_GZIP',
    );
    $keys = array_values(array_filter($keys));
    
    return $keys;
  }
  
}
