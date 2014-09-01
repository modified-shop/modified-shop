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

define('MODULE_JANOLAW_TEXT_TITLE', 'janolaw AGB Hosting-Service');
define('MODULE_JANOLAW_TEXT_DESCRIPTION', '<a href="http://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><img src="images/janolaw/janolaw_185x35.png" border=0></a><br /><br />Deutschlands gro&szlig;es Rechtsportal janolaw bietet ma&szlig;geschneiderte L&ouml;sungen f&uuml;r Ihre Rechtsfragen - von der Anwaltshotline bis zu individuellen Vertr&auml;gen mit Anwaltsgarantie. Mit dem AGB Hosting-Service f&uuml;r Internetshops k&ouml;nnen Sie die rechtlichen Kerndokumente AGB, Widerrufsbelehrung, Impressum und Datenschutzerkl&auml;rung individuell auf Ihren Shop anpassen und laufend durch das janolaw Team aktualisieren lassen. Mehr Schutz geht nicht.<br /><br /><a href="http://www.janolaw.de/internetrecht/agb/agb-hosting-service/modified/index.html?partnerid=8764#menu" target="_blank"><strong><u>Hier geht&#x27;s zum Angebot<u></strong></a>');
define('MODULE_JANOLAW_USER_ID_TITLE', '<hr noshade>User-ID');
define('MODULE_JANOLAW_USER_ID_DESC', 'Ihre User-ID');
define('MODULE_JANOLAW_SHOP_ID_TITLE', '<hr noshade>Shop-ID');
define('MODULE_JANOLAW_SHOP_ID_DESC', 'Die Shop-ID Ihres Onlineshops');
define('MODULE_JANOLAW_STATUS_DESC', 'Modul aktivieren?');
define('MODULE_JANOLAW_STATUS_TITLE', 'Status');
define('MODULE_JANOLAW_TYPE_TITLE', 'Speichern als');
define('MODULE_JANOLAW_TYPE_DESC', 'Sollen die Daten in einer Datei oder in der Datenbank gepseichert werden?');
define('MODULE_JANOLAW_FORMAT_TITLE', 'Format Typ');
define('MODULE_JANOLAW_FORMAT_DESC', 'Sollen die Daten als Text oder HTML gepseichert werden?');
define('MODULE_JANOLAW_UPDATE_INTERVAL_TITLE', 'Update Interval');
define('MODULE_JANOLAW_UPDATE_INTERVAL_DESC', 'In welchen Abst&auml;nden sollen die Daten aktualisiert werden?');


// include needed functions
class janolaw {
  var $code, $title, $description, $enabled;

  function janolaw() {
    global $order;

     $this->code = 'janolaw';
     $this->title = MODULE_JANOLAW_TEXT_TITLE;
     $this->description = MODULE_JANOLAW_TEXT_DESCRIPTION;
     $this->enabled = ((MODULE_JANOLAW_STATUS == 'True') ? true : false);
   }

  function process($file) {
    require_once(DIR_FS_CATALOG.'includes/external/janolaw/janolaw.php');
    $janolaw = new janolaw_content();
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
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_SHOP_ID', '12345',  '6', '2', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_USER_ID', '12345',  '6', '3', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_TYPE', 'Database',  '6', '4', 'xtc_cfg_select_option(array(\'File\', \'Database\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_FORMAT', 'HTML',  '6', '5', 'xtc_cfg_select_option(array(\'HTML\', \'TXT\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_UPDATE_INTERVAL', '86400',  '6', '6', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_JANOLAW_LAST_UPDATED', '',  '6', '6', '', now())");
  }

  function remove() {
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
                 );
  }
}
?>