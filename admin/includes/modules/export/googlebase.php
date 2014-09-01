<?php
/* -----------------------------------------------------------------------------------------
   $Id: googlebase.php 4280 2013-01-12 12:14:07Z Tomcraft1980 $

  modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2005 (froogle.php, v 1188 2005/08/28); matthias - www.xt-commerce.com
   (c) 2006 xt-commerce; www.xt-commerce.com
   -----------------------------------------------------------------------------------------
   Erweiterung der googlebase.php (c)2009 by Hetfield - http://www.MerZ-IT-SerVice.de um folgende Funktionen:
   - Gewichts- oder preisabhängige Vesandkosten mit Berücksichtigung der Versandkostenfrei-Grenze
   - Beachtung des Mindermengenzuschlags
   - Zustand 'neu' fest hinterlegt
   - Anzeige Zahlungsarten
   - Anzeige Gewicht
   - Anzeige EAN
   - Auswahl der verschiedenen suchmaschinenfreundlichen URL für den Exportlink (Original/keine, Shopstat oder DirectURL)
   - Umlautproblematik und str_replace-Wahnsinn beseitigt

   updated version by franky_n

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_GOOGLEBASE_TEXT_TITLE', 'Google Base - TXT / XML<br/>Exportmodul f&uuml;r Google Base / inkl. Felder "Zustand" und "Versandkosten" <b>[VERALTET - Entspricht nicht den aktuellen Spezifikationen]</b>');
define('MODULE_GOOGLEBASE_TEXT_DESCRIPTION', 'Export - Google Base (Tab getrennt)');
define('MODULE_GOOGLEBASE_FILE_TITLE' , '<hr noshade>Dateiname');
define('MODULE_GOOGLEBASE_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportdatei am Server gespeichert werden soll.<br />(Verzeichnis export/)');
define('MODULE_GOOGLEBASE_STATUS_TITLE','Status');
define('MODULE_GOOGLEBASE_STATUS_DESC','Modulstatus');
define('MODULE_GOOGLEBASE_CURRENCY_TITLE','W&auml;hrung');
define('MODULE_GOOGLEBASE_CURRENCY_DESC','Welche W&auml;hrung soll exportiert werden?');
define('MODULE_GOOGLEBASE_SHIPPING_COST_TITLE','<hr noshade><b>Versandkosten</b>');
define('MODULE_GOOGLEBASE_SHIPPING_COST_DESC','Die Versandkosten basieren auf dem Artikelpreis oder dem Artikelgewicht. Beispiel: 25:4.90,50:9.90,etc.. Bis 25 werden 4.90 verrechnet, dar&uuml;ber bis 50 werden 9.90 verrechnet, etc.');
define('MODULE_GOOGLEBASE_SHIPPING_ART_TITLE','<hr noshade><b>Versandkosten-Methode</b>');
define('MODULE_GOOGLEBASE_SHIPPING_ART_DESC','Die Versandkosten basieren auf dem Artikelpreis oder dem Artikelgewicht.');
define('MODULE_GOOGLEBASE_SUMAURL_TITLE','<hr noshade><b>Suchmaschinenfreundliche URL</b>');
define('MODULE_GOOGLEBASE_SUMAURL_DESC','W&auml;hlen Sie aus, ob und welche Erweiterung Sie f&uuml;r suchmaschinenfreundliche URL in Ihrem Shop nutzen');
define('MODULE_GOOGLEBASE_FORMAT_TITLE','Exportformat');
define('MODULE_GOOGLEBASE_FORMAT_DESC','Welches Format soll exportiert werden?');
define('EXPORT_YES','Nur Herunterladen');
define('EXPORT_NO','Am Server Speichern');
define('CURRENCY','<hr noshade><b>W&auml;hrung:</b>');
define('CURRENCY_DESC','W&auml;hrung in der Exportdatei');
define('EXPORT','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen. Dieser kann einige Minuten in Anspruch nehmen.');
define('EXPORT_TYPE','<hr noshade><b>Speicherart:</b>');
define('EXPORT_STATUS_TYPE','<hr noshade><b>Kundengruppe:</b>');
define('EXPORT_STATUS','Bitte w&auml;hlen Sie die Kundengruppe, die Basis f&uuml;r den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>):</b>');
define('CAMPAIGNS','<hr noshade><b>Kampagnen:</b>');
define('CAMPAIGNS_DESC','Mit Kampagne zur Nachverfolgung verbinden.');
define('SHIPPING_COUNTRY','<hr noshade><b>Land (optional):</b>');
define('SHIPPING_COUNTRY_DESC','Das Land, in das der Artikel geliefert wird. Bitte geben Sie DE als Wert an. Google Base akzeptiert nur Versandkosten f&uuml;r Lieferungen innerhalb Deutschlands.<br />Hinweis: Falls kein Wert angegeben wird, nimmt Google Base an, dass sich die Versandkosten auf das Zielland des Artikels beziehen.');
define('DATE_FORMAT_EXPORT', '%d.%m.%Y');  // this is used for strftime()

// include needed functions


class googlebase {
  var $code, $title, $description, $enabled;

  function googlebase() {
    global $order;

    $this->code = 'googlebase';
    $this->title = MODULE_GOOGLEBASE_TEXT_TITLE;
    $this->description = MODULE_GOOGLEBASE_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_GOOGLEBASE_SORT_ORDER;
    $this->enabled = ((MODULE_GOOGLEBASE_STATUS == 'True') ? true : false);
    $this->CAT=array();
    $this->PARENT=array();
  }

  function process($file = MODULE_GOOGLEBASE_FILE) {
    // Read Modules
    $module_type = 'payment';
    $module_directory = DIR_FS_CATALOG_MODULES . 'payment/';
    $module_file_extension = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '.'));
    $directory_array = array();
    if ($dir = @dir($module_directory)) {
      while ($module_file = $dir->read()) {
        if (!is_dir($module_directory . $module_file)) {
          if (substr($module_file, strrpos($module_file, '.')) == $module_file_extension) {
            $directory_array[] = $module_file;
          }
        }
      }
      sort($directory_array);
      $dir->close();
    }

    $installed_modules = array();
    $module_info = array();
    for ($i = 0, $n = sizeof($directory_array); $i < $n; $i++) {
      $module_file = $directory_array[$i];

      include(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $module_file);
      include($module_directory . $module_file);

      $class = substr($module_file, 0, strrpos($module_file, '.'));
      if (xtc_class_exists($class)) {
        $module = new $class();
        $module_info[] = array($module->code => $module->title);
      }
    }
    @xtc_set_time_limit(0);
    require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
    $xtPrice = new xtcPrice($_POST['currencies'],$_POST['status']);

    if ($_POST['sumaurl'] == 'directurl') {
      require_once(DIR_FS_CATALOG.'inc/bluegate_seo.inc.php');
      $bluegateSeo = new BluegateSeo();
    }

    $schema_txt_de = "beschreibung".chr(9)."id".chr(9)."link".chr(9)."preis".chr(9)."w".chr(228)."hrung".chr(9)."titel".chr(9)."zustand".chr(9)."bild_url".chr(9)."ean".chr(9)."gewicht".chr(9)."marke".chr(9)."versand".chr(9)."zahlungsmethode".chr(9)."zahlungsrichtlinien".chr(13);

    $schema_xml_de = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
                     '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n".
                     "\t".'<channel>'."\n".
                     "\t"."\t".'<title>'.encode_htmlspecialchars(TITLE).'</title>'."\n".
                     "\t"."\t".'<description>'.META_DESCRIPTION.'</description>'."\n".
                     "\t"."\t".'<link>'.HTTP_SERVER.'</link>'."\n";

    if ($_POST['shippingcosts'] != MODULE_GOOGLEBASE_SHIPPING_COST) {
      xtc_db_query("update " . TABLE_CONFIGURATION . " set configuration_value = '" . xtc_db_input($_POST['shippingcosts']) . "' where configuration_key = 'MODULE_GOOGLEBASE_SHIPPING_COST'");
    }
    $zahlungsmethode = '';
    if (defined('MODULE_PAYMENT_INSTALLED') && xtc_not_null(MODULE_PAYMENT_INSTALLED)) {
      $other_payments = '';
      $creditcard_modules = array('cc', 'moneybookers_cc', 'amoneybookers', 'worldpay', 'ipayment', 'iclear', 'paymentpartner_cc', 'wire_card_c3');
      $americanexpress_modules = array('cc', 'moneybookers_cc', 'amoneybookers', 'ipayment');
      $lastschrift_modules = array('banktransfer', 'ipaymentelv', 'paymentpartner_dd');
      $ueberweisung_modules = array('moneyorder', 'sofortueberweisungvorkasse', 'eustandardtransfer');
      $cash_modules = array('cash');
      $scheck_modules = array('moneyorder');
      $customers_status_query = xtc_db_query("SELECT customers_status_payment_unallowed FROM " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . (int)$_POST['status'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
      $customers_status_value = xtc_db_fetch_array($customers_status_query);
      $installedpayments = explode(';', MODULE_PAYMENT_INSTALLED);
      $unallowed_payment_modules = explode(',', $customers_status_value['customers_status_payment_unallowed']);
      for ($i = 0, $n = sizeof($installedpayments); $i < $n; $i++) {
        $installedpayments[$i] = str_replace('.php','',$installedpayments[$i]);
        if (!in_array($installedpayments[$i], $unallowed_payment_modules)) {
          if (in_array($installedpayments[$i], $creditcard_modules)) { $cc = true; } else
          if (in_array($installedpayments[$i], $americanexpress_modules)) { $ae = true; } else
          if (in_array($installedpayments[$i], $lastschrift_modules)) { $la = true; } else
          if (in_array($installedpayments[$i], $ueberweisung_modules)) { $uw = true; } else
          if (in_array($installedpayments[$i], $cash_modules)) { $ca = true; } else
          if (in_array($installedpayments[$i], $scheck_modules)) { $sc = true; } else {
            $number_module_info = (count($module_info)-1);
            foreach ($module_info as $module_key) {
              foreach ($module_key as $module_attr => $module_desc) {
                if ($installedpayments[$i] == $module_attr) {
                  if ($number_module_info != $module_key) {
                    $other_payments .= $module_desc .', ';
                  }
                }
              }
            }
          }
        }
      }
      if ($cc == true) { $creditcard = 'Visa,MasterCard,'; } else { $creditcard = ''; }
      if ($ae == true) { $americanexpress = 'AmericanExpress,'; } else { $americanexpress = ''; }
      if ($la == true) { $lastschrift = 'Lastschrift,'; } else { $lastschrift = ''; }
      if ($uw == true) { $ueberweisung = chr(220).'berweisung,'; } else { $ueberweisung = ''; }
      if ($ca == true) { $cash = 'Barzahlung,'; } else { $cash = ''; }
      if ($sc == true) { $scheck = 'Scheck'; } else { $scheck = ''; }
      $zahlungsmethode = $creditcard.$americanexpress.$lastschrift.$ueberweisung.$cash.$scheck;
      if (substr($zahlungsmethode, -1) == ',') { $zahlungsmethode = substr($zahlungsmethode, 0, -1); }
      if (substr($other_payments, -2) == ', ') { $other_payments = substr($other_payments, 0, -2); }
      $zahlungsrichtlinie = 'Wir unterst&uuml;tzen neben den Zahlungsarten '.$zahlungsmethode.' auch noch folgende Zahlungsarten '.$other_payments;
    }

    $export_query = xtc_db_query("SELECT
                                         p.products_id,
                                         pd.products_name,
                                         pd.products_description,
                                         p.products_model,
                                         p.products_ean,
                                         p.products_image,
                                         p.products_price,
                                         p.products_weight,
                                         p.products_tax_class_id,
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
      $products_price = $xtPrice->xtcGetPrice($products['products_id'], $format=false, 1, $products['products_tax_class_id'], '');
      $categorie_query=xtc_db_query("SELECT
                                            categories_id
                                            FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                            WHERE products_id='".$products['products_id']."'");
      while ($categorie_data=xtc_db_fetch_array($categorie_query)) {
        $categories=$categorie_data['categories_id'];
      }

      // remove trash
      $products_description = strip_tags($products['products_description']);
      $products_description = html_entity_decode($products_description);
      $products_description = str_replace(";",", ",$products_description);
      $products_description = str_replace("'",", ",$products_description);
      $products_description = str_replace("\n"," ",$products_description);
      $products_description = str_replace("\r"," ",$products_description);
      $products_description = str_replace("\t"," ",$products_description);
      $products_description = str_replace("\v"," ",$products_description);
      $products_description = str_replace(chr(13)," ",$products_description);
      $products_description = substr($products_description, 0, 65536);
      $products_name = strip_tags($products['products_name']);
      $products_name = html_entity_decode($products_name);
      $products_name = str_replace(";",", ",$products_name);
      $products_name = str_replace("'",", ",$products_name);
      $products_name = str_replace("\n"," ",$products_name);
      $products_name = str_replace("\r"," ",$products_name);
      $products_name = str_replace("\t"," ",$products_name);
      $products_name = str_replace("\v"," ",$products_name);
      $products_name = str_replace(chr(13)," ",$products_name);
      $cat = $this->buildCAT($categories);

      if ($products['products_image'] != ''){
        $image = HTTP_CATALOG_SERVER . DIR_WS_CATALOG_ORIGINAL_IMAGES .$products['products_image'];
      } else {
        $image = '';
      }
      if ($products['products_weight'] != '0.00'){
        $weight = number_format($products['products_weight'],2,'.','');
      } else {
        $weight = '';
      }
      $versand = '0.00';
      if ($products_price < MODULE_ORDER_TOTAL_LOWORDERFEE_ORDER_UNDER && MODULE_ORDER_TOTAL_LOWORDERFEE_STATUS == 'true') {
        if (MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION == 'national' or MODULE_ORDER_TOTAL_LOWORDERFEE_DESTINATION == 'both') {
          $customers_tax_query = xtc_db_query("SELECT customers_status_show_price_tax, customers_status_add_tax_ot FROM " . TABLE_CUSTOMERS_STATUS . " WHERE customers_status_id = '" . (int)$_POST['status'] . "' AND language_id = '" . (int)$_SESSION['languages_id'] . "'");
          $customers_tax_value = xtc_db_fetch_array($customers_tax_query);
          $tax = xtc_get_tax_rate(MODULE_ORDER_TOTAL_LOWORDERFEE_TAX_CLASS);
          if ($customers_tax_value['customers_status_show_price_tax'] == 1) {
            $low_order_fee = xtc_add_tax(MODULE_ORDER_TOTAL_LOWORDERFEE_FEE, $tax);
          }
          if ($customers_tax_value['customers_status_show_price_tax'] == 0 && $customers_tax_value['customers_status_add_tax_ot'] == 1) {
            $low_order_fee = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
          }
          if ($customers_tax_value['customers_status_show_price_tax'] == 0 && $customers_tax_value['customers_status_add_tax_ot'] != 1) {
            $low_order_fee = MODULE_ORDER_TOTAL_LOWORDERFEE_FEE;
          }
          $versand = $versand + $low_order_fee;
        }
      }
      if ($products_price > MODULE_SHIPPING_FREEAMOUNT_AMOUNT && MODULE_SHIPPING_FREEAMOUNT_STATUS == 'True') {
        $versand = $versand;
      } else if ($products_price > MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
        $versand = $versand;
      } else {
        $shipping = -1;
        $shippinglist = preg_split("/[:,]/" , $_POST['shippingcosts']); // Hetfield - 2009-08-18 - replaced deprecated function split with preg_split to be ready for PHP >= 5.3
        for ($i=0; $i<sizeof($shippinglist); $i+=2) {
          if ($_POST['shippingart'] == 'weight') {
            if ($products['products_weight'] <= $shippinglist[$i]) {
              $shipping = $shippinglist[$i+1];
              break;
            }
          } else if ($_POST['shippingart'] == 'price') {
            if ($products_price <= $shippinglist[$i]) {
              $shipping = $shippinglist[$i+1];
              break;
            }
          }
        }
        if ($shipping == -1) {
          $shipping_cost = 0;
        } else {
          $shipping_cost = $shipping;
        }
        $versand = $versand + $shipping_cost;
      }
      if ($_POST['sumaurl'] == 'shopstat') {
        $cat = strip_tags($this->buildCAT($categories));
        require_once(DIR_FS_INC . 'xtc_href_link_from_admin.inc.php');
        $productURL = xtc_href_link_from_admin('product_info.php', xtc_product_link($products['products_id'], $products['products_name']), 'NONSSL', false);
        if (!empty($_POST['campaign'])) {
          $productURL .= '?'.$_POST['campaign'];
        }
      } else if ($_POST['sumaurl'] == 'directurl') {
        $productURL = $bluegateSeo->getProductLink(xtc_product_link($products['products_id'], $products['products_name']),$connection,$_SESSION['languages_id']);
        if ($_POST['campaign']<>'') {
          $productURL .= '?'.$_POST['campaign'];
        }
      } else if ($_POST['sumaurl'] == 'original') {
        $productURL = HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?'.xtc_product_link($products['products_id'], $products['products_name']);
        if (!empty($_POST['campaign'])) {
          $productURL .= '&'.$_POST['campaign'];
        }
      }

      //create content
      $schema_txt_de .= $products_description."\t".
                        $products['products_id']."\t".
                        $productURL . "\t" .
                        number_format($products_price,2,'.','')."\t".
                        $_POST['currencies']."\t".
                        $products_name."\t".
                        "neu\t".
                        $image."\t" .
                        $products['products_ean']."\t".
                        $weight."\t".
                        $products['manufacturers_name']."\t".
                        $_POST['shipping_country'].":::".number_format($versand,2,'.','')."\t" .
                        $zahlungsmethode."\t".
                        $zahlungsrichtlinie."\n";

      $schema_xml_de .= "\t"."\t".'<item>'."\n".
                        "\t"."\t"."\t".'<beschreibung>'.$products_description.'</beschreibung>'."\n".
                        "\t"."\t"."\t".'<g:id>'.$products['products_id'].'</g:id>'."\n".
                        "\t"."\t"."\t".'<link>'.str_replace('&', '&amp;', $productURL).'</link>'."\n".
                        "\t"."\t"."\t".'<g:preis>'.number_format($products_price,2,'.','').'</g:preis>'."\n".
                        "\t"."\t"."\t".'<g:währung>'.$_POST['currencies'].'</g:währung>'."\n".
                        "\t"."\t"."\t".'<titel>'.$products_name.'</titel>'."\n".
                        "\t"."\t"."\t".'<g:zustand>'.'neu'.'</g:zustand>'."\n".
                        "\t"."\t"."\t".'<g:bild_url>'.$image.'</g:bild_url>'."\n" .
                        "\t"."\t"."\t".'<g:ean>'.$products['products_ean'].'</g:ean>'."\n".
                        "\t"."\t"."\t".'<g:gewicht>'.$weight.'</g:gewicht>'."\n".
                        "\t"."\t"."\t".'<g:marke>'.$products['manufacturers_name'].'</g:marke>'."\n".
                        "\t"."\t"."\t".'<g:versand>'.$_POST['shipping_country'].':::'.number_format($versand,2,'.','').'</g:versand>'."\n" .
                        "\t"."\t"."\t".'<g:zahlungsmethode>'.$zahlungsmethode.'</g:zahlungsmethode>'."\n" .
                        "\t"."\t"."\t".'<g:payment_notes>'.$zahlungsrichtlinie.'</g:payment_notes>'."\n".
                        "\t"."\t".'</item>'."\n";
    }
    $schema_xml_de .= "\t".'</channel>'."\n".
                      '</rss>'."\n";

    // create File
    $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file, "w+");

    if (MODULE_GOOGLEBASE_FORMAT == 'TXT') {
      fputs($fp,$schema_txt_de);
    } else {
      fputs($fp,$schema_xml_de);
    }
    fclose($fp);

    switch ($_POST['export']) {
      case 'yes':
        // send File to Browser
        $extension = substr($file, -3);
        $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file,"rb");
        $buffer = fread($fp, filesize(DIR_FS_DOCUMENT_ROOT.'export/' . $file));
        fclose($fp);
        header('Content-type: application/x-octet-stream; charset=iso-8859-15');
        header('Content-disposition: attachment; filename=' . $file);
        echo $buffer;
        exit;

        break;
    }

  }

  function buildCAT($catID) {
    if (isset($this->CAT[$catID])) {
      return  $this->CAT[$catID];
    } else {
      $cat=array();
      $tmpID=$catID;
      while ($this->getParent($catID)!=0 || $catID!=0) {
        $cat_select=xtc_db_query("SELECT categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id='".$catID."' and language_id='".$_SESSION['languages_id']."'");
        $cat_data=xtc_db_fetch_array($cat_select);
        $catID=$this->getParent($catID);
        $cat[]=$cat_data['categories_name'];
      }
      $catStr='';
      for ($i=count($cat);$i>0;$i--) {
        $catStr.=$cat[$i-1].' > ';
      }
      $this->CAT[$tmpID]=$catStr;
      return $this->CAT[$tmpID];
    }
  }

  function getParent($catID) {
    if (isset($this->PARENT[$catID])) {
      return $this->PARENT[$catID];
    } else {
      $parent_query=xtc_db_query("SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id='".$catID."'");
      $parent_data=xtc_db_fetch_array($parent_query);
      $this->PARENT[$catID]=$parent_data['parent_id'];
      return  $parent_data['parent_id'];
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
    $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
    $campaign_query = xtc_db_query("select campaigns_name, campaigns_refID from ".TABLE_CAMPAIGNS." order by campaigns_id");
    while ($campaign = xtc_db_fetch_array($campaign_query)) {
      $campaign_array[] = array ('id' => 'refID='.$campaign['campaigns_refID'], 'text' => $campaign['campaigns_name'],);
    }
    $shipping_country_array = array(array('id' => '', 'text' => TEXT_NONE));
    $shipping_country_query = xtc_db_query("SELECT countries_iso_code_2 FROM ".TABLE_COUNTRIES." ORDER BY countries_iso_code_2");
    while ($shipping_country = xtc_db_fetch_array($shipping_country_query)) {
      $shipping_country_array[] = array('id' => strtoupper($shipping_country['countries_iso_code_2']), 'text' => strtoupper($shipping_country['countries_iso_code_2']),);
    }
    return array('text' =>  EXPORT_STATUS_TYPE.'<br />'.
                            EXPORT_STATUS.'<br />'.
                            xtc_draw_pull_down_menu('status',$customers_statuses_array, '1').'<br />'.
                            CURRENCY.'<br />'.
                            CURRENCY_DESC.'<br />'.
                            $curr.
                            '<b>'.MODULE_GOOGLEBASE_SHIPPING_COST_TITLE.'</b><br />'.
                            MODULE_GOOGLEBASE_SHIPPING_COST_DESC.'<br />'.
                            xtc_draw_input_field('shippingcosts',MODULE_GOOGLEBASE_SHIPPING_COST).'<br />'.
                            '<b>'.MODULE_GOOGLEBASE_SHIPPING_ART_TITLE.'</b><br />'.
                            MODULE_GOOGLEBASE_SHIPPING_ART_DESC.'<br />'.
                            xtc_draw_radio_field('shippingart', 'weight',true).'Versandksten nach Gewicht<br />'.
                            xtc_draw_radio_field('shippingart', 'price',false).'Versandkosten nach Preis<br />'.
                            SHIPPING_COUNTRY.'<br />'.
                            SHIPPING_COUNTRY_DESC.'<br />'.
                            xtc_draw_pull_down_menu('shipping_country',$shipping_country_array,'DE').'<br />'.
                            '<b>'.MODULE_GOOGLEBASE_SUMAURL_TITLE.'</b><br />'.
                            MODULE_GOOGLEBASE_SUMAURL_DESC.'<br />'.
                            xtc_draw_radio_field('sumaurl', 'original',true).'Originale bzw. keine<br />'.
                            xtc_draw_radio_field('sumaurl', 'shopstat',false).'Shopstat<br />'.
                            xtc_draw_radio_field('sumaurl', 'directurl',false).'DirectURL<br />'.
                            CAMPAIGNS.'<br />'.
                            CAMPAIGNS_DESC.'<br />'.
                            xtc_draw_pull_down_menu('campaign',$campaign_array).'<br />'.
                            EXPORT_TYPE.'<br />'.
                            EXPORT.'<br />'.
                            xtc_draw_radio_field('export', 'no',false).EXPORT_NO.'<br />'.
                            xtc_draw_radio_field('export', 'yes',true).EXPORT_YES.'<br />'.
                            '<br />' . xtc_button(BUTTON_EXPORT) .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=googlebase')));
  }

  function check() {
    if (!isset($this->_check)) {
      $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_GOOGLEBASE_STATUS'");
      $this->_check = xtc_db_num_rows($check_query);
    }
    return $this->_check;
  }

  function install() {
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_GOOGLEBASE_FILE', 'googlebase.txt', '6', '1', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_GOOGLEBASE_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_GOOGLEBASE_SHIPPING_COST', '25:6.90,50:9.90,10000:0.00', '6', '1', '', '', now())");
    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_GOOGLEBASE_FORMAT', 'TXT',  '6', '1', 'xtc_cfg_select_option(array(\'TXT\', \'XML\'), ', now())");
  }

  function remove() {
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_GOOGLEBASE_SHIPPING_COST'");
  }

  function keys() {
    return array('MODULE_GOOGLEBASE_STATUS','MODULE_GOOGLEBASE_FORMAT','MODULE_GOOGLEBASE_FILE');
  }
}
?>