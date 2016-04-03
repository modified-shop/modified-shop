<?php
/* -----------------------------------------------------------------------------------------
$Id: preisroboter.php 2666 2012-02-23 11:38:17Z dokuman $

XT-Commerce - community made shopping
http://www.xt-commerce.com

Copyright © 2003 XT-Commerce
-----------------------------------------------------------------------------------------
based on:
© 2000-2001 The Exchange Project (earlier name of osCommerce)
© 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
© 2003 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org

Released under the GNU General Public License
---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_PREISROBOTER_TEXT_DESCRIPTION', 'Export - preisroboter.de (| getrennt)<br /><strong>Format:</strong><br />Artikelnummer|Artikelname|Kurzbeschreibung|Preis|Bild-URL|Deeplink|Lieferzeit|Versandkostenfrei');
define('MODULE_PREISROBOTER_TEXT_TITLE', 'PreisRoboter.de - CSV');
define('MODULE_PREISROBOTER_FILE_TITLE' , '<hr noshade>Dateiname');
define('MODULE_PREISROBOTER_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br />(Verzeichnis export/)');
define('MODULE_PREISROBOTER_STATUS_DESC','Modulstatus');
define('MODULE_PREISROBOTER_STATUS_TITLE','Status');
define('MODULE_PREISROBOTER_CURRENCY_TITLE','W&auml;hrung');
define('MODULE_PREISROBOTER_CURRENCY_DESC','Welche W&auml;hrung soll exportiert werden?');
define('EXPORT_YES','Nur Herunterladen');
define('EXPORT_NO','Am Server Speichern');
define('CURRENCY','<hr noshade><strong>W&auml;hrung:</strong>');
define('CURRENCY_DESC','W&auml;hrung in der Exportdatei');
define('EXPORT','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('EXPORT_TYPE','<hr noshade><strong>Speicherart:</strong>');
define('EXPORT_STATUS_TYPE','<hr noshade><strong>Kundengruppe:</strong>');
define('EXPORT_STATUS','Bitte w&auml;hlen Sie die Kundengruppe, die Basis für den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>):</strong>');
define('DATE_FORMAT_EXPORT', '%d.%m.%Y'); // this is used for strftime()
define('VERSANDKOSTENFREI_AB',999999999);


// include needed functions

//BOF - Tomcraft - 2009-07-21 - Exclude xtc_image_submit.inc.php as it is allready declared in /admin/includes/functions/general.php
//require_once(DIR_FS_INC . 'xtc_image_submit.inc.php');
//EOF - Tomcraft - 2009-07-21 - Exclude xtc_image_submit.inc.php as it is allready declared in /admin/includes/functions/general.php
//require_once(DIR_FS_INC . 'xtc_parse_input_field_data.inc.php');
require_once(DIR_FS_INC . 'xtc_image_button.inc.php');

class preisroboter {
var $code, $title, $description, $enabled;


function preisroboter() {
global $order;

$this->code = 'preisroboter';
$this->title = MODULE_PREISROBOTER_TEXT_TITLE;
$this->description = MODULE_PREISROBOTER_TEXT_DESCRIPTION;
$this->sort_order = MODULE_PREISROBOTER_SORT_ORDER;
$this->enabled = ((MODULE_PREISROBOTER_STATUS == 'True') ? true : false);

}


function process($file) {

@xtc_set_time_limit(0);
require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
$xtPrice = new xtcPrice($_POST['currencies'],$_POST['status']);

//$schema = 'Produktbezeichnung;Beschreibung;Preis;Url;Bild;Währung;Kategorie;Gültig bis;Gültig ab;FSK;Typ;Produkt ID;Hersteller;Bestellnummer;Suchbegriffe;Preistyp' . "\n";
$export_query =xtc_db_query("SELECT
p.products_id,
pd.products_name,
pd.products_description,
p.products_model,
p.products_image,
p.products_price,
p.products_status,
p.products_date_available,
p.products_shippingtime,
p.products_discount_allowed,
pd.products_meta_keywords,
p.products_tax_class_id,
p.products_date_added,
m.manufacturers_name
FROM
" . TABLE_PRODUCTS . " p LEFT JOIN
" . TABLE_MANUFACTURERS . " m
ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
" . TABLE_PRODUCTS_DESCRIPTION . " pd
ON p.products_id = pd.products_id AND
pd.language_id = '".$_SESSION['languages_id']."' LEFT JOIN
" . TABLE_SPECIALS . " s
ON p.products_id = s.products_id
WHERE
p.products_status = 1
ORDER BY
p.products_date_added DESC,
pd.products_name");


while ($products = xtc_db_fetch_array($export_query)) {

$products_price = $xtPrice->xtcGetPrice($products['products_id'],
$format=false,
1,
$products['products_tax_class_id'],
'');

// remove trash
$products_description = strip_tags($products['products_description']);
$products_description = (strlen($products_description)> 150)?substr($products_description,0,145).' ...':$products_description;
$products_description = str_replace("|",", ",$products_description);
$products_description = str_replace("\n"," ",$products_description);
$products_description = str_replace("\r"," ",$products_description);
$products_description = str_replace("\t"," ",$products_description);
$products_description = str_replace("\v"," ",$products_description);
$products_description = str_replace("&quot,"," \"",$products_description);
$products_description = str_replace("&qout,"," \"",$products_description);

        //-- SNAKELAB ----//
            require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');
            $link = xtc_href_link_from_admin('product_info.php', 'products_id=' . $products['products_id']);
            (preg_match("/\?/",$link)) ? $link .= '&' : $link .= '?';
            $link .= 'referer='.$this->code;
            (!empty($_POST['campaign']))
                ? $link .= '&'.$_POST['campaign']
                : false;
        //-- SNAKELAB ----//

//create content
$schema .=
$products['products_id'].'|'.
$products['products_name'] .'|'.
$products_description .'|'.
$products_price. '|'.
HTTP_CATALOG_SERVER . DIR_WS_CATALOG_THUMBNAIL_IMAGES .$products['products_image'] . '|' .
$link. '|' .
'99' . '|' .
(($products_price >= VERSANDKOSTENFREI_AB)?'1':'0'). "\n";

}
// create File
$fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file, "w+");
fputs($fp, $schema);
fclose($fp);


switch ($_POST['export']) {
case 'yes':
// send File to Browser
$extension = substr($file, -3);
$fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file,"rb");
$buffer = fread($fp, filesize(DIR_FS_DOCUMENT_ROOT.'export/' . $file));
fclose($fp);
header('Content-type: application/x-octet-stream');
header('Content-disposition: attachment; filename=' . $file);
echo $buffer;
exit;

break;
}

}

function display() {

$customers_statuses_array = xtc_get_customers_statuses();

// build Currency Select
$curr='';
$currencies=xtc_db_query("SELECT code FROM ".TABLE_CURRENCIES);
while ($currencies_data=xtc_db_fetch_array($currencies)) {
$curr.=xtc_draw_radio_field('currencies', $currencies_data['code'],true).$currencies_data['code'].'<br />';
}

return array('text' => EXPORT_STATUS_TYPE.'<br />'.
EXPORT_STATUS.'<br />'.
xtc_draw_pull_down_menu('status',$customers_statuses_array, '1').'<br />'.
CURRENCY.'<br />'.
CURRENCY_DESC.'<br />'.
$curr.
EXPORT_TYPE.'<br />'.
EXPORT.'<br />'.
xtc_draw_radio_field('export', 'no',false).EXPORT_NO.'<br />'.
xtc_draw_radio_field('export', 'yes',true).EXPORT_YES.'<br />'.
//BOF - Tomcraft - 2009-07-21 - Exclude xtc_image_submit.inc.php as it is allready declared in /admin/includes/functions/general.php
/*
'<br />' . xtc_image_submit('button_export.gif', IMAGE_UPDATE) .

'<a href="' . xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=preisroboter') . '">' .
xtc_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
*/
'<br>' . xtc_button(BUTTON_EXPORT) .

xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=preisroboter')));
//EOF - Tomcraft - 2009-07-21 - Exclude xtc_image_submit.inc.php as it is allready declared in /admin/includes/functions/general.php

}

function check() {
if (!isset($this->_check)) {
$check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PREISROBOTER_STATUS'");
$this->_check = xtc_db_num_rows($check_query);
}
return $this->_check;
}

function install() {
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PREISROBOTER_FILE', 'preisroboter.txt', '6', '1', '', now())");
xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PREISROBOTER_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
}

function remove() {
xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
}

function keys() {
return array('MODULE_PREISROBOTER_STATUS','MODULE_PREISROBOTER_FILE');
}

}
?>
