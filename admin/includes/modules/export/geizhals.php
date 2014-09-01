<?php
/* -----------------------------------------------------------------------------------------
   $Id: geizhals.php 1508 2010-11-20 20:16:09Z dokuman $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com
   (c) 2003 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org
   (c) 2006 xt-commerce; www.xt-commerce.com

   based on:
   billiger.php
   added fields for geizhals specific export and included zip capability;
   named module geizhals.php

   updated version by franky_n

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_GEIZHALS_TEXT_DESCRIPTION', 'Export - Geizhals.de (; getrennt)');
define('MODULE_GEIZHALS_TEXT_TITLE', 'Geizhals.at - CSV');
define('MODULE_GEIZHALS_FILE_TITLE' , '<hr noshade>Dateiname');
define('MODULE_GEIZHALS_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br>(Verzeichnis export/)');
define('MODULE_GEIZHALS_STATUS_DESC','Modulstatus');
define('MODULE_GEIZHALS_STATUS_TITLE','Status');
define('MODULE_GEIZHALS_CURRENCY_TITLE','W&auml;hrung');
define('MODULE_GEIZHALS_CURRENCY_DESC','Welche W&auml;hrung soll exportiert werden?');
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
define('DATE_FORMAT_EXPORT', '%d.%m.%Y');  // this is used for strftime()
// include needed functions


  class geizhals {
    var $code, $title, $description, $enabled;

    function geizhals() {
      global $order;

      $this->code = 'geizhals';
      $this->title = MODULE_GEIZHALS_TEXT_TITLE;
      $this->description = MODULE_GEIZHALS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_GEIZHALS_SORT_ORDER;
      $this->enabled = ((MODULE_GEIZHALS_STATUS == 'True') ? true : false);
      $this->CAT=array();
      $this->PARENT=array();

    }


    function process($file) {
      @xtc_set_time_limit(0);
      require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
      $xtPrice = new xtcPrice($_POST['currencies'],$_POST['status']);
      $schema = 'artikelid;hersteller;herstellernummer;bezeichnung;kategorie;beschreibung_kurz;beschreibung_lang;bild_klein;deeplink;preis_val;product_ean;'.
                'lagerstand;lieferzeit;vkat;vkat_ausland1a;vkde;vkde_ausland1;freeamount'."\n"; # added new fields by geizhals
      $export_query =xtc_db_query("SELECT
                                           p.products_id,
                                           pd.products_name,
                                           pd.products_description,pd.products_short_description,
                                           p.products_model,p.products_ean,
                                           p.products_image,
                                           p.products_price,
                                           p.products_status,
                                           p.products_date_available,
                                           p.products_shippingtime,
                                           p.products_discount_allowed,
                                           pd.products_meta_keywords,
                                           p.products_tax_class_id,
                                           p.products_date_added,
                                           m.manufacturers_name,
                                           p.products_quantity,
                                           p.products_weight
                                    FROM
                                           " . TABLE_PRODUCTS . " p LEFT JOIN
                                           " . TABLE_MANUFACTURERS . " m
                                        ON p.manufacturers_id = m.manufacturers_id LEFT JOIN
                                           " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                        ON p.products_id = pd.products_id AND
                                           pd.language_id = '".$_SESSION['languages_id']."' LEFT JOIN
                                           " . TABLE_SPECIALS . " s
                                        ON p.products_id = s.products_id
                                     WHERE p.products_status = 1
                                     ORDER BY
                                           p.products_date_added DESC,
                                           pd.products_name"); # added p.products_quantity, p.products_weight to select by geizhals


      while ($products = xtc_db_fetch_array($export_query)) {
        $products_price = $xtPrice->xtcGetPrice($products['products_id'], $format=false, 1, $products['products_tax_class_id'], '');

        // get product categorie
        $categorie_query=xtc_db_query("SELECT
                                              categories_id
                                         FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                        WHERE products_id='".$products['products_id']."'");
        while ($categorie_data=xtc_db_fetch_array($categorie_query)) {
          $categories=$categorie_data['categories_id'];
        }

        ################## added by geizhals
        $shipping_query=xtc_db_query("SELECT
                                             shipping_status_name
                                        FROM ".TABLE_SHIPPING_STATUS."
                                       WHERE shipping_status_id=".$products['products_shippingtime'].
                                       " AND language_id=".$_SESSION['languages_id']);
                                       $shipping_data=xtc_db_fetch_array($shipping_query);
                                       $shipping[]=$shipping_data['shipping_status_name'];


        $vkat_query=xtc_db_query("select configuration_value as vkat from configuration where configuration_key='MODULE_SHIPPING_AP_COST_8'");
        $vkat_data=xtc_db_fetch_array($vkat_query);
        $vkat[]=$vkat_data['vkat'];

        $vkat_ausland_query=xtc_db_query("select configuration_value as vkat from configuration where configuration_key='MODULE_SHIPPING_AP_COST_1'");
        $vkat_ausland_data=xtc_db_fetch_array($vkat_ausland_query);
        $vkat_ausland[]=$vkat_ausland_data['vkat'];


        $vkde_query=xtc_db_query("select configuration_value as vkde from configuration where configuration_key='MODULE_SHIPPING_DP_COST_6'");
        $vkde_data=xtc_db_fetch_array($vkde_query);
        $vkde[]=$vkde_data['vkde'];

        $vkde_ausland_query=xtc_db_query("select configuration_value as vkde from configuration where configuration_key='MODULE_SHIPPING_DP_COST_1'");
        $vkde_ausland_data=xtc_db_fetch_array($vkde_ausland_query);
        $vkde_ausland[]=$vkde_ausland_data['vkde'];

        $free_query=xtc_db_query("select configuration_value as freeamount from configuration where configuration_key='MODULE_SHIPPING_FREEAMOUNT_AMOUNT'");
        $free_data=xtc_db_fetch_array($free_query);
        $free[]=$free_data['freeamount'];
        ################## end added by geizhals

        // remove trash
        $products_description = strip_tags($products['products_description']);
        $products_description = str_replace("<br>"," ",$products_description);
        $products_description = str_replace("<br />"," ",$products_description);
        $products_description = str_replace(";",", ",$products_description);
        $products_description = str_replace("'",", ",$products_description);
        $products_description = str_replace("\n"," ",$products_description);
        $products_description = str_replace("\r"," ",$products_description);
        $products_description = str_replace("\t"," ",$products_description);
        $products_description = str_replace("\v"," ",$products_description);
        $products_description = str_replace("&quot,"," \"",$products_description);
        $products_description = str_replace("&qout,"," \"",$products_description);
        $products_description = str_replace(chr(13)," ",$products_description);

        $products_short_description = strip_tags($products['products_short_description']);
        $products_short_description = str_replace("<br>"," ",$products_short_description);
        $products_short_description = str_replace("<br />"," ",$products_short_description);
        $products_short_description = str_replace(";",", ",$products_short_description);
        $products_short_description = str_replace("'",", ",$products_short_description);
        $products_short_description = str_replace("\n"," ",$products_short_description);
        $products_short_description = str_replace("\r"," ",$products_short_description);
        $products_short_description = str_replace("\t"," ",$products_short_description);
        $products_short_description = str_replace("\v"," ",$products_short_description);
        $products_short_description = str_replace("&quot,"," \"",$products_short_description);
        $products_short_description = str_replace("&qout,"," \"",$products_short_description);
        $products_short_description = str_replace(chr(13)," ",$products_short_description);
        $products_short_description = substr($products_short_description, 0, 255);
        $products_description = substr($products_description, 0, 65536);
        $cat = $this->buildCAT($categories);

        if ($products['products_image'] != ''){
          $image = HTTP_CATALOG_SERVER . DIR_WS_CATALOG_THUMBNAIL_IMAGES .$products['products_image'];
        }else{
          $image = '';
        }

        //create content
        $schema .= $products['products_id'] .";".
                   $products['manufacturers_name'].";".
                   $products['products_model'].";".
                   $products['products_name'].";".
                   substr($cat,0,strlen($cat)-2).";".
                   $products_short_description.";".
                   $products_description.";".
                   $image.";".
                   HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?'.$_POST['campaign'].xtc_product_link($products['products_id'], $products['products_name']) . ";" .
                   number_format($products_price,2,'.',''). ";" .
                   $products['products_ean'] . ";".
                   $products['products_quantity'] . ";" .
                   xtc_get_shipping_status_name($products['products_shippingtime']) . ";" .
                   $this->getShipCost($vkat[0], $products['products_weight']) . ";" .
                   $this->getShipCost($vkat_ausland[0], $products['products_weight']). ";" .
                   $this->getShipCost($vkde[0], $products['products_weight']) . ";" .
                   $this->getShipCost($vkde_ausland[0], $products['products_weight']). ";" .
                   $free[0] . "\n";
      }
      // create File
      $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file, "w+");
      fputs($fp, $schema);
      fclose($fp);

      ################## added by geizhals
      // zip file
      $zipfile = new zipfile();
      $filedata = implode("", file(DIR_FS_DOCUMENT_ROOT.'export/'.$file));
      $zipfile->add_file($filedata, $file);
      header("Content-type: application/octet-stream");
      header("Content-disposition: attachment; filename=zipfile.zip");

      $fp = fopen(DIR_FS_DOCUMENT_ROOT.'export/' . $file.'.zip', "w+");
      fputs($fp, $zipfile->file());
      fclose($fp);
      ################## end added by geizhals

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

    ################## added by geizhals
    function getShipCost($table, $weight) {
      $vals=explode(',',$table); //Dokuman - 2010-11-20 - replace deprecated function split() with explode()
      $ret='n/a';
      foreach($vals as &$val) {
        list($kg,$cost)=explode(':',$val); //Dokuman - 2010-11-20 - replace deprecated function split() with explode()
        if($weight <= $kg) {
          $ret = $cost;
          break;
        }
      }
      return $ret;
    }
    ################## end added by geizhals

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
       $curr.=xtc_draw_radio_field('currencies', $currencies_data['code'],true).$currencies_data['code'].'<br>';
      }
      $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
      $campaign_query = xtc_db_query("select campaigns_name, campaigns_refID from ".TABLE_CAMPAIGNS." order by campaigns_id");
      while ($campaign = xtc_db_fetch_array($campaign_query)) {
        $campaign_array[] = array ('id' => 'refID='.$campaign['campaigns_refID'].'&', 'text' => $campaign['campaigns_name'],);
      }

      return array('text' =>  EXPORT_STATUS_TYPE.'<br>'.
                              EXPORT_STATUS.'<br>'.
                              xtc_draw_pull_down_menu('status',$customers_statuses_array, '1').'<br>'.
                              CURRENCY.'<br>'.
                              CURRENCY_DESC.'<br>'.
                              $curr.
                              CAMPAIGNS.'<br>'.
                              CAMPAIGNS_DESC.'<br>'.
                              xtc_draw_pull_down_menu('campaign',$campaign_array).'<br>'.
                              EXPORT_TYPE.'<br>'.
                              EXPORT.'<br>'.
                              xtc_draw_radio_field('export', 'no',false).EXPORT_NO.'<br>'.
                              xtc_draw_radio_field('export', 'yes',true).EXPORT_YES.'<br>'.
                              '<br>' . xtc_button(BUTTON_EXPORT) .
                              xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=geizhals')));
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_GEIZHALS_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_GEIZHALS_FILE', 'geizhals.csv',  '6', '1', '', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_GEIZHALS_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
    }

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_GEIZHALS_STATUS','MODULE_GEIZHALS_FILE');
    }
  }

  ################## added by geizhals
  /*
  Zip file creation class makes zip files on the fly...
  use the functions add_dir() and add_file() to build the zip file;
  see example code below
  by Eric Mueller
  http://www.themepark.com
  v1.1 9-20-01
  - added comments to example
  v1.0 2-5-01
  initial version with:
  - class appearance
  - add_file() and file() methods
  - gzcompress() output hacking
  by Denis O.Philippov, webmaster@atlant.ru, http://www.atlant.ru
  */

  // official ZIP file format: http://www.pkware.com/appnote.txt

  class zipfile {
    var $datasec = array(); // array to store compressed data
    var $ctrl_dir = array(); // central directory
    var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
    var $old_offset = 0;

    // adds "directory" to archive - do this before putting any files in directory!
    // $name - name of directory... like this: "path/"
    // ...then you can add files using add_file with names like "path/file.txt"
    function add_dir($name) {
      $name = str_replace("\\", "/", $name);

      $fr = "\x50\x4b\x03\x04";
      $fr .= "\x0a\x00";    // ver needed to extract
      $fr .= "\x00\x00";    // gen purpose bit flag
      $fr .= "\x00\x00";    // compression method
      $fr .= "\x00\x00\x00\x00"; // last mod time and date

      $fr .= pack("V",0); // crc32
      $fr .= pack("V",0); //compressed filesize
      $fr .= pack("V",0); //uncompressed filesize
      $fr .= pack("v", strlen($name) ); //length of pathname
      $fr .= pack("v", 0 ); //extra field length
      $fr .= $name;
      // end of "local file header" segment

      // no "file data" segment for path

      // "data descriptor" segment (optional but necessary if archive is not served as file)
      $fr .= pack("V",$crc); //crc32
      $fr .= pack("V",$c_len); //compressed filesize
      $fr .= pack("V",$unc_len); //uncompressed filesize

      // add this entry to array
      $this -> datasec[] = $fr;

      $new_offset = strlen(implode("", $this->datasec));

      // now add to central record
      $cdrec = "\x50\x4b\x01\x02";
      $cdrec .="\x00\x00";    // version made by
      $cdrec .="\x0a\x00";    // version needed to extract
      $cdrec .="\x00\x00";    // gen purpose bit flag
      $cdrec .="\x00\x00";    // compression method
      $cdrec .="\x00\x00\x00\x00"; // last mod time & date
      $cdrec .= pack("V",0); // crc32
      $cdrec .= pack("V",0); //compressed filesize
      $cdrec .= pack("V",0); //uncompressed filesize
      $cdrec .= pack("v", strlen($name) ); //length of filename
      $cdrec .= pack("v", 0 ); //extra field length
      $cdrec .= pack("v", 0 ); //file comment length
      $cdrec .= pack("v", 0 ); //disk number start
      $cdrec .= pack("v", 0 ); //internal file attributes
      $ext = "\x00\x00\x10\x00";
      $ext = "\xff\xff\xff\xff";
      $cdrec .= pack("V", 16 ); //external file attributes  - 'directory' bit set

      $cdrec .= pack("V", $this -> old_offset ); //relative offset of local header
      $this -> old_offset = $new_offset;

      $cdrec .= $name;
      // optional extra field, file comment goes here
      // save to array
      $this -> ctrl_dir[] = $cdrec;
    }

    // adds "file" to archive
    // $data - file contents
    // $name - name of file in archive. Add path if your want
    function add_file($data, $name) {
      $name = str_replace("\\", "/", $name);
      //$name = str_replace("\\", "\\\\", $name);

      $fr = "\x50\x4b\x03\x04";
      $fr .= "\x14\x00";    // ver needed to extract
      $fr .= "\x00\x00";    // gen purpose bit flag
      $fr .= "\x08\x00";    // compression method
      $fr .= "\x00\x00\x00\x00"; // last mod time and date

      $unc_len = strlen($data);
      $crc = crc32($data);
      $zdata = gzcompress($data);
      $zdata = substr( substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
      $c_len = strlen($zdata);
      $fr .= pack("V",$crc); // crc32
      $fr .= pack("V",$c_len); //compressed filesize
      $fr .= pack("V",$unc_len); //uncompressed filesize
      $fr .= pack("v", strlen($name) ); //length of filename
      $fr .= pack("v", 0 ); //extra field length
      $fr .= $name;
      // end of "local file header" segment

      // "file data" segment
      $fr .= $zdata;

      // "data descriptor" segment (optional but necessary if archive is not served as file)
      $fr .= pack("V",$crc); //crc32
      $fr .= pack("V",$c_len); //compressed filesize
      $fr .= pack("V",$unc_len); //uncompressed filesize

      // add this entry to array
      $this -> datasec[] = $fr;

      $new_offset = strlen(implode("", $this->datasec));

      // now add to central directory record
      $cdrec = "\x50\x4b\x01\x02";
      $cdrec .="\x00\x00";    // version made by
      $cdrec .="\x14\x00";    // version needed to extract
      $cdrec .="\x00\x00";    // gen purpose bit flag
      $cdrec .="\x08\x00";    // compression method
      $cdrec .="\x00\x00\x00\x00"; // last mod time & date
      $cdrec .= pack("V",$crc); // crc32
      $cdrec .= pack("V",$c_len); //compressed filesize
      $cdrec .= pack("V",$unc_len); //uncompressed filesize
      $cdrec .= pack("v", strlen($name) ); //length of filename
      $cdrec .= pack("v", 0 ); //extra field length
      $cdrec .= pack("v", 0 ); //file comment length
      $cdrec .= pack("v", 0 ); //disk number start
      $cdrec .= pack("v", 0 ); //internal file attributes
      $cdrec .= pack("V", 32 ); //external file attributes - 'archive' bit set

      $cdrec .= pack("V", $this -> old_offset ); //relative offset of local header
      $this -> old_offset = $new_offset;

      $cdrec .= $name;
      // optional extra field, file comment goes here
      // save to central directory
      $this -> ctrl_dir[] = $cdrec;
    }

    function file() { // dump out file
      $data = implode("", $this -> datasec);
      $ctrldir = implode("", $this -> ctrl_dir);

      return
             $data.
             $ctrldir.
             $this -> eof_ctrl_dir.
             pack("v", sizeof($this -> ctrl_dir)).     // total # of entries "on this disk"
             pack("v", sizeof($this -> ctrl_dir)).     // total # of entries overall
             pack("V", strlen($ctrldir)).             // size of central dir
             pack("V", strlen($data)).                 // offset to start of central dir
             "\x00\x00";                             // .zip file comment length
    }
  }
  ################## end by geizhals

?>