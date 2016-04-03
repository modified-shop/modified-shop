<?php
/* -----------------------------------------------------------------------------------------
   $Id: findologic.php 2011-11-24 modified-shop $

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

// include needed functions
class findologic {
  var $code, $title, $description, $enabled;

  function __construct() {
     $this->code = 'findologic';
     $this->title = MODULE_FINDOLOGIC_TEXT_TITLE;
     $this->description = MODULE_FINDOLOGIC_TEXT_DESCRIPTION;
     $this->sort_order = MODULE_FINDOLOGIC_SORT_ORDER;
     $this->enabled = ((MODULE_FINDOLOGIC_STATUS == 'True') ? true : false);
   }

  function process($file) {

  }

  function display() {
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
                           xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=findologic')) . "</div>");
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_FINDOLOGIC_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }


  function install_additional() {
    $languages = xtc_get_languages();
    
    $key = array();
    for ($i=0, $n=count($languages); $i<$n; $i++) {
      $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_FINDOLOGIC_SERVICE_URL_".strtoupper($languages[$i]['code'])."'");
      if (xtc_db_num_rows($check_query) == 0) {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_SHOP_ID_".strtoupper($languages[$i]['code'])."', '',  '6', '1', '', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_SERVICE_URL_".strtoupper($languages[$i]['code'])."', '',  '6', '1', '', now())");
      }
      $key[] = 'MODULE_FINDOLOGIC_SHOP_ID_'.strtoupper($languages[$i]['code']);
      $key[] = 'MODULE_FINDOLOGIC_SERVICE_URL_'.strtoupper($languages[$i]['code']);

      define('MODULE_FINDOLOGIC_SHOP_ID_'.strtoupper($languages[$i]['code']).'_TITLE', '<hr noshade>Shopkey - '.xtc_cfg_fl_get_language($languages[$i]['code']).'</b>');
      define('MODULE_FINDOLOGIC_SHOP_ID_'.strtoupper($languages[$i]['code']).'_DESC', 'Ihr Shopkey<br />Sie finden den Shopkey im FINDOLOGIC Kundenaccount &rarr; Men&uuml; Account &rarr; Stammdaten.');
      define('MODULE_FINDOLOGIC_SERVICE_URL_'.strtoupper($languages[$i]['code']).'_TITLE', '<b><hr noshade>FINDOLOGIC/Service-URL - '.xtc_cfg_fl_get_language($languages[$i]['code']).'</b>');
      define('MODULE_FINDOLOGIC_SERVICE_URL_'.strtoupper($languages[$i]['code']).'_DESC', 'Die FINDOLOGIC/Service-URL Ihres Onlineshops<br /><strong>WICHTIG:</strong> Vergessen Sie bei den URLs nicht den Slash am Ende, da es sonst zu Problemen bei der Darstellung der Ergebnisse kommt.<br />Sie finden die FINDOLOGIC/Service-URL im FINDOLOGIC Kundenaccount &rarr; Men&uuml; Account &rarr; Stammdaten.');
    }
    return $key;
  }

    
  function install() {
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_FINDOLOGIC_LANG', 'de',  '6', '1', 'xtc_cfg_fl_get_language', 'xtc_cfg_fl_select_language(', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_FINDOLOGIC_CUSTOMER_GROUP', '1',  '6', '1', 'xtc_get_customers_status_name', 'xtc_cfg_fl_select_status(', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_CURRENCY', 'EUR',  '6', '1', 'xtc_cfg_fl_select_currency(', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_FINDOLOGIC_AUTOCOMPLETE', 'False',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

  function keys() {
    $key = array('MODULE_FINDOLOGIC_STATUS',
                 'MODULE_FINDOLOGIC_CUSTOMER_GROUP',
                 'MODULE_FINDOLOGIC_CURRENCY',
                 'MODULE_FINDOLOGIC_LANG',
                 'MODULE_FINDOLOGIC_AUTOCOMPLETE');

    $additional = $this->install_additional();
    return array_merge($key, $additional);
  }
}


//additional functions
function xtc_cfg_fl_select_currency($configuration, $key) {
    $currency = '';
    $currencies=xtc_db_query("SELECT code FROM ".TABLE_CURRENCIES);
    while ($currencies_data=xtc_db_fetch_array($currencies)) {
     $currency .= xtc_draw_radio_field('configuration['.$key.']', $currencies_data['code'], (($currencies_data['code'] == MODULE_FINDOLOGIC_CURRENCY) ? true : '')) . $currencies_data['code'] . '<br>';
    }
  return $currency;
}

function xtc_cfg_fl_select_status($configuration, $key) {
  return xtc_draw_pull_down_menu('configuration['.$key.']', xtc_get_customers_statuses(), $configuration);
}

function xtc_cfg_fl_select_language($configuration, $key) {
  $languages_query = xtc_db_query("SELECT code, 
                                          name
                                     FROM ".TABLE_LANGUAGES." 
                                    WHERE status = '1' 
                                 ORDER BY sort_order
                                   ");
  while ($languages = xtc_db_fetch_array($languages_query)) {
    $languages_array[] = array ('id' => $languages['code'],
                                'text' => $languages['name']
                                );
  }
  return xtc_draw_pull_down_menu('configuration['.$key.']', $languages_array, $configuration);
}

function xtc_cfg_fl_get_language($code) {
  $languages_query = xtc_db_query("SELECT name
                                     FROM ".TABLE_LANGUAGES." 
                                    WHERE code = '".$code."'");
  $languages = xtc_db_fetch_array($languages_query);
  return $languages['name'];
}

?>