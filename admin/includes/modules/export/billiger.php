<?php
/* -----------------------------------------------------------------------------------------
   $Id: billiger.php 2020 2011-06-24 10:10:55Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2006 xt-commerce; www.xt-commerce.com
   (c) 2008 modified by m3WebWork.de - version 1.1

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

define('MODULE_BILLIGER_TEXT_DESCRIPTION', 'Einfach mit wenigen Klicks alle Artikel samt Versandkosten f&uuml;r www.billiger.de exportieren.');
define('MODULE_BILLIGER_TEXT_TITLE', 'Billiger.de Export - CSV');
define('MODULE_BILLIGER_FILE_TITLE' , '<hr />Dateiname');
define('MODULE_BILLIGER_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br>(Verzeichnis export/)');
define('MODULE_BILLIGER_STATUS_DESC', 'Modulstatus');
define('MODULE_BILLIGER_STATUS_TITLE', 'Status');
define('MODULE_BILLIGER_CURRENCY_TITLE', 'W&auml;hrung');
define('MODULE_BILLIGER_CURRENCY_DESC', 'Welche W&auml;hrung soll exportiert werden?');
define('EXPORT_YES', 'Nur Herunterladen');
define('EXPORT_NO', 'Am Server Speichern');
define('CURRENCY', '<hr /><b>W&auml;hrung:</b>');
define('CURRENCY_DESC', 'W&auml;hrung in der Exportdatei');
define('LANGUAGE', '<hr /><b>Sprache:</b>');
define('LANGUAGE_DESC', 'Sprache in der Exportdatei');
define('MODULE_BILLIGER_SHIPPING_COST_TITLE', '<hr /><b>Versandkosten:</b>');
define('MODULE_BILLIGER_SHIPPING_COST_DESC', 'Die Versandkosten basieren auf Preis oder Gewicht des Artikels. Beispiel: 25:5.50,50:8.50,etc.. <b>Bis</b> 25 werden 5.50 verrechnet, <b>bis</b> 50 werden 8.50 verrechnet, etc. F&uuml;r alles dar&uuml;ber werden keine Versandkosten berechnet!<br /><br />Die Versandkosten sind als Brutto anzugeben.<br />Es gilt die unten eingestellte W&auml;hrung.');
define('MODULE_BILLIGER_SHIPPING_METHOD_TITLE', '<b>Versandkosten Methode</b>');
define('MODULE_BILLIGER_SHIPPING_METHOD_DESC', 'Die Versandkosten basieren auf Preis oder Gewicht des Artikels.');
define('EXPORT', 'Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('EXPORT_TYPE', '<hr /><b>Speicherart:</b>');
define('EXPORT_STATUS_TYPE', '<hr /><b>Kundengruppe:</b>');
define('EXPORT_STATUS', 'Bitte w&auml;hlen Sie die Kundengruppe, die Basis f&uuml;r den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>):</b>');
define('CAMPAIGNS', '<hr /><b>Kampagnen:</b>');
define('CAMPAIGNS_DESC', 'Mit Kampagne zur Nachverfolgung verbinden.');
define('DATE_FORMAT_EXPORT', '%d.%m.%Y'); // this is used for strftime()

class billiger {
    var $code;
    var $title;
    var $description;
    var $enabled;

    function billiger()
    {
        global $order;

        $this->code = 'billiger';
        $this->title = MODULE_BILLIGER_TEXT_TITLE;
        $this->description = MODULE_BILLIGER_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_BILLIGER_SORT_ORDER;
        $this->enabled = ((MODULE_BILLIGER_STATUS == 'True') ? true : false);
        $this->CAT = array();
        $this->PARENT = array();
    }

    function process($file)
    {
        @xtc_set_time_limit(0);

        $file = $_POST['configuration']['MODULE_BILLIGER_FILE'];

        #$config_query = xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET configuration_value = ".$_POST['configuration']['MODULE_BILLIGER_SHIPPING_COST']." WHERE configuration_key = 'MODULE_BILLIGER_SHIPPING_METHOD'");

        require(DIR_FS_CATALOG . DIR_WS_CLASSES . 'xtcPrice.php');
        $xtPrice = new xtcPrice($_POST['currencies'], $_POST['status']);
        // query
        //BOF - DokuMan - 2011-06-24 - fix sql query (thx to franky_n)
        /*
        $export_query = xtc_db_query("SELECT
                             p.products_id,
                             p.products_model,
                             p.products_ean,
                             p.products_image,
                             p.products_price,
                             p.products_status,
                             p.products_date_available,
                             p.products_shippingtime,
                             p.products_discount_allowed,
                             p.products_tax_class_id,
                             p.products_date_added,
                             p.products_weight,
                             pd.products_name,
                             pd.products_description,
                             pd.products_short_description,
                             pd.products_meta_keywords,
                             m.manufacturers_name,
                             lng.code
                         FROM
                             " . TABLE_PRODUCTS . " p LEFT JOIN
                             " . TABLE_MANUFACTURERS . " m
                           ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
                             " . TABLE_PRODUCTS_DESCRIPTION . " pd
                           ON p.products_id = pd.products_id LEFT JOIN
                             " . TABLE_SPECIALS . " s
                           ON p.products_id = s.products_id LEFT JOIN
                             " . TABLE_LANGUAGES . " as lng
                           ON lng.languages_id = '" . (int) $_POST['languages_id'] . "'
                         WHERE
                           p.products_status = 1 AND
                           lng.languages_id = pd.language_id
                         ORDER BY
                            p.products_date_added DESC,
                            pd.products_name");
            */
            $export_query =xtc_db_query("SELECT
                     p.products_id,
                     pd.products_name,
                     pd.products_description,
                     pd.products_short_description,
                     p.products_model,
                     p.products_ean,
                     p.products_image,
                     p.products_price,
                     p.products_status,
                     p.products_date_available,
                     p.products_shippingtime,
                     p.products_discount_allowed,
                     pd.products_meta_keywords,
                     p.products_tax_class_id,
                     p.products_date_added,
                     p.products_weight,
                     m.manufacturers_name
                 FROM
                     " . TABLE_PRODUCTS . " p LEFT JOIN
                     " . TABLE_MANUFACTURERS . " m
                   ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
                     " . TABLE_PRODUCTS_DESCRIPTION . " pd
                   ON p.products_id = pd.products_id AND
                    pd.language_id = '".(int)$_SESSION['languages_id']."' LEFT JOIN
                     " . TABLE_SPECIALS . " s
                   ON p.products_id = s.products_id
                 WHERE
                   p.products_status = 1
                 ORDER BY
                    p.products_date_added DESC,
                    pd.products_name");
        //BOF - DokuMan - 2011-06-24 - fix sql query (thx to franky_n)

        // csv schema / headline
        $schema = 'id;hersteller;modell_nr;name;kategorie;beschreibung;bild_klein;bild_gross;link;lieferzeit;lieferkosten;preis;waehrung;aufbauservice;24_Std_service;EAN;ASIN;ISBN;PZN;ISMN;EPC;VIN';
        $schema .= "\n";
        // parse data
        while ($products = xtc_db_fetch_array($export_query)) {
            $id = $products['products_id'];
            $hersteller = $products['manufacturers_name'];
            $modell_nr = '';
            $name = $this->cleanVars($products['products_name']);
            $kategorie = $this->buildCAT($this->getCategoriesID($products['products_id']));
            $beschreibung = substr($this->cleanVars($products['products_short_description']), 0, 255);
            $bild_klein = ($products['products_image'] != '') ? HTTP_CATALOG_SERVER . DIR_WS_CATALOG_THUMBNAIL_IMAGES . $products['products_image'] : '';
            $bild_gross = ($products['products_image'] != '') ? HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES . $products['products_image'] : '';
            $lang_param = ( ($products['code'] != DEFAULT_LANGUAGE) ? '&language='.$products['code'] : '' );
            $link = xtc_catalog_href_link('product_info.php', xtc_product_link($products['products_id'], $products['products_name']).(!empty($_POST['campaign']) ? '&'.$_POST['campaign'] : ''));
            $lieferzeit = $this->getShippingtimeName($products['products_shippingtime']);
            $lieferkosten = number_format($this->getShippingCost($products['products_price'], $products['products_weight']), 2, ',', '');
            $preis = $xtPrice->xtcGetPrice($products['products_id'], $format = false, 1, $products['products_tax_class_id'], '');
            $waehrung = $_POST['currencies'];
            $aufbauservice = '';
            $x24_Std_service = '';
            $EAN = $products['products_ean'];
            $ASIN = '';
            $ISBN = '';
            $PZN = '';
            $ISMN = '';
            $EPC = '';
            $VIN = '';
            // add line
            $schema .= $id . ";" . // id
            $hersteller . ";" . // hersteller
            $modell_nr . ";" . // modell_nr
            $name . ";" . // name
            substr($kategorie, 0, strlen($kategorie)-2) . ";" . // kategorie
            $beschreibung . ";" . // beschreibung
            $bild_klein . ";" . // bild_klein
            $bild_gross . ";" . // bild_gross
            $link . ";" . // link;
            $lieferzeit . ";" . // lieferzeit
            $lieferkosten . ";" . // lieferkosten
            number_format($preis, 2, ',', '') . ";" . // preis
            $waehrung . ";" . // waehrung
            $aufbauservice . ";" . // aufbauservice - nicht unterstützt von xtcommerce 3.0.4 SP2.1
            $x24_Std_service . ";" . // 24_Std_service - nicht unterstützt von xtcommerce 3.0.4 SP2.1
            $EAN . ";" . // EAN
            $ASIN . ";" . // ASIN
            $ISBN . ";" . // ISBN
            $PZN . ";" . // PZN
            $ISMN . ";" . // ISMN
            $EPC . ";" . // EPC
            $VIN . "" . // VIN (letzter Wert KEIN TRENNZEICHEN!)
            "\n";
        }

        $filename = DIR_FS_DOCUMENT_ROOT . 'export/' . $file;
        if($_POST['export'] == 'yes') { $filename = $filename.'.tmp_'.time(); }
        // create File
        $fp = fopen( $filename, "w+");
        fputs($fp, $schema);
        fclose($fp);
        // send File to Browser

        switch ($_POST['export']) {
            case 'yes':
                header('Content-type: application/x-octet-stream');
                header('Content-disposition: attachment; filename=' . $file);
                readfile ( $filename );
                unlink( $filename );
                exit;
            break;
    }
  }
    // helper
    function buildCAT($catID)
    {
        if (isset($this->CAT[$catID])) {
            return $this->CAT[$catID];
        } else {
            $cat = array();
            $tmpID = $catID;

            while ($this->getParent($catID) != 0 || $catID != 0) {
                $cat_select = xtc_db_query("SELECT categories_name FROM " . TABLE_CATEGORIES_DESCRIPTION . " WHERE categories_id='" . $catID . "' and language_id='" . $_POST['languages_id'] . "'");
                $cat_data = xtc_db_fetch_array($cat_select);
                $catID = $this->getParent($catID);
                $cat[] = $cat_data['categories_name'];
            }
            $catStr = '';
            for ($i = count($cat);$i > 0;$i--) {
                $catStr .= $cat[$i-1] . ' > ';
            }
            $this->CAT[$tmpID] = $catStr;
            return $this->CAT[$tmpID];
        }
    }
    // helper
    function getParent($catID)
    {
        if (isset($this->PARENT[$catID])) {
            return $this->PARENT[$catID];
        } else {
            $parent_query = xtc_db_query("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . $catID . "'");
            $parent_data = xtc_db_fetch_array($parent_query);
            $this->PARENT[$catID] = $parent_data['parent_id'];
            return $parent_data['parent_id'];
        }
    }
    // helper
    function getCategoriesID($pID)
    {
        $categorie_query = xtc_db_query("SELECT
                                          categories_id
                                        FROM
                      " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                        WHERE
                      products_id='" . $pID . "'");
        while ($categorie_data = xtc_db_fetch_array($categorie_query)) {
            $categories = $categorie_data['categories_id'];
        }
        return $categories;
    }
    // helper
    function getShippingtimeName($sID)
    {
        $query = xtc_db_query("SELECT shipping_status_name FROM " . TABLE_SHIPPING_STATUS . " WHERE shipping_status_id='" . $sID . "' AND language_id='" . $_POST['languages_id'] . "'");
        $data = xtc_db_fetch_array($query);
        $this->SHIPPINGTIMENAME = $data['shipping_status_name'];
        return $data['shipping_status_name'];
    }
    // helper
    function getShippingCost($pPrice, $pWeight)
    {
        $shipping_cost_array = explode(',', $_POST['configuration']['MODULE_BILLIGER_SHIPPING_COST']);
        rsort($shipping_cost_array);

        for($i = 0;$i < count($shipping_cost_array);$i++) {
            $shipping_cost_values[$i] = explode(':', $shipping_cost_array[$i]);
        }

        for($i = 0;$i < count($shipping_cost_values);$i++) {
            switch ($_POST['configuration']['MODULE_BILLIGER_SHIPPING_METHOD']) {
                case 'price':
                    if ($pPrice < $shipping_cost_values[$i][0]) {
                        $return = $shipping_cost_values[$i][1];
                    }
                    break;
                case 'weight':
                    if ($pWeight < $shipping_cost_values[$i][0]) {
                        $return = $shipping_cost_values[$i][1];
                    }
                    break;
                default: ;
            } // switch
        }
        return $return;
    }
    // helper
    function cleanVars($string)
    {
        $string = strip_tags($string);
        $string = html_entity_decode($string);
        $string = str_replace("<br>", " ", $string);
        $string = str_replace("<br />", " ", $string);
        $string = str_replace("<br/>", " ", $string);
        $string = str_replace(";", ", ", $string);
        $string = str_replace("'", ", ", $string);
        $string = str_replace("\n", " ", $string);
        $string = str_replace("\r", " ", $string);
        $string = str_replace("\t", " ", $string);
        $string = str_replace("\v", " ", $string);
        $string = str_replace("&quot,", " \"", $string);
        $string = str_replace("&qout,", " \"", $string);
        $string = str_replace(chr(13), " ", $string);

        return $string;
    }
    // display
    function display()
    {
        /* Auswahl Kundengruppe vorbeiten */
        $customers_statuses_array = xtc_get_customers_statuses();

        /* Auswahl Währung vorbereiten */
        $curr = '';
        $currencies = xtc_db_query("SELECT code FROM " . TABLE_CURRENCIES . " ORDER BY currencies_id DESC");
        while ($currencies_data = xtc_db_fetch_array($currencies)) {
            $curr .= xtc_draw_radio_field('currencies', $currencies_data['code'], true) . $currencies_data['code'] . '<br>';
        }

        /* Auswahl Sprachen vorbereiten (ich)*/
        $lang = '';
        $languages = xtc_db_query("SELECT languages_id, name FROM " . TABLE_LANGUAGES . " ORDER BY sort_order ASC");
        while ($languages_data = xtc_db_fetch_array($languages)) {
            $lang .= xtc_draw_radio_field('languages_id', $languages_data['languages_id'], true) . $languages_data['name'] . '<br>';
        }
        /* Auswahl Kampagnen vorbereiten */
        $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
        $campaign_query = xtc_db_query("select campaigns_name, campaigns_refID from " . TABLE_CAMPAIGNS . " order by campaigns_id");
        while ($campaign = xtc_db_fetch_array($campaign_query)) {
            $campaign_array[] = array ('id' => 'refID=' . $campaign['campaigns_refID'] . '&', 'text' => $campaign['campaigns_name'],);
        }

        /* Ausgabe */
        return array('text' =>
            EXPORT_STATUS_TYPE . '<br>' .
            EXPORT_STATUS . '<br>' .
            xtc_draw_pull_down_menu('status', $customers_statuses_array, '1') . '<br>' .
            LANGUAGE . '<br>' .
            LANGUAGE_DESC . '<br>' . $lang .
            CURRENCY . '<br>' .
            CURRENCY_DESC . '<br>' . $curr .
            CAMPAIGNS . '<br>' .
            CAMPAIGNS_DESC . '<br>' .
            xtc_draw_pull_down_menu('campaign', $campaign_array) . '<br>' .
            EXPORT_TYPE . '<br>' .
            EXPORT . '<br>' .
            xtc_draw_radio_field('export', 'no', false) . EXPORT_NO . '<br>' .
            xtc_draw_radio_field('export', 'yes', true) . EXPORT_YES . '<br>' . '<br>' . xtc_button(BUTTON_EXPORT) .
            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=billiger')) . '');
    }
    // check
    function check()
    {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_BILLIGER_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }
    // install
    function install()
    {
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_BILLIGER_FILE', 'billiger.csv',  '6', '1', '', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_BILLIGER_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_BILLIGER_SHIPPING_METHOD', 'price',  '6', '1', 'xtc_cfg_select_option(array(\'price\', \'weight (kg)\'), ', now())");
        xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_BILLIGER_SHIPPING_COST', '25:5.50,50:8.50',  '6', '1', '', now())");
    }
    // remove
    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }
    // keys
    function keys()
    {
        return array('MODULE_BILLIGER_STATUS', 'MODULE_BILLIGER_FILE', 'MODULE_BILLIGER_SHIPPING_METHOD', 'MODULE_BILLIGER_SHIPPING_COST');
    }

}
?>