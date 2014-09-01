<?php
/* -----------------------------------------------------------------------------------------
   $Id: wein2.php 2666 2012-02-23 11:38:17Z dokuman $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com 
   (c) 2003   nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 

   2011-01-20: Version 2.3, VPE added by Herwig Seitz

   ---------------------------------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  define('MODULE_WEIN2_TEXT_DESCRIPTION', '<script type="text/javascript" src="http://api.wein.cc/xtcdesciption.php?v=2.3"></script>');
  define('MODULE_WEIN2_TEXT_TITLE', 'Wein.cc - XML  V2.3');
  define('MODULE_WEIN2_FILE_TITLE' , 'Dateiname');
  define('MODULE_WEIN2_FILE_DESC' , 'Datei wird im Verzeichnis export/ abgelegt.');
  define('MODULE_WEIN2_STATUS_DESC','Modulstatus');
  define('MODULE_WEIN2_STATUS_TITLE','Status');
  define('MODULE_WEIN2_CURRENCY_TITLE','W&auml;hrung');
  define('MODULE_WEIN2_CURRENCY_DESC','Welche W&auml;hrung soll exportiert werden?');
  define('EXPORT_YES','Nur Herunterladen');
  define('EXPORT_NO','Am Server Speichern');
  define('CURRENCY','<b>W&auml;hrung</b>');
  define('CURRENCY_DESC','W&auml;hrung in der Exportdatei');
  define('EXPORT','Bitte den Sicherungsprozess AUF KEINEN FALL unterbrechen.');
  define('EXPORT_TYPE','<b>Speicherart</b>');
  define('EXPORT_STATUS_TYPE','<b>Kundengruppe</b>');
  define('EXPORT_STATUS','Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>');
  define('CHARSET','iso-8859-1');
  define('CAMPAIGNS','<b>Kampagne</b>');
  define('CAMPAIGNS_DESC','Mit Kampagne zur Nachverfolgung verbinden.');

  // include needed functions
  class wein2 {
    var $code, $title, $description, $enabled;


    function wein2() {
      global $order;

      $this->code = 'wein2';
      $this->title = MODULE_WEIN2_TEXT_TITLE;
      $this->description = MODULE_WEIN2_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_WEIN2_SORT_ORDER;
      $this->enabled = ((MODULE_WEIN2_STATUS == 'True') ? true : false);

    }


    function xmlescape($s){
      return str_replace(array('&','<','>','"',"'"),array('&amp;','&lt;','&gt;','&quot;','&apos;'),$s);  
    }


    function process($file) {
      @xtc_set_time_limit(0);
      require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
      $xtPrice = new xtcPrice($_POST['currencies'],$_POST['status']);

      $VPE=Array();
      $res=xtc_db_query("SELECT products_vpe_id id,products_vpe_name name FROM " . TABLE_PRODUCTS_VPE . " WHERE language_id='".$_SESSION['languages_id']."'");
      while($vpe=xtc_db_fetch_array($res))
        $VPE[$vpe[id]]=$vpe[name];
      $schema = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\n<WEINCCEXPORT xmlns=\"http://technik.wein.cc/\" version=\"".MODULE_WEIN2_TEXT_TITLE."\" currency=\"".$_POST['currencies']."\" shopversion=\"".PROJECT_VERSION."\">\n";
      $export_query =xtc_db_query("SELECT
                                          p.products_id,
                                          pd.products_name,
                                          pd.products_description,
                                          p.products_model,
                                          p.products_ean,
                                          p.products_image,
                                          p.products_shippingtime,
                                          p.products_price,
                                          p.products_status,
                                          p.products_discount_allowed,
                                          p.products_tax_class_id,
                                          p.products_date_added,
                                          m.manufacturers_name,
                                          p.products_quantity,
                                          p.products_last_modified,
                                          p.products_vpe_status,
                                          p.products_vpe,
                                          p.products_vpe_value
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
        // remove trash
        $products_description=$products['products_description'];
        $products_description=str_replace("\n"," ",$products_description);
        $products_description=str_replace("\r"," ",$products_description);
        $products_description=str_replace("\t"," ",$products_description);
        $products_description=substr($products_description,0,65536);
        $categorie_query=xtc_db_query("SELECT
                                              categories_id
                                         FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                        WHERE products_id='".$products['products_id']."'");
        while ($categorie_data=xtc_db_fetch_array($categorie_query)) {
          $categories=$categorie_data['categories_id'];
        }
        $categorie_query=xtc_db_query("SELECT
                                              categories_name
                                         FROM ".TABLE_CATEGORIES_DESCRIPTION."
                                        WHERE categories_id='".$categories."'
                                          AND language_id='".$_SESSION['languages_id']."'");
        $categorie_data=xtc_db_fetch_array($categorie_query);
        //create content
        if($products['products_image']==''){
          $wein_image="";
        } else {
          $wein_image=HTTP_CATALOG_SERVER . DIR_WS_CATALOG_ORIGINAL_IMAGES . $products['products_image'];
        }
        $tax=$xtPrice->TAX[$products['products_tax_class_id']];
        if(!$tax)
          $tax=19;
        $produkturl=HTTP_CATALOG_SERVER . DIR_WS_CATALOG .'product_info.php?'.$_POST['campaign'].xtc_product_link($products['products_id']);

        $schema.="<PRODUKT>\n".
                 '<products_id>'.$products['products_id']."</products_id>\n".
                 '<ARTIKELNUMMER>'.$products['products_model']."</ARTIKELNUMMER>\n".
                 '<PREIS>'.number_format($products_price,2,'.','')."</PREIS>\n".
                 '<PRODUKTNAME>'.$this->xmlescape($products['products_name'])."</PRODUKTNAME>\n".
                 '<PRODUZENT>'.$this->xmlescape($products['manufacturers_name']). "</PRODUZENT>\n".
                 '<KATEGORIE>'.$this->xmlescape($categorie_data['categories_name']). "</KATEGORIE>\n".
                 "<TAX>$tax</TAX>\n".
                 '<EAN>'.$products['products_ean'] . "</EAN>\n".
                 "<PRODUKTURL>".$this->xmlescape($produkturl)."</PRODUKTURL>\n".
                 "<BILDURL>".$this->xmlescape($wein_image)."</BILDURL>\n".
                 '<BESCHREIBUNG>'.$this->xmlescape($products_description). "</BESCHREIBUNG>\n".
                 '<LAGERSTAND>'.$products['products_quantity'] . "</LAGERSTAND>\n".
                 "<QUANTITY>1</QUANTITY>\n".
                 '<LASTMODIFIED>'.($products['products_last_modified']?$products['products_last_modified']:$products['products_date_added']) . "</LASTMODIFIED>\n".
                 '<DATEADDED>'.$products['products_date_added']."</DATEADDED>\n".
                 '<VPE_NAME>'.$VPE[$products['products_vpe']]."</VPE_NAME>\n".
                 '<VPE_STATUS>'.$products['products_vpe_status']."</VPE_STATUS>\n".
                 '<VPE_VALUE>'.$products['products_vpe_value']."</VPE_VALUE>\n".
                 "</PRODUKT>\n";

        $query="SELECT quantity, personal_offer*(1+$tax/100) staffelpreis 
                  FROM " . TABLE_PERSONAL_OFFERS_BY . $_POST['status']." 
                    WHERE products_id=".$products['products_id']." AND quantity>1";
        $res=xtc_db_query($query);
        while($r=xtc_db_fetch_array($res)){
          $schema.="<PRODUKT>\n".
                   '<products_id>'.$products['products_id']."</products_id>\n".
                   '<ARTIKELNUMMER>'.$products['products_model']."</ARTIKELNUMMER>\n".
                   '<PREIS>'.number_format($r[staffelpreis],2,'.','')."</PREIS>\n".
                   '<PRODUKTNAME>'.$this->xmlescape($products['products_name'])."</PRODUKTNAME>\n".
                   '<PRODUZENT>'.$this->xmlescape($products['manufacturers_name']). "</PRODUZENT>\n".
                   '<KATEGORIE>'.$this->xmlescape($categorie_data['categories_name']). "</KATEGORIE>\n".
                   "<TAX>$tax</TAX>\n".
                   '<EAN>'.$products['products_ean']."</EAN>\n".
                   "<PRODUKTURL>".$this->xmlescape($produkturl)."</PRODUKTURL>\n".
                   "<BILDURL>".$this->xmlescape($wein_image)."</BILDURL>\n".
                   '<BESCHREIBUNG>'.$this->xmlescape($products_description). "</BESCHREIBUNG>\n".
                   '<LAGERSTAND>'.$products['products_quantity'] . "</LAGERSTAND>\n".
                   "<QUANTITY>".$r[quantity]."</QUANTITY>\n".
                   '<LASTMODIFIED>'.($products['products_last_modified']?$products['products_last_modified']:$products['products_date_added']) . "</LASTMODIFIED>\n".
                   '<DATEADDED>'.$products['products_date_added'] . "</DATEADDED>\n".
                   '<VPE_NAME>'.$VPE[$products['products_vpe']]."</VPE_NAME>\n".
                   '<VPE_STATUS>'.$products['products_vpe_status']."</VPE_STATUS>\n".
                   '<VPE_VALUE>'.$products['products_vpe_value']."</VPE_VALUE>\n".
                   "</PRODUKT>\n";
        }
      }
      $schema.="</WEINCCEXPORT>\n";

      // create File
      $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file, "w+");
      fputs($fp, $schema);
      fclose($fp);

      //ping wein.cc
      $exporturl="http://".$_SERVER[HTTP_HOST].array_shift(explode('/admin/',$_SERVER[SCRIPT_NAME],2))."/export/".$file."\n";
      file("http://api.wein.cc/updateping.php?exporturl=".urlencode($exporturl)."&version=".urlencode(MODULE_WEIN2_TEXT_TITLE));

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
       $curr.=xtc_draw_radio_field('currencies', $currencies_data['code'],true).$currencies_data['code'].'<br>';
      }
      $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
      $campaign_query = xtc_db_query("select campaigns_name, campaigns_refID from ".TABLE_CAMPAIGNS." order by campaigns_id");
      while ($campaign = xtc_db_fetch_array($campaign_query)) {
        $campaign_array[] = array ('id' => 'refID='.$campaign['campaigns_refID'].'&', 'text' => $campaign['campaigns_name'],);
      }
      $text=  EXPORT_STATUS_TYPE.'<br>'.
              EXPORT_STATUS.'<br>'.
              xtc_draw_pull_down_menu('status',$customers_statuses_array, '1').'<br>'.
              '<br><b>W&auml;hrung</b><br>'.
              $curr.
              '<br><b>Kampagne</b><br>'.
              xtc_draw_pull_down_menu('campaign',$campaign_array).'<br>'.                             
              '<br><b>Speicherart</b><br>'.
              xtc_draw_radio_field('export', 'no',true).EXPORT_NO.'<br>'.
              xtc_draw_radio_field('export', 'yes',false).EXPORT_YES.'<br>'.
              '<br>' . xtc_button(BUTTON_EXPORT);
      $text=str_replace('<hr noshade>','<br>',$text);
      $text=str_replace('<b>Kundengruppe:</b>','<b>Kundengruppe</b>',$text);
      $text=str_replace('Bitte w&auml;hlen Sie die Kundengruppe, die Basis f&uuml;r den Exportierten Preis bildet. (Falls Sie keine Kundengruppenpreise haben, w&auml;hlen Sie <i>Gast</i>):</b>','Standard ist Gast',$text);
      return array('text' => $text);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_WEIN2_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_WEIN2_FILE', 'wein.xml',  '6', '1', '', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_WEIN2_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_WEIN2_STATUS','MODULE_WEIN2_FILE');
    }
  }
?>