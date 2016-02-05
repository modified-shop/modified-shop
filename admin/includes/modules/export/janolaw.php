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
define('MODULE_JANOLAW_SHOP_ID_TITLE', 'Shop-ID');
define('MODULE_JANOLAW_SHOP_ID_DESC', 'Die Shop-ID Ihres Onlineshops');
define('MODULE_JANOLAW_STATUS_DESC', 'Modul aktivieren?');
define('MODULE_JANOLAW_STATUS_TITLE', 'Status');
define('MODULE_JANOLAW_TYPE_TITLE', '<hr noshade>Speichern als');
define('MODULE_JANOLAW_TYPE_DESC', 'Sollen die Daten in einer Datei oder in der Datenbank gespeichert werden?');
define('MODULE_JANOLAW_FORMAT_TITLE', 'Format Typ');
define('MODULE_JANOLAW_FORMAT_DESC', 'Sollen die Daten als Text oder HTML gespeichert werden?');
define('MODULE_JANOLAW_UPDATE_INTERVAL_TITLE', '<hr noshade>Update Intervall');
define('MODULE_JANOLAW_UPDATE_INTERVAL_DESC', 'In welchen Abst&auml;nden sollen die Daten aktualisiert werden?');
define('MODULE_JANOLAW_ERROR', 'Bitte pr&uuml;fen sie die Zuordnung der Dokumente.');

define('MODULE_JANOLAW_TYPE_DATASECURITY_TITLE', '<hr noshade>Rechtstext Datenschutz');
define('MODULE_JANOLAW_TYPE_DATASECURITY_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_DATASECURITY_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_DATASECURITY_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_DATASECURITY_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_DATASECURITY_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_TERMS_TITLE', '<hr noshade>Rechtstext AGB');
define('MODULE_JANOLAW_TYPE_TERMS_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_TERMS_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_TERMS_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_TERMS_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_TERMS_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_LEGALDETAILS_TITLE', '<hr noshade>Rechtstext Impressum');
define('MODULE_JANOLAW_TYPE_LEGALDETAILS_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_LEGALDETAILS_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_LEGALDETAILS_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_REVOCATION_TITLE', '<hr noshade>Rechtstext Widerruf');
define('MODULE_JANOLAW_TYPE_REVOCATION_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.');
define('MODULE_JANOLAW_PDF_REVOCATION_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_REVOCATION_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_REVOCATION_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_REVOCATION_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');

define('MODULE_JANOLAW_TYPE_WITHDRAWAL_TITLE', '<hr noshade>Rechtstext Widerrufsformular');
define('MODULE_JANOLAW_TYPE_WITHDRAWAL_DESC', 'Bitte geben Sie an, in welcher Seite dieser Rechtstext automatisch eingef&uuml;gt werden soll.<br/><br/><b>Wichtig:</b> das funktioniert erst ab Version 3. Die Umstellung kann bei Janolaw veranlasst werden.');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_TITLE', 'PDF als Download');
define('MODULE_JANOLAW_PDF_WITHDRAWAL_DESC', 'Sollen die Daten zus&auml;tzlich als PDF gespeichert und ein Link eingef&uuml;gt werden?<br/><b>Wichtig:</b> Das funktioniert nur in der HTML Version!');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_TITLE', 'PDF als E-Mail Anhang');
define('MODULE_JANOLAW_MAIL_WITHDRAWAL_DESC', 'Soll das PDF als Anhang zur Auftragsbest&auml;tigung mitgeschickt werden?');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_TITLE', 'Kombinierte Widerrufsbelehrung/Widerrufsformular');
define('MODULE_JANOLAW_WITHDRAWAL_COMBINE_DESC', 'Soll eine kombinierte Widerrufsbelehrung mit Widerrufsformular erstellt werden?');

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
    global $messageStack;

    // include needed class
    require_once(DIR_FS_CATALOG.'includes/external/janolaw/janolaw.php');
    
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

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_DATASECURITY', '',  '6', '1', 'xtc_cfg_select_content_module_jl(', 'xtc_cfg_display_content_jl', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_TERMS', '',  '6', '1', 'xtc_cfg_select_content_module_jl(', 'xtc_cfg_display_content_jl', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_LEGALDETAILS', '',  '6', '1', 'xtc_cfg_select_content_module_jl(', 'xtc_cfg_display_content_jl', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_REVOCATION', '',  '6', '1', 'xtc_cfg_select_content_module_jl(', 'xtc_cfg_display_content_jl', now())");
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, use_function, date_added) VALUES ('MODULE_JANOLAW_TYPE_WITHDRAWAL', '',  '6', '1', 'xtc_cfg_select_content_module_jl(', 'xtc_cfg_display_content_jl', now())");
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

// additional function
/**
 * xtc_cfg_select_content_jl()
 *
 * @param string $cfg_key
 * @param string $cfg_value
 * @param string $name
 * @return pulldown
 */
function xtc_cfg_select_content_jl($cfg_key, $cfg_value, $name = '%s') {
  $content_array = array(array('id' => '', 'text' => TEXT_SELECT));
  $content_query = xtc_db_query("SELECT content_group, 
                                        content_title 
                                   FROM ".TABLE_CONTENT_MANAGER." 
                                  WHERE languages_id = '".(int)$_SESSION['languages_id']."'
                               GROUP BY content_group");
  while ($content = xtc_db_fetch_array($content_query)) {
    $content_array[] = array('id' => $content['content_group'], 'text' => $content['content_title'] . ' (coID: '.$content['content_group'].')');
  }
  return xtc_draw_pull_down_menu(sprintf($name, $cfg_key), $content_array, $cfg_value);
}

/**
 * xtc_cfg_select_content_module_jl()
 *
 * @param string $configuration
 * @param string $key
 * @return pulldown
 */
function xtc_cfg_select_content_module_jl($cfg_value, $cfg_key) {
  return xtc_cfg_select_content_jl($cfg_key, $cfg_value, 'configuration[%s]');
}

/**
 * xtc_cfg_display_content_jl()
 *
 * @param string $content_group
 * @return string
 */
function xtc_cfg_display_content_jl($content_group) {
  $content_query = xtc_db_query("SELECT content_title 
                                   FROM ".TABLE_CONTENT_MANAGER." 
                                  WHERE languages_id = '".(int)$_SESSION['languages_id']."' 
                                    AND content_group = '".$content_group."'
                                  LIMIT 1");
  $content = xtc_db_fetch_array($content_query);
  return $content['content_title'];
}

?>