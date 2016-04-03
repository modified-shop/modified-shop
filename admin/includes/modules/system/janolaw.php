<?php
/* -----------------------------------------------------------------------------------------
   $Id: janolaw.php 2011-11-24 modified-shop $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003   nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: billiger.php 950 2005-05-14 16:45:21Z mz $)
   (c) 2008 Gambio OHG (billiger.php 2008-11-11 gambio)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class janolaw {
  var $code, $title, $description, $enabled;

  function __construct() {
    global $order;

     $this->code = 'janolaw';
     $this->title = MODULE_JANOLAW_TEXT_TITLE;
     $this->description = MODULE_JANOLAW_TEXT_DESCRIPTION;
     $this->enabled = ((MODULE_JANOLAW_STATUS == 'True') ? true : false);
   }

  function process($file) {
    global $messageStack;

    // include needed class
    require_once(DIR_FS_EXTERNAL.'janolaw/janolaw.php');
    
    $error = false;
    $check_array = array(janolaw_content::get_configuration('MODULE_JANOLAW_TYPE_DATASECURITY'),
                         janolaw_content::get_configuration('MODULE_JANOLAW_TYPE_TERMS'),
                         janolaw_content::get_configuration('MODULE_JANOLAW_TYPE_LEGALDETAILS'),
                         janolaw_content::get_configuration('MODULE_JANOLAW_TYPE_REVOCATION'),
                         janolaw_content::get_configuration('MODULE_JANOLAW_TYPE_WITHDRAWAL')
                         );
    $check = array_count_values($check_array);
    foreach ($check as $key => $value) {
      if ($key != '' && $value > 1) {
        $error = true;
        break;
      }
    }
    
    if ($error === true) {
      $messageStack->add_session(MODULE_JANOLAW_ERROR, 'warning');
    } else {    
      $janolaw = new janolaw_content();
    }
  }

  function display() {
    $interval_array = array(array('id' => '86400', 'text' => '24 Stunden'),
                            array('id' => '43200', 'text' => '12 Stunden'),
                            array('id' => '21600', 'text' => '6 Stunden'),
                            array('id' => '10800', 'text' => '3 Stunden'),
                            array('id' => '3600',  'text' => '1 Stunden'),
                           );
    
    return array('text' => '<br/><b>'.MODULE_JANOLAW_UPDATE_INTERVAL_TITLE.'</b>
                            <br/>'.MODULE_JANOLAW_UPDATE_INTERVAL_DESC.'<br/>'.
                            xtc_draw_pull_down_menu('configuration[MODULE_JANOLAW_UPDATE_INTERVAL]', $interval_array, MODULE_JANOLAW_UPDATE_INTERVAL).'<br />'.
                           '<br /><div align="center">' . xtc_button('OK') .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=janolaw')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_JANOLAW_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_STATUS', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_SHOP_ID', '',  '6', '2', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_USER_ID', '',  '6', '3', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_TYPE', 'Database',  '6', '4', 'xtc_cfg_select_option(array(\'File\', \'Database\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_FORMAT', 'HTML',  '6', '5', 'xtc_cfg_select_option(array(\'HTML\', \'TXT\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_UPDATE_INTERVAL', '86400',  '6', '6', '', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_LAST_UPDATED', '',  '6', '7', '', now())");

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_PDF_DATASECURITY', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_PDF_TERMS', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_PDF_LEGALDETAILS', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_PDF_REVOCATION', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_PDF_WITHDRAWAL', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_MAIL_DATASECURITY', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_MAIL_TERMS', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_MAIL_LEGALDETAILS', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_MAIL_REVOCATION', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_MAIL_WITHDRAWAL', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_JANOLAW_WITHDRAWAL_COMBINE', 'False',  '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_DATASECURITY', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_TERMS', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_LEGALDETAILS', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_REVOCATION', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_WITHDRAWAL', '',  '6', '1', 'xtc_cfg_select_content_module(', 'xtc_cfg_display_content', now())");
  }

  function remove() {
    $database_table = 'content_file';
    if (MODULE_JANOLAW_TYPE == 'Database') {
      $database_table = 'content_text';
    }
    xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                     SET ".$database_table." = ''
                   WHERE content_group = '".MODULE_JANOLAW_TYPE_DATASECURITY."'");
    xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                     SET ".$database_table." = ''
                   WHERE content_group = '".MODULE_JANOLAW_TYPE_TERMS."'");
    xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                     SET ".$database_table." = ''
                   WHERE content_group = '".MODULE_JANOLAW_TYPE_LEGALDETAILS."'");
    xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                     SET ".$database_table." = ''
                   WHERE content_group = '".MODULE_JANOLAW_TYPE_REVOCATION."'");
    xtc_db_query("UPDATE ".TABLE_CONTENT_MANAGER."
                     SET ".$database_table." = ''
                   WHERE content_group = '".MODULE_JANOLAW_TYPE_WITHDRAWAL."'");

    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_JANOLAW_UPDATE_INTERVAL'");
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_JANOLAW_LAST_UPDATED'");
  }

  function keys() {
    return array('MODULE_JANOLAW_STATUS',
                 'MODULE_JANOLAW_USER_ID',
                 'MODULE_JANOLAW_SHOP_ID',
                 'MODULE_JANOLAW_TYPE',
                 'MODULE_JANOLAW_FORMAT',

                 'MODULE_JANOLAW_TYPE_DATASECURITY',
                 'MODULE_JANOLAW_PDF_DATASECURITY',
                 'MODULE_JANOLAW_MAIL_DATASECURITY',

                 'MODULE_JANOLAW_TYPE_TERMS',
                 'MODULE_JANOLAW_PDF_TERMS',
                 'MODULE_JANOLAW_MAIL_TERMS',

                 'MODULE_JANOLAW_TYPE_LEGALDETAILS',
                 'MODULE_JANOLAW_PDF_LEGALDETAILS',
                 'MODULE_JANOLAW_MAIL_LEGALDETAILS',

                 'MODULE_JANOLAW_TYPE_REVOCATION',
                 'MODULE_JANOLAW_PDF_REVOCATION',
                 'MODULE_JANOLAW_MAIL_REVOCATION',

                 'MODULE_JANOLAW_TYPE_WITHDRAWAL', 
                 'MODULE_JANOLAW_WITHDRAWAL_COMBINE',                 
                 'MODULE_JANOLAW_PDF_WITHDRAWAL',                 
                 'MODULE_JANOLAW_MAIL_WITHDRAWAL',                 
                 );
  }
}
?>