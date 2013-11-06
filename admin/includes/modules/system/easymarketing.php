<?php
/* -----------------------------------------------------------------------------------------
   $Id:$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_EASYMARKETING_TEXT_DESCRIPTION', 'Modul - Easymarketing.de');
define('MODULE_EASYMARKETING_TEXT_TITLE', 'Easymarketing.de');
define('MODULE_EASYMARKETING_STATUS_TITLE','Status');
define('MODULE_EASYMARKETING_STATUS_DESC','Modulstatus') ;
define('MODULE_EASYMARKETING_SHOP_TOKEN_TITLE','Shop Token') ;
define('MODULE_EASYMARKETING_SHOP_TOKEN_DESC','Dieser muss bei Easymarketing hinterlegt werden.<br/><br/><b>Hinweis:</b> Der Token muss 32 Zeichen lang sein. Sollte dies nicht der Fall sein, wird dieser automatisch erzeugt.');
define('MODULE_EASYMARKETING_ACCESS_TOKEN_TITLE','Access Token') ;
define('MODULE_EASYMARKETING_ACCESS_TOKEN_DESC','Diesen Token erhalten sie von Easymarketing.');
define('MODULE_EASYMARKETING_CONDITION_DEFAULT_TITLE','Artikelzustand') ;
define('MODULE_EASYMARKETING_CONDITION_DEFAULT_DESC','Bitte geben sie den Zustand der Artikel an.');
define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0_TITLE','Verf&uuml;gbarkeit - Lagerbestand < 1') ;
define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0_DESC','Bitte geben sie die Verf&uuml;gbarkeit der Artikel an, welche im Shop einen negativen Lagerbestand oder einen Lagerbestand von 0 haben');
define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1_TITLE','Verf&uuml;gbarkeit - Lagerbestand > 0') ;
define('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1_DESC','Bitte geben sie die Verf&uuml;gbarkeit der Artikel an, welche im Shop einen positiven Lagerbestand haben');


class easymarketing {
  var $code, $title, $description, $enabled;


  function easymarketing() {
    global $order;

    $this->code = 'easymarketing';
    $this->title = MODULE_EASYMARKETING_TEXT_TITLE;
    $this->description = MODULE_EASYMARKETING_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_EASYMARKETING_SORT_ORDER;
    $this->enabled = ((MODULE_EASYMARKETING_STATUS == 'True') ? true : false);
  }


  function process($file) {
    if ($_POST['configuration']['MODULE_EASYMARKETING_SHOP_TOKEN'] != MODULE_EASYMARKETING_SHOP_TOKEN 
        || strlen(MODULE_EASYMARKETING_SHOP_TOKEN) != '32') 
    {
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = '".md5(xtc_rand(0, 9999999999))."' WHERE configuration_key = 'MODULE_EASYMARKETING_SHOP_TOKEN'");
    }
  }
  
  function display() {
    return array('text' =>  '<br>' . xtc_button(BUTTON_SAVE) .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=easymarketing')));
  }


  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_EASYMARKETING_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }


  function install() {
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_CONDITION_DEFAULT', 'new',  '6', '1', 'xtc_cfg_select_condition(', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_SHOP_TOKEN', '',  '6', '1', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_ACCESS_TOKEN', '',  '6', '1', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0', 'available for order',  '6', '1', 'xtc_cfg_select_availibility(', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1', 'in stock',  '6', '1', 'xtc_cfg_select_availibility(', now())");
  }


  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }


  function keys() {
    return array('MODULE_EASYMARKETING_STATUS',
                 'MODULE_EASYMARKETING_SHOP_TOKEN',
                 'MODULE_EASYMARKETING_ACCESS_TOKEN',
                 'MODULE_EASYMARKETING_CONDITION_DEFAULT',
                 'MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_1',
                 'MODULE_EASYMARKETING_AVAILIBILLITY_STOCK_0',
                 );
  }
}


//additional functions
function xtc_cfg_select_condition($configuration, $key) {
  $condition_dropdown = array(
                          array('id' => 'new', 'text' => 'Neu'),
                          array('id' => 'refurbished', 'text' => 'Erneuert'),
                          array('id' => 'used', 'text' => 'Gebraucht'),
                        );
  return xtc_draw_pull_down_menu('configuration['.$key.']', $condition_dropdown, $configuration);
}

function xtc_cfg_select_availibility($configuration, $key) {
  $availibility_dropdown = array(
                             array('id' => 'in stock', 'text' => 'Auf Lager'),
                             array('id' => 'available for order', 'text' => 'Bestellbar'),
                             array('id' => 'out of stock', 'text' => 'Nicht auf Lager'),
                             array('id' => 'preorder', 'text' => 'Vorbestellen'),
                           );
  return xtc_draw_pull_down_menu('configuration['.$key.']', $availibility_dropdown, $configuration);
}

?>