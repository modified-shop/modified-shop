<?php
  /*
  xs:booster v1.0425 für xt:Commerce.
  Copyright (c) 2008-2012 xt:booster Ltd.
  http://www.xsbooster.com

  Licensed under GNU/GPL
  */

  if(!function_exists("curl_version")) {
    ?>
    <div style="font-size:11px;font-family:verdana,arial;color:red;font-weight:bold;"><?php echo TXT_CURL_WARNING; ?></div>
    <?php
    exit;
  }
  @set_time_limit(0);
  require('includes/application_top.php'); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
  require_once("../".DIR_WS_CLASSES.'xtbooster.php');
  require_once("../".DIR_WS_CLASSES.'xtcPrice.php');
  require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
  require_once('./includes/xsbooster/xsb_functions.php'); 
  if(is_file('./includes/xsbooster/xsb_tecdoc.php')) {
    require_once('./includes/xsbooster/xsb_tecdoc.php');
  }
  $xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
  require_once(DIR_FS_INC.'xtc_wysiwyg.inc.php'); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)

  $character_set_client = xtc_db_query("SHOW VARIABLES LIKE 'character_set_client'");
  $character_set_client = xtc_db_fetch_array($character_set_client);
  $character_set_client = $character_set_client['Value'];

  $xtb_module=$_SERVER['REQUEST_METHOD']=='GET'?$_GET['xtb_module']:$_POST['xtb_module'];
  if ($_POST['ACTION_Relist'])
    $xtb_module='RelistItem';
  $xtb = new xtbooster_base;
  $xtb->config();

  if( strtolower($xtb_config['MODULE_XTBOOSTER_STATUS'])!='true' ) {
    echo TXT_NOT_YET_INSTALLED;
  } else {
    if( !isset($xtb_config['MODULE_XTBOOSTER_SHOPKEY']) || trim($xtb_config['MODULE_XTBOOSTER_SHOPKEY']) == '' ) {
      $xtb_module = 'conf';
    }
    if($xtb_module=='cats') {
      if(!isset($_GET['depth'])) {
        $depth=1;
      } else {
        $depth=$_GET['depth'];
        $depth++;
      }
      $url = "id=".$_GET['id']."&id=".$_GET['id']."&depth=".$depth."&EBAY_SITE=".$_GET['EBAY_SITE']."&ShopKey=".$xtb_config['MODULE_XTBOOSTER_SHOPKEY'];
      if(isset($_GET['root']))
        $url .= "&root=".$_GET['root'];
      $x = new xtbooster_base;
      header("Content-type: text/html; charset=utf-8", true);
      echo $x->get("/_client_xt_ebaycat.php",$url);
      exit;
    } elseif($xtb_module=='FetchListingDurationOptions') {
      $url = "TYPE=".$_POST['TYPE']."&EBAY_SITE=".$_POST['EBAY_SITE']."&ShopKey=".$xtb_config['MODULE_XTBOOSTER_SHOPKEY'];
      $x = new xtbooster_base;
      header("Content-type: text/html; charset=utf-8", true);
      echo $x->get("/_client_xt_ebayduration.php",$url);
      exit;
    } elseif($xtb_module=='FetchShippingDetails') {
      $r = $xtb->exec("ACTION: GetShippingServiceDetails\nXTB_VERSION: ".$_POST['XTB_VERSION']."\nEBAY_SITE: ".$_POST['EBAY_SITE']."\n");
      header("Content-type: text/html; charset=utf-8", true);
      echo $r;
      exit;
    } elseif($xtb_module=='FetchPaymentMethods') {
      $r = $xtb->exec("ACTION: GetPaymentMethods\nXTB_VERSION: ".$_POST['XTB_VERSION']."\nEBAY_SITE: ".$_POST['EBAY_SITE']."\n");
      echo $r;
      exit;
    } elseif($xtb_module=='FetchAttributes') {
      $r = $xtb->exec("ACTION: GetAttributes\nXTB_VERSION: ".$_POST['XTB_VERSION']."\nCATEGORY_ID: ".$_POST['CATEGORY_ID']."\nEBAY_SITE: ".$_POST['EBAY_SITE']."\n");
      echo $_POST['CATEGORY_ID'] . '||' . $r;
      exit;
    } elseif($xtb_module=='SendTestMail') {
      $r = $xtb->exec("ACTION: SendTestMail\nXTB_VERSION: ".$_POST['XTB_VERSION']."\nKIND_OF_EMAIL: ".$_POST['KIND_OF_EMAIL']."\nFROM_NAME: ".base64_encode($_POST['FROM_NAME'])."\nFROM_ADDR: ".base64_encode($_POST['FROM_ADDR'])."\nSUBJECT: ".base64_encode($_POST['SUBJECT'])."\nMAIL_CONTENT: ".base64_encode($_POST['MAIL_CONTENT'])."\n");
      exit;
    } elseif ($xtb_module=='relist_ajx') {
      header("Content-type: text/html; charset=utf-8", true);
      // Ein Item neu einstellen
      $ITEM_ID = unserialize(base64_decode($_POST['request']));
      $RelistType = 0; // 0: Aktive Auktion, 1 (teilweise) erfolgreich, 2 erfolglos

      // Handelt es sich um eine Auktion, die erfolglos abgelaufen ist?
      $rlResult  = xtc_db_query("SELECT * FROM xtb_auctions WHERE XTB_ITEM_ID='".$ITEM_ID."'");
      $data    = xtc_db_fetch_array($rlResult);

      if ($data['_EBAY_END_TIME']<time()) {  // Auktion abgelaufen
        if ($data['_EBAY_QUANTITY_BUYED']==0) { // Nichts verkauft
          $RelistType = 2;
        } else {                // Einige Artikel verkauft
          $RelistType = 1;
        }
      } else { // Auktion läuft noch
        $RelistType = 0;
      }
      // API-Call "RelistItem" machen
      $request = "ACTION:      RelistItem
                  ITEMID:     ".$data['_EBAY_ITEM_ID']."
                  EBAYMARKETPLACE:     ".$data['_EBAY_MARKETPLACE']."
                 ";
      $res = $xtb->exec($request);
      $r   = $xtb->parse($res);
  
      // Bei Erfolg Datensatz klonen und neue Auktions-ID eintragen
      if ($r['RESULT']=='SUCCESS')  {
        // Datensatz klonen
        xtc_db_query("INSERT INTO xtb_auctions (products_id, 
                                  TITLE, 
                                  SUBTITLE, 
                                  DESCRIPTION, 
                                  CAT_PRIMARY, 
                                  CAT_SECONDARY, 
                                  PICTUREURL, 
                                  SCHEDULETIME, 
                                  STARTPRICE,  
                                  BUYITNOWPRICE, 
                                  CURRENCY, 
                                  COUNTRY, 
                                  TYPE, 
                                  QUANTITY, 
                                  DURATION, 
                                  LOCATION, 
                                  POSTALCODE, 
                                  _EBAY_MARKETPLACE, 
                                  LISTINGENHANCEMENTS, 
                                  GALLERY_PICTUREURL, 
                                  GALLERYTYPE)
                                  SELECT products_id, 
                                         TITLE, 
                                         SUBTITLE, 
                                         DESCRIPTION, 
                                         CAT_PRIMARY, 
                                         CAT_SECONDARY, 
                                         PICTUREURL, 
                                         SCHEDULETIME, 
                                         STARTPRICE,  
                                         BUYITNOWPRICE, 
                                         CURRENCY, 
                                         COUNTRY, 
                                         TYPE, 
                                         QUANTITY, 
                                         DURATION, 
                                         LOCATION, 
                                         POSTALCODE, 
                                         _EBAY_MARKETPLACE, 
                                         LISTINGENHANCEMENTS, 
                                         GALLERY_PICTUREURL, 
                                         GALLERYTYPE
                                    FROM xtb_auctions WHERE XTB_ITEM_ID='".$ITEM_ID."' LIMIT 1");
  
        // Geänderte Daten ergänzen
        xtc_db_query("UPDATE xtb_auctions 
                         SET _EBAY_ITEM_ID='".$r['ITEMID']."',
                             _EBAY_START_TIME='".$r['STARTTIME']."',
                             _EBAY_END_TIME='".$r['ENDTIME']."',
                             _EBAY_STATUS='active',
                             _EBAY_QUANTITY_BUYED='0',
                             QUANTITY_CHECKED_OUT='0',
                             SCHEDULETIME='".$r['STARTTIME']."',
                             _XTB_ITEM_HASH='".$r['ITEM_HASH']."'
                       WHERE XTB_ITEM_ID='". xsb_db_first(xtc_db_query("SELECT LAST_INSERT_ID() as last_insert_id FROM xtb_auctions")) ."'");
            
        // Produktdaten abfragen
        $auction_query =  xtc_db_query("SELECT products_id, TITLE, TYPE, QUANTITY FROM xtb_auctions WHERE XTB_ITEM_ID='".$ITEM_ID."' LIMIT 1"); $auction_data = xtc_db_fetch_array($auction_query);
        $products_query = xtc_db_query("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join ".TABLE_PRODUCTS_IMAGES." as pi ON (pi.products_id = pd.products_id) WHERE p.products_id = pd.products_id AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND p.products_id = '".$auction_data['products_id']."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
        $x = xtc_db_fetch_array($products_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)

        // Erfolgsmeldung
        ?>
        <div style="display:none" id="RESULT"><?php echo $r['RESULT'];?></div>
        <div class="smallText" style="font-size:arial;font-size:10px;padding:4px;background-color:#707070;color:white;border-bottom:1px solid white;">
          <strong><?php echo TXT_EBAY_AUCTION_WITH_ID.' '.$r['ITEMID_ORIGIN'].' '.TXT_HAS_BEEN_RELISTED.', '. TXT_NEW_ID.': '.$r['ITEMID']; ?> (<?php echo "eBay ".$r['EBAY_SITE_COUNTRY']; ?>)</strong><br/>
          <?php echo $auction_data['QUANTITY']?>x <?php echo stripslashes($auction_data['TITLE']); ?> (<?php echo TXT_ART_NO .' '. $x['products_model']?>), <?php echo TXT_RUNTIME?>: <?php echo strftime(TIME_FORMAT,$r['STARTTIME']);?> - <?php echo strftime(TIME_FORMAT,$r['ENDTIME']);?>, <?php echo TXT_AUCTIONTYPE?>: <?php echo $auction_data['TYPE']?>
        </div>
        <?php
      } else {
        // Fehlermeldung
        $e=unserialize($r['ERROR_MSG']);
        if(is_array($e))  {
          foreach($e as $item) {
            ?>
            <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($item->ShortMessage)." - ".encode_htmlspecialchars($item->LongMessage).""." (".encode_htmlspecialchars($item->ErrorCode).")";?></div>
            <?php
          }
        } elseif(is_object($e)) {
          $item=$e;  ?>
          <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($item->ShortMessage)." - ".encode_htmlspecialchars($item->LongMessage).""." (".encode_htmlspecialchars($item->ErrorCode).")";?></div>
          <?php
        } else {
          ?>
          <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($r['ERROR_MSG'])." (".$r['ERROR_CODE'].")";?></div>
          <?php
        }
      }
      exit;
      // Ende relist_ajx  
    } elseif($xtb_module=='add_base') {
      $_POST['add'] = unXmlize($_POST['add']);
      $jobs = array();
      if($_POST['add']['multi_xtb']=='1') {
        // Multi Transaction
        $multi_products = $_SESSION['xtb1']['multi_xtb'];
        $_SESSION['xtb1']['multi_settings'] = $_POST['add']; 
        // Trade Template abrufen..
        $requestx = "ACTION:  TradeTemplateFetch";
        $resx = $xtb->exec($requestx);
        $resx = $xtb->parse($resx);
        // $tradetemplate = $resx['TEMPLATE'];
        $_SESSION['xtb1']['multi_settings']['tradetemplate'] = $resx['TEMPLATE']; 
        $_SESSION['xtb1']['multi_settings']['DEFAULT_CUSTOMER_GROUP'] = $resx['DEFAULT_CUSTOMER_GROUP']; 
        foreach($multi_products as $products_id) {
          $item['PRODUCT_ID'] = $products_id;
          $jobs[$products_id]=$item;
        }
      } else {
        // Single Transaction
        if(isset($_SESSION['xtb1']['multi_settings'])) { 
          unset($_SESSION['xtb1']['multi_settings']);
        }
        $jobs[] = $_POST['add'];
      }
      $requests=array();
      foreach($jobs as $item) {
        $requests[]=base64_encode(serialize($item)); 
      }
      echo xmlize($requests);
      exit;
    } elseif($xtb_module=='add_ajx') {
      header("Content-type: text/html; charset=utf-8", true);
      if(isset($_SESSION['xtb1']['multi_settings'])) {
        $item = $_SESSION['xtb1']['multi_settings'];
        $item['PRODUCT_ID'] = implode(unserialize(base64_decode($_POST['request'])));
      } else {
        $item = unserialize(base64_decode($_POST['request'])); }
        $products_query = xtc_db_query("SELECT * FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd left join ".TABLE_PRODUCTS_IMAGES." as pi ON (pi.products_id = pd.products_id) WHERE p.products_id = pd.products_id AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND p.products_id = '".$item['PRODUCT_ID']."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
        $x = xtc_db_fetch_array($products_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
        // Weitere Produkt-Bilder
        $images = array();
        $images[0]=$x['products_image'];
        if($x['image_nr']!='')
          $images[$x['image_nr']]=$x['image_name'];
        while($x1 = xtc_db_fetch_array($products_query)) 
          $images[$x1['image_nr']] = $x1['image_name']; 
          if(isset($item['DESCRIPTION'])) {
            $desc = $item['DESCRIPTION'];
            if(preg_match("/#ARTICLE_PRICE#/", $desc)) {
              $desc = str_replace("#ARTICLE_PRICE#", $xtPrice->xtcFormat($item['STARTPRICE'],true), $desc);
            }
            if(preg_match("/#ARTICLE_VPE#/",$desc)) {
              if($x['products_vpe_value']==0)
                $x['products_vpe_value'] = 1;
              $desc = str_replace("#ARTICLE_VPE#", $xtPrice->xtcFormat($item['STARTPRICE'] * (1.0 / $x['products_vpe_value']), true)."/".xtc_get_vpe_name($x['products_vpe']), $desc);
            }
          } else {
            // 280809: Wenn multi, Artikel erst hier zusammensetzen,
            // damit der Multi-String nicht so ewig lang ist
            $tradetemplate = $_SESSION['xtb1']['multi_settings']['tradetemplate'];
            $item['TITLE'] = $x['products_name'];
            if(1==$_SESSION['xtb1']['multi_settings']['AUTO_SUBTITLE'][0])
              $item['SUBTITLE'] = $x['products_short_description'];
            $tax_query = xtc_db_query("select tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '".$x['products_tax_class_id']."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
            $tax = xtc_db_fetch_array($tax_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
            $price = $x['products_price'];
            $price = ($price*($tax['tax_rate']+100)/100);
    
            if(trim($_SESSION['xtb1']['multi_settings']['STARTPRICE_DISCOUNT'])=='')
              $_SESSION['xtb1']['multi_settings']['STARTPRICE_DISCOUNT'] = 0;
            $item['STARTPRICE'] = round($price-($price/100*$_SESSION['xtb1']['multi_settings']['STARTPRICE_DISCOUNT']),2);
            if(@implode($_SESSION['xtb1']['multi_settings']['BUYITNOW_ACTIVE'])=='1') {
              $item['BUYITNOW_ACTIVE'] = $_SESSION['xtb1']['multi_settings']['BUYITNOW_ACTIVE'];
              if(trim($_SESSION['xtb1']['multi_settings']['BUYITNOW_DISCOUNT'])=='')
                $_SESSION['xtb1']['multi_settings']['BUYITNOW_DISCOUNT'] = 0;
              $item['BUYITNOWPRICE'] = round($price-($price/100*$_SESSION['xtb1']['multi_settings']['BUYITNOW_DISCOUNT']),2);
            }
            $pi=0;
            foreach($images as $k=>$image) {
              // absolute Bild-Adressen beruecksichtigen
              // & schauen dass kein https drinsteht (das eBay nicht akzeptiert)
              if((0 === strpos($image,'http://'))||(0 === strpos($image,'https://'))) {
                if($pi==0) {
                  $item['PICTUREURL'] = $image!='' ? str_replace('https','http',$image) : '';
                  $item['GALLERY_PICTUREURL'] = $image!='' ? str_replace('https','http',$image) : '';
                }
              } else {
                if(substr(DIR_WS_CATALOG_POPUP_IMAGES,-1)!='/'&&$image[0]!='/')
                  $images[$k] = "/".$image;
                if($pi==0) {
                  $item['PICTUREURL'] = $image!='' ? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image : '';
                  $item['GALLERY_PICTUREURL'] = $image!='' ? HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image : '';
                }
              }
              $pi++;
            }
            reset($images);

            $desc = $tradetemplate;
            if(preg_match("/#ARTICLE_DESCRIPTION#/",$desc)) {
              $desc = str_replace("#ARTICLE_DESCRIPTION#", $x['products_description'], $desc);
            }
            if(preg_match("/#ARTICLE_TITLE#/",$desc)) {
              $desc = str_replace("#ARTICLE_TITLE#", $x['products_name'], $desc);
            }
            if(preg_match("/#ARTICLE_SUBTITLE#/",$desc)) {
              $desc = str_replace("#ARTICLE_SUBTITLE#", $x['products_short_description'], $desc);
            }
            if(preg_match("/#ARTICLE_PRICE#/",$desc)) {
              $desc = str_replace("#ARTICLE_PRICE#", $xtPrice->xtcFormat($item['STARTPRICE'],true), $desc);
            }
            if(preg_match("/#ARTICLE_NUMBER#/",$desc)) {
              $desc = str_replace("#ARTICLE_NUMBER#", $x['products_model'], $desc);
            }
            if(preg_match("/#ARTICLE_VPE#/",$desc)) {
              if($x['products_vpe_value']==0)
                $x['products_vpe_value'] = 1;
              $desc = str_replace("#ARTICLE_VPE#", $xtPrice->xtcFormat($item['STARTPRICE'] * (1.0 / $x['products_vpe_value']), true)."/".xtc_get_vpe_name($x['products_vpe']), $desc);
            }
            $item['DESCRIPTION'] = $desc;
          }
          foreach($images as $pi=>$image) { $pi++;
            if((0 === strpos($image,'http://'))||(0 === strpos($image,'https://'))) {
              if(preg_match("/src=\"*#PICTURE_".$pi."#\"*/", $desc)) {
                $desc = str_replace("#PICTURE_".$pi."#", $image, $desc);
              } elseif(preg_match("/#PICTURE_".$pi."#/", $desc)) {
                $desc = str_replace("#PICTURE_".$pi."#", "<img src=\"".$image."\" style=\"border:0;\" alt=\"\" title=\"\" />", $desc);
              }
            } else {
              if(substr(DIR_WS_CATALOG_POPUP_IMAGES,-1)!='/'&&$image[0]!='/')
                $images[$pi-1] = "/".$image; 
              if(preg_match("/src=\"*#PICTURE_".$pi."#\"*/", $desc)) {
                $desc = str_replace("#PICTURE_".$pi."#", HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image, $desc);
              } elseif(preg_match("/#PICTURE_".$pi."#/", $desc)) {
                $desc = str_replace("#PICTURE_".$pi."#", "<img src=\"".HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image."\" style=\"border:0;\" alt=\"\" title=\"\" />", $desc);
              }
            }
          }
          for($pi=0;$pi<30;$pi++)
            $desc = preg_replace("/<img [^<>]*src *= *\"*#PICTURE_".$pi."#\"* [^>]*>/i", "", $desc);
          for($pi=0;$pi<30;$pi++)
            $desc = str_replace("#PICTURE_".$pi."#", "", $desc);
          // Relative Bildnamen aus der Produktbescheibung oder Template mit der Shop-URL versehen
          if (preg_match('#src=(?![\'"]?(?:https?:)?//)([\'"])?#', $desc)) {
            $desc=preg_replace('#src=(?![\'"]?(?:https?:)?//)([\'"])?\/#', 'src=$1'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG, $desc); 
            $desc=preg_replace('#src=(?![\'"]?(?:https?:)?//)([\'"])?#', 'src=$1'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG, $desc); 
          }
          $item['DESCRIPTION'] = $desc;
          reset($images);

          // Zeichensatz pruefen
          if(!is_utf8($item['TITLE']))
          { $item['TITLE'] = utf8_encode($item['TITLE']); }
          if(!is_utf8($item['SUBTITLE']))
          { $item['SUBTITLE'] = utf8_encode($item['SUBTITLE']); }
          if(!is_utf8($item['DESCRIPTION']))
          { $item['DESCRIPTION'] = utf8_encode($item['DESCRIPTION']); }

          // stripslashes nicht bei multi (bereits implizit passiert, noch mal wuerde gewollte Backslashes loeschen)
          if(!isset($_SESSION['xtb1']['multi_settings']))
          { $item['DESCRIPTION'] = stripslashes($item['DESCRIPTION']); }

          $item['POSTALCODE'] = xsb_db_first(xtc_db_query("select configuration_value from ".TABLE_CONFIGURATION." where configuration_key='MODULE_XTBOOSTER_STDPLZ'"));

          $title = utf8_substr(strip_tags($item['TITLE']),0,79);
  
          $request = "ACTION:			AddItem
          TITLE:			-=".base64_encode($title)."
          ";
          if($item['SUBTITLE_USE'][0] | $item['AUTO_SUBTITLE'][0]) {
            $subtitle = utf8_substr(strip_tags($item['SUBTITLE']),0,54);
            $request .= "SUBTITLE:		-=".base64_encode($subtitle)."\n";
          }
          $request .= "
                       DESCRIPTION:	-=".base64_encode($item['DESCRIPTION'])."
                       LOCATION:    -=".base64_encode($item['LOCATION'])."
                       COUNTRY:    DE
                       EBAY_SITE:     ".$item['EBAY_SITE']."
                       CURRENCY:    ".$item['CURRENCY']."
                       TYPE:      ".$item['TYPE']."
                       HITCOUNTER:    ".$item['HITCOUNTER']."
                       POSTALCODE:    ".$item['POSTALCODE']."
                       PICTUREURL:    ".$item['PICTUREURL']."
                       PICTUREURL1:  ".$item['PICTUREURL1']."
                       PICTUREURL2:  ".$item['PICTUREURL2']."
                       STARTPRICE:    ".$item['STARTPRICE']."
                       LISTINGDURATION: ".$item['DURATION']."
                       QUANTITY:    ".$item['QUANTITY']."
                       PRODUCT_ID:    ".$item['PRODUCT_ID']."
                       LISTINGENHANCEMENTS: ".@implode(",",$item['LISTINGENHANCEMENTS'])."
                       PAYMENTMETHODS: ".@implode(",",$item['PAYMENTMETHODS'])."
                       PAYPAL_ADDRESS: ".$item['PAYPAL_ADDRESS']."
                       GALLERYTYPE:  ".$item['GALLERYTYPE']."
                       GALLERY_PICTUREURL: ".$item['GALLERY_PICTUREURL']."
                       XTBOOSTER_VERSION: ".XTBOOSTER_VERSION."
                       REDIRECT_USER_TO: ".$item['REDIRECT_USER_TO']."
                       ALLOW_USER_CHQTY: ".$item['ALLOW_USER_CHQTY']."
                       DEFAULT_CUSTOMER_GROUP: ".$item['DEFAULT_CUSTOMER_GROUP']."
                       ATTRIBUTES1: -=".base64_encode(serialize($item['ATTRIBUTES1']))."
                       ATTRIBUTES2: -=".base64_encode(serialize($item['ATTRIBUTES2']))."
                      ";
  
          if($item['CAT_PRIMARY']!='') {
            $request .= "CAT_PRIMARY:  ".$item['CAT_PRIMARY']."\n";
            $request .= "CAT_PRIMARY_DESCR:  -=".base64_encode($item['CAT_PRIMARY_DESCR'])."\n";
          }
          if($item['CAT_SECONDARY']!='') {
            $request .= "CAT_SECONDARY:  ".$item['CAT_SECONDARY']."\n";
            $request .= "CAT_SECONDARY_DESCR: -=".base64_encode($item['CAT_SECONDARY_DESCR'])."\n";
          }
          if($item['CAT_STORE_PRIMARY']!='') {
            $request .= "CAT_STORE_PRIMARY:  ".$item['CAT_STORE_PRIMARY']."\n";
            if($item['CAT_STORE_PRIMARY_DESCR']!='')
              $request .= "CAT_STORE_PRIMARY_DESCR:  -=".base64_encode($item['CAT_STORE_PRIMARY_DESCR'])."\n";
          }
          if($item['CAT_STORE_SECONDARY']!='') {
            $request .= "CAT_STORE_SECONDARY:  ".$item['CAT_STORE_SECONDARY']."\n";
            if($item['CAT_STORE_SECONDARY_DESCR']!='')
              $request .= "CAT_STORE_SECONDARY_DESCR: -=".base64_encode($item['CAT_STORE_PRIMARY_DESCR'])."\n";
          }
  
          $request .= "SCHEDULETIME:  ".$item['SCHEDULETIME']."\n";
  
          if($item['PRIVATE_LISTING']=='1')
            $request .= "PRIVATE_LISTING:	1\n";

          if($item['BEST_OFFER']=='1')
            $request .= "BEST_OFFER:	1\n";

          if(defined('TECDOC_TABLE') && defined('TECDOC_COLUMN') && defined('TECDOC_PRODUCTS_ID_ALIAS')) {
            $kTypeQuery = xtc_db_query("SELECT ".TECDOC_COLUMN." FROM ".TECDOC_TABLE." WHERE ".TECDOC_PRODUCTS_ID_ALIAS."=".$item['PRODUCT_ID']." LIMIT 1");
            if(xtc_db_num_rows($kTypeQuery) > 0) {
              $kType = mysql_result($kTypeQuery, 0, 0);
            } else {
              $kType = '';
            }
            if (!empty($kType)) {
              $request .= "TECDOC_KTYPE:      $kType\n";
            }
          }

          if(@implode($item['BUYITNOW_ACTIVE'])=='1')
            $request .= "BUYITNOWPRICE:  ".$item['BUYITNOWPRICE']."\n";
  
          if(trim($item['SHIPPINGCOSTS'])!='') {
            $request .= "SHIPPINGCOSTS:  ".(($item['SHIPPINGCOSTS']=='=GEWICHT')?$x['products_weight']:$item['SHIPPINGCOSTS'])."\n";
            $x = explode("|",$item['SHIPPINGTYPE']);
            $request .= "SHIPPINGTYPE:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS: ".$item['SHIPTOLOCATIONS']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST: ".$item['SHIPPINGSERVICEADDITIONALCOST']."\n";
          }
          if(trim($item['SHIPPINGCOSTS1'])!='') {
            $request .= "SHIPPINGCOSTS1:  ".$item['SHIPPINGCOSTS1']."\n";
            $x = explode("|",$item['SHIPPINGTYPE1']);
            $request .= "SHIPPINGTYPE1:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS1: ".$item['SHIPTOLOCATIONS1']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST1']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST1: ".$item['SHIPPINGSERVICEADDITIONALCOST1']."\n";
          }
          if(trim($item['SHIPPINGCOSTS2'])!='') {
            $request .= "SHIPPINGCOSTS2:  ".$item['SHIPPINGCOSTS2']."\n";
            $x = explode("|",$item['SHIPPINGTYPE2']);
            $request .= "SHIPPINGTYPE2:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS2: ".$item['SHIPTOLOCATIONS2']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST2']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST2: ".$item['SHIPPINGSERVICEADDITIONALCOST2']."\n";
          }
          if(trim($item['SHIPPINGCOSTS3'])!='') {
            $request .= "SHIPPINGCOSTS3:  ".$item['SHIPPINGCOSTS3']."\n";
            $x = explode("|",$item['SHIPPINGTYPE3']);
            $request .= "SHIPPINGTYPE3:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS3: ".$item['SHIPTOLOCATIONS3']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST3']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST3: ".$item['SHIPPINGSERVICEADDITIONALCOST3']."\n";
          }
          if(trim($item['SHIPPINGCOSTS4'])!='') {
            $request .= "SHIPPINGCOSTS4:  ".$item['SHIPPINGCOSTS4']."\n";
            $x = explode("|",$item['SHIPPINGTYPE4']);
            $request .= "SHIPPINGTYPE4:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS4: ".$item['SHIPTOLOCATIONS4']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST4']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST4: ".$item['SHIPPINGSERVICEADDITIONALCOST4']."\n";
          }
          if(trim($item['SHIPPINGCOSTS5'])!='') {
            $request .= "SHIPPINGCOSTS5:  ".$item['SHIPPINGCOSTS5']."\n";
            $x = explode("|",$item['SHIPPINGTYPE5']);
            $request .= "SHIPPINGTYPE5:  ".$x[0]."\n";
            if($x[1]=='1')
              $request .= "SHIPTOLOCATIONS5: ".$item['SHIPTOLOCATIONS5']."\n";
            if($item['QUANTITY']>1&&$item['SHIPPINGSERVICEADDITIONALCOST5']!='')
              $request .= "SHIPPINGSERVICEADDITIONALCOST5: ".$item['SHIPPINGSERVICEADDITIONALCOST5']."\n";
          }
  
          $res = $xtb->exec($request);
          $request = $xtb->parse($request);
          $r = $xtb->parse($res);

          if($r['RESULT']=='SUCCESS') {
            $TITLE =    $request['TITLE']!=""?"0x".bin2hex(stripslashes($request['TITLE'])):"''";
            $SUBTITLE = $request['SUBTITLE']!=""?"0x".bin2hex($request['SUBTITLE']):"''";
            $DESCRIPTION = $request['DESCRIPTION']!=""?"0x".bin2hex($request['DESCRIPTION']):"''";
            $LOCATION = $request['LOCATION']!=""?"0x".bin2hex($request['LOCATION']):"''";
            $_XTB_ITEM_HASH = $r['ITEM_HASH'];
            $sql = "
                    INSERT INTO `xtb_auctions` (
                                `products_id`,
                                `TITLE`,
                                `SUBTITLE`,
                                `DESCRIPTION`,
                                `CAT_PRIMARY`,
                                `CAT_SECONDARY`,
                                `PICTUREURL`,
                                `SCHEDULETIME`,
                                `STARTPRICE`,
                                `BUYITNOWPRICE`,
                                `CURRENCY`,
                                `COUNTRY`,
                                `TYPE`,
                                `QUANTITY`,
                                `DURATION`,
                                `LOCATION`,
                                `POSTALCODE`,
                                `_EBAY_ITEM_ID`,
                                `_EBAY_START_TIME`,
                                `_EBAY_END_TIME`,
                                `_EBAY_STATUS`,
                                `_EBAY_QUANTITY_BUYED`,
                                `_EBAY_MARKETPLACE`,
                                `QUANTITY_CHECKED_OUT`,
                                `LISTINGENHANCEMENTS`,
                                `GALLERYTYPE`,
                                `GALLERY_PICTUREURL`,
                                `_XTB_ITEM_HASH`
                              ) VALUES (
                                '".$request['PRODUCT_ID']."',
                                $TITLE,
                                $SUBTITLE,
                                $DESCRIPTION,
                                '".$request['CAT_PRIMARY']."',
                                '".$request['CAT_SECONDARY']."',
                                '".$request['PICTUREURL']."',
                                '".$request['SCHEDULETIME']."',
                                '".$request['STARTPRICE']."',
                                '".$request['BUYITNOWPRICE']."',
                                '".$request['CURRENCY']."',
                                '".$request['COUNTRY']."',
                                '".$request['TYPE']."',
                                '".$request['QUANTITY']."',
                                '".$request['LISTINGDURATION']."',
                                $LOCATION,
                                '".$request['POSTALCODE']."',
                                '".$r['ITEMID']."',
                                '".$r['STARTTIME']."',
                                '".$r['ENDTIME']."',
                                'active',
                                0,
                                '".$r['EBAY_SITE_COUNTRY']."',
                                0,
                                '".$request['LISTINGENHANCEMENTS']."',
                                '".$request['GALLERYTYPE']."',
                                '".$request['GALLERY_PICTUREURL']."',
                                '".$_XTB_ITEM_HASH."'
                              )";
            xtc_db_query($sql);
            ?>
            <div style="display:none" id="RESULT"><?php echo $r['RESULT'];?></div>
            <div class="smallText" style="font-size:arial;font-size:10px;padding:4px;background-color:#707070;color:white;border-bottom:1px solid white;">
              <strong><?php echo TXT_EBAY_AUCTION_WITH_ID .' '.$r['ITEMID'].' '.TXT_HAS_BEEN_CREATED."! (eBay ".$r['EBAY_SITE_COUNTRY'].")"; ?></strong><br/>
              <?php echo $request['QUANTITY'].'x '.stripslashes($request['TITLE']).' ('.TXT_ART_NO.' '.$x['products_model'].'), '.TXT_RUNTIME.': '.strftime(TIME_FORMAT,$r['STARTTIME']).' - '.strftime(TIME_FORMAT,$r['ENDTIME']).', '.TXT_AUCTIONTYPE.': '.$request['TYPE'].', '.TXT_STARTPRICE.': '.$request['STARTPRICE']; if($request['BUYITNOWPRICE'] > $request['STARTPRICE']) echo ", ".TXT_BUYITNOWPRICE.': '.$request['BUYITNOWPRICE'];?>
            </div>
            <?php
          } else {
            $e=unserialize($r['ERROR_MSG']);
            // print_r($r);
            if(is_array($e)) {
              foreach($e as $item) {
                // if(is_array($item)) print_r($item);
                ?>
                <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($item->ShortMessage)." - ".encode_htmlspecialchars($item->LongMessage).""." (".encode_htmlspecialchars($item->ErrorCode).")";?></div>
                <?php
              }
            } elseif(is_object($e)) {
              $item=$e;
              ?>
              <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($item->ShortMessage)." - ".encode_htmlspecialchars($item->LongMessage).""." (".encode_htmlspecialchars($item->ErrorCode).")";?></div>
              <?php
            } else {
              ?>
              <div class="smallText" style="padding:4px;background-color:red;color:white;font-weight:bold;"><?php echo TXT_ERROR?>: <?php echo encode_htmlspecialchars($r['ERROR_MSG'])." (".$r['ERROR_CODE'].")";?></div>
              <?php
            }
          }
          exit;
        }
        header("Content-type: text/html; charset=utf-8", true);
        require (DIR_WS_INCLUDES.'head.php');
        ?>
            <link rel="stylesheet" type="text/css" href="includes/xsbooster/xsbooster.css" />
            <script type="text/javascript" src="includes/xsbooster/prototype.js"></script>
            <script type="text/javascript" src="includes/xsbooster/effects.js"></script>
            <script type="text/javascript" src="includes/xsbooster/xsbooster.js"></script>
            <script>
              var XTB_VERSION="<?php echo XTBOOSTER_VERSION?>";
              var XTBOOSTER_VERSION=XTB_VERSION;
            </script>
          </head>
          <body>
            <div id='screen' style="display:none;height:9000;"></div>
            <div id='pleasewait' style="display:none;"><div id='pleasewaitcontent'><?php echo TXT_PLEASEWAIT; ?></div></div>
            <!-- header //-->
            <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
            <!-- header_eof //-->
            <!-- body //-->
            <table border="0" width="100%" cellspacing="2" cellpadding="2">
              <tr>
                <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
                  <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
                    <!-- left_navigation //-->
                    <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
                    <!-- left_navigation_eof //-->
                  </table>
                </td>
                <!-- body_text //-->
                <td class="boxCenter" width="100%" valign="top">
                  <table border="0" width="100%" cellspacing="0" cellpadding="2">
                    <tr>
                      <td width="100%" style="padding:10px;">
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                          <tr>
                            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                            <td class="pageHeading" align="right"><?php echo xtc_draw_separator('pixel_trans.gif', '57', '40'); ?></td>
                          </tr>
                        </table>
                        <div id='content_ajx' style='display:none;'>
                          <div id='jso'>
                            <div class="smallText" style="font-size:arial;font-size:11px;padding:4px;border-bottom:1px solid white;">
                              <div id='moment' style="font-weight:bold;font-size:13px;margin-bottom:5px;"><?php echo TXT_BE_PATIENT_WHILE_SUBMITTING_AUCTIONS?></div>
                              <div id='status' style="font-weight:bold;color:green;margin-bottom:5px;"><?php echo ' <span id="itemcount">0</span> '.TXT_AUCTIONS_SUBMITTED.' (0%)'?></div>
                            </div>
                          </div>
                          <div id='content_ajx_in'></div>
                          <a href="JavaScript:void(0);" onclick="xsb.back()"><?php echo TXT_GO_BACK;?></a>
                        </div>
                        <div id='content'>
                          <?php
                          switch($xtb_module) {
                            case 'conf':
                              if($_SERVER['REQUEST_METHOD']=='POST') {
                                // MODULE_XTBOOSTER_SHOPKEY
                                if( !isset($xtb_config['MODULE_XTBOOSTER_SHOPKEY']) )
                                  xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_XTBOOSTER_SHOPKEY', '".$_POST['MODULE_XTBOOSTER_SHOPKEY']."', '6', '1', '', now())");
                                else
                                  xtc_db_query("update " . TABLE_CONFIGURATION . " SET configuration_value = '".$_POST['MODULE_XTBOOSTER_SHOPKEY']."' WHERE configuration_key = 'MODULE_XTBOOSTER_SHOPKEY'");
                                // MODULE_XTBOOSTER_STDSITE
                                if(@$_POST['MODULE_XTBOOSTER_STDSITE']!='') {
                                  if( !isset($xtb_config['MODULE_XTBOOSTER_STDSITE']) )
                                    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_XTBOOSTER_STDSITE', '".$_POST['MODULE_XTBOOSTER_STDSITE']."', '6', '1', '', now())");
                                  else
                                    xtc_db_query("update " . TABLE_CONFIGURATION . " SET configuration_value = '".$_POST['MODULE_XTBOOSTER_STDSITE']."' WHERE configuration_key = 'MODULE_XTBOOSTER_STDSITE'");
                                }
                                // MODULE_XTBOOSTER_STDCURRENCY
                                if(@$_POST['MODULE_XTBOOSTER_STDCURRENCY']!='') {
                                  if( !isset($xtb_config['MODULE_XTBOOSTER_STDCURRENCY']) )
                                    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_XTBOOSTER_STDCURRENCY', '".$_POST['MODULE_XTBOOSTER_STDCURRENCY']."', '6', '1', '', now())");
                                  else
                                    xtc_db_query("update " . TABLE_CONFIGURATION . " SET configuration_value = '".$_POST['MODULE_XTBOOSTER_STDCURRENCY']."' WHERE configuration_key = 'MODULE_XTBOOSTER_STDCURRENCY'");
                                }
                                // MODULE_XTBOOSTER_STDSTANDORT
                                if(@$_POST['MODULE_XTBOOSTER_STDSTANDORT']!='') {
                                  if( !isset($xtb_config['MODULE_XTBOOSTER_STDSTANDORT']) )
                                    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_XTBOOSTER_STDSTANDORT', '".$_POST['MODULE_XTBOOSTER_STDSTANDORT']."', '6', '1', '', now())");
                                  else
                                    xtc_db_query("update " . TABLE_CONFIGURATION . " SET configuration_value = '".$_POST['MODULE_XTBOOSTER_STDSTANDORT']."' WHERE configuration_key = 'MODULE_XTBOOSTER_STDSTANDORT'");
                                }
                                // MODULE_XTBOOSTER_STDPLZ
                                if(@$_POST['MODULE_XTBOOSTER_STDPLZ']!='') {
                                  if( !isset($xtb_config['MODULE_XTBOOSTER_STDPLZ']) )
                                    xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) values ('MODULE_XTBOOSTER_STDPLZ', '".$_POST['MODULE_XTBOOSTER_STDPLZ']."', '6', '1', '', now())");
                                  else
                                    xtc_db_query("update " . TABLE_CONFIGURATION . " SET configuration_value = '".$_POST['MODULE_XTBOOSTER_STDPLZ']."' WHERE configuration_key = 'MODULE_XTBOOSTER_STDPLZ'");
                                }
                                $requestx = "ACTION: EmailTemplateSave
                                             TEMPLATES_LANGUAGE: -=".base64_encode($_POST['MODULE_XTBOOSTER_TEMPLATES_LANGUAGE'])."
                                             KIND_OF_EMAIL: -=".base64_encode($_POST['KIND_OF_EMAIL'])."
                                             BCC_TO_SHOP: -=".base64_encode($_POST['MODULE_XTBOOSTER_EMAIL_BCC'])."
                                             TEMPLATE_SUBJECT: -=".base64_encode(trim($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE_SUBJECT']))."
                                             TEMPLATE_FROM: -=".base64_encode(trim($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE_FROM']))."
                                             TEMPLATE_FROM_NAME: -=".base64_encode(trim($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_NAME']))."
                                             TEMPLATE_FROM_ADDR: -=".base64_encode(trim($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_ADDR'],"<> "))."
                                             TEMPLATE: -=".base64_encode($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE'])."
                                             TEMPLATE_HTML: -=".base64_encode($_POST['MODULE_XTBOOSTER_EMAILTEMPLATE_HTML'])."
                                            ";
                                $res = $xtb->exec($requestx);
                                $res = $xtb->parse($res);
                                $requestx = "ACTION: TradeTemplateSave
                                             TEMPLATE: -=".base64_encode($_POST['MODULE_XTBOOSTER_TRADETEMPLATE'])."
                                             HITCOUNTER: ".$_POST['MODULE_XTBOOSTER_STDHITCOUNTER']."
                                             STOCKWARNING: ".$_POST['MODULE_XTBOOSTER_STOCKWARNING']."
                                             PAYMENTMETHODS: ".(@implode(",",$_POST['MODULE_XTBOOSTER_STDPAYMENTMETHODS']))."
                                             PAYPAL_ADDRESS: -=".base64_encode($_POST['MODULE_XTBOOSTER_STDPAYPAL_ADDRESS'])."
                                             CHANGE_QTYS: ".$_POST['MODULE_XTBOOSTER_CHANGEQUANTITY']."
                                             REDIRECT_TO: ".$_POST['MODULE_XTBOOSTER_REDIRECT']."
                                             DEFAULT_CUSTOMER_GROUP: ".$_POST['MODULE_XTBOOSTER_DEFAULTCUSTOMERGROUP']."
                                             DEFAULT_EBAY_SITE: ".$_POST['MODULE_XTBOOSTER_DEFAULTEBAYSITE']."
                                             DEFAULT_COUNTRY: ".$_POST['MODULE_XTBOOSTER_DEFAULTCOUNTRY']."
                                             DEFAULT_DISPATCH_TIME_MAX: ".$_POST['MODULE_XTBOOSTER_DISPATCHTIMEMAX']."
                                             DEFAULT_RETURNS_WITHIN: ".$_POST['MODULE_XTBOOSTER_RETURNSWITHIN']."
                                             VATPERCENT: ".$_POST['MODULE_XTBOOSTER_VATPERCENT']."
                                             MULTI_ONLYONSTOCK: ".$_POST['MODULE_XTBOOSTER_MULTIONLYONSTOCK']."
                                             MULTI_REVERSECATS: ".$_POST['MODULE_XTBOOSTER_MULTIREVERSECATS']."
                                            ";
                                $res = $xtb->exec($requestx);
                                $res = $xtb->parse($res);
                                if ($res['RESULT'] == 'FAILURE') {
                                  echo '<div style="font-weight:bold;color:#f00">'.$res['ERROR_MSG'].'</div>';
                                }
                                $xtb->config();
                              }
                              $emailtemplate='';
                              $subject='';
                              $from='';
                              // E-Mail Template abrufen..
                              $requestx = "ACTION:  EmailTemplateFetch";
                              $res = $xtb->exec($requestx);
                              $res = $xtb->parse($res);
                              if($res['ERROR_CODE']==4002) {
                                echo "<div class='smallText' style='padding:10px;color:white;background:red;font-weight:bold;'>".TXT_SHOPKEY_FAILURE."</div>";
                                unset($xtb_config['MODULE_XTBOOSTER_SHOPKEY']);
                              } elseif( !isset($xtb_config['MODULE_XTBOOSTER_SHOPKEY']) || trim($xtb_config['MODULE_XTBOOSTER_SHOPKEY']) == '' ) {
                                echo "<div class='smallText' style='padding:10px;color:white;background:red;font-weight:bold;'>".TXT_SHOPKEY_DOESNT_EXISTS."</div>";
                              } else {
                                $TEMPLATES_LANGUAGE = $res['TEMPLATES_LANGUAGE'];
                                $KIND_OF_EMAIL = $res['KIND_OF_EMAIL'];
                                $BCC_TO_SHOP = $res['BCC_TO_SHOP'];
                                $subject = $res['TEMPLATE_SUBJECT'];
                                $from = $res['TEMPLATE_FROM'];
                                $from_name = $res['TEMPLATE_FROM_NAME'];
                                $from_addr = $res['TEMPLATE_FROM_ADDR'];
                                $emailtemplate = $res['TEMPLATE'];
                                $emailtemplate_html = $res['TEMPLATE_HTML'];
                                // Ggf. $from in Name und Adresse aufteilen, falls nicht da
                                if(empty($from_addr)) {
                                  $from_arr = explode(' ',$from);
                                  if(1 == sizeof($from_arr)) {
                                    $from_name = ''; $from_addr = trim($from, "<> ");
                                  } else {
                                    $from_addr = $from_arr[sizeof($from_arr) - 1];
                                    $from_name = trim(substr($from,0,strlen($from)-strlen($from_addr)));
                                    $from_addr = trim($from_addr,"<> ");
                                  }
                                }
                                // Trade Template abrufen..
                                $requestx = "ACTION:TradeTemplateFetch";
                                $res = $xtb->exec($requestx);
                                $res = $xtb->parse($res);
                                $ebay_username = $res['EBAY_USERNAME'];
                                $tradetemplate = $res['TEMPLATE'];
                                $hitcounter = $res['HITCOUNTER'];
                                $stockwarning = $res['STOCKWARNING'];
                                $redirect = $res['REDIRECT_TO'];
                                $change_qtys = $res['CHANGE_QTYS'];
                                $paymentmethods = explode(",",$res['PAYMENTMETHODS']);
                                $paypal_address = $res['PAYPAL_ADDRESS'];
                                $latest_version = $res['LATEST_VERSION'];
                                $default_customer_group = $res['DEFAULT_CUSTOMER_GROUP'];
                                $supported_ebay_sites = unserialize($res['SUPPORTED_EBAY_SITES']);
                                $supported_countries = unserialize($res['SUPPORTED_COUNTRIES']);
                                $default_ebay_site = $res['DEFAULT_EBAY_SITE'];
                                $default_country = $res['DEFAULT_COUNTRY'];
                                $multi_onlyonstock = $res['MULTI_ONLYONSTOCK'];
                                $multi_reversecats = $res['MULTI_REVERSECATS'];
                                $dispatch_time_max = $res['DEFAULT_DISPATCH_TIME_MAX'];
                                $returns_within = $res['DEFAULT_RETURNS_WITHIN'];
                                $supported_dispatchtimes = unserialize($res['SUPPORTED_DISPATCH_TIME_MAX']);
                                $supported_returnswithin = unserialize($res['SUPPORTED_RETURNSWITHIN']);
                                $vatpercent = $res['VATPERCENT'];
                                $extra_features = unserialize($res['EXTRA_FEATURES']);
                                if(XTBOOSTER_VERSION!='#_version#')
                                  if($latest_version>XTBOOSTER_VERSION)
                                    echo "&nbsp;&nbsp;<div class='smallText' onclick='window.open(\"http://www.xtbooster.de/xtb/download\");' style='cursor:pointer;padding:2px;background-color:green;font-weight:bold;color:white;font-size:11px;margin-bottom:20px;'>".TXT_NEW_XTB_VERSION_AVAILABLE." [".TXT_CURRENT_XTB_VERSION.": ".$latest_version.", ".TXT_YOUR_XTB_VERSION.": ".XTBOOSTER_VERSION."]</div>";
                              }
                              ?>
                              <div id='content_title' style="font-family:arial;"><?php echo TXT_CONFIG?></div>
                              <?php
                              $nowysiwyg=true;
                              if(!preg_match("/safari/i",$_SERVER['HTTP_USER_AGENT'])) {
                                $nowysiwyg=false;
                                if($xtb_config['MODULE_XTBOOSTER_SHOPKEY']!='') {
                                  $out = xtc_wysiwyg('content_manager', 'de', $langID = ''); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                  echo $out = str_replace("cont", "MODULE_XTBOOSTER_TRADETEMPLATE", $out);
                                  // Fuer den Email-Editor:
                                  switch($KIND_OF_EMAIL) {
                                    case('html_email'):
                                      $html_mail_display='block';
                                      $txt_mail_display ='none';
                                      $email_display='';
                                      break;
                                    case('no_email'):
                                      $html_mail_display='none';
                                      $txt_mail_display ='none';
                                      $email_display='none';
                                      break;
                                    default:
                                      $html_mail_display='none';
                                      $txt_mail_display ='block';
                                      $email_display='';
                                      break;
                                  }
                                }
                              }
                              ?>
                              <script>
                                function in_array(needle,haystack) {
                                  for(var i=0;i<haystack.length;i++) if(needle==haystack[i]) return true;  return false;
                                }
                                function onChangeStdeBaySite(t) {
                                  switch(t.value) {
                                    case '77': // germany
                                      $('data_returnswithin').setStyle({'display':'none'});
                                      $('data_vatpercent').setStyle({'display':''});
                                      break;
                                    case '16': // austria
                                      $('data_vatpercent').setStyle({'display':''});
                                      break;
                                    case '192': // swiss
                                      $('data_vatpercent').setStyle({'display':''});
                                      break;
                                    default:
                                      $('data_returnswithin').setStyle({'display':''});
                                      $('data_vatpercent').setStyle({'display':'none'});
                                      break;
                                  }
                                  FetchPaymentMethods();
                                }
                                function FetchPaymentMethods() {
                                  var i;
                                  var EBAY_SITE = $('EBAY_SITE').value;
                                  $('PAYMENTMETHODS').update('');
                                  new Ajax.Updater("PAYMENTMETHODS", "xtbooster.php", { 
                                    method: 'post',
                                    onCreate: function(t) {
                                      xtb_dimensions();
                                      $('screen').setStyle({'display':''});
                                      $('pleasewait').setStyle({'display':'','top':(xtb_pageYOffset+((xtb_innerHeight/2)-40))+'px'});
                                      $$('html')[0].setStyle({'overflow':'hidden'}); $$('body')[0].setStyle({'overflow':'hidden'});
                                    },
                                    onLoaded: function(t) {
                                      $('screen').setStyle({'display':'none'});
                                      $('pleasewait').setStyle({'display':'none'});
                                      $$('html')[0].setStyle({'overflow':'auto'}); $$('body')[0].setStyle({'overflow':'auto'});
                                    },
                                    onComplete: function(transport) {
                                      var i=0;
                                      var paymentmethods = new Array();
                                      <?php
                                        foreach($paymentmethods as $k=>$v)
                                          echo "\t\t\t\tpaymentmethods[i++]='".$v."';\n";
                                      ?>
                                      for(i=$('PAYMENTMETHODS').options.length-1;i>=0;i--) {
                                        $('PAYMENTMETHODS').options[i].selected = in_array($('PAYMENTMETHODS').options[i].value,paymentmethods);
                                        if($('PAYMENTMETHODS').options[i].value=='PayPal'&&'<?php echo $paypal_address; ?>'=='') 
                                          $('PAYMENTMETHODS').remove(i);
                                      }
                                    },
                                    parameters: {
                                      xtb_module: 'FetchPaymentMethods',
                                      XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                      EBAY_SITE: EBAY_SITE
                                    }
                                  }
                                  );
                                }
                                function sendTestMail() {
                                  // die Fkt soll nur xtbooster mit xtb_module=SendTestMail
                                  // und mail-Art und Inhalt aufrufen
                                  new Ajax.Request("xtbooster.php", { 
                                    method: 'post',
                                    onCreate:   function(t) {
                                      $('BUTTON_SEND_TESTMAIL').disabled=true;
                                      $('TEXT_TESTMAIL_SENT').style.display='none';
                                    },
                                    onLoaded: function(t) {
                                    },
                                    onComplete: function(t) {
                                      $('BUTTON_SEND_TESTMAIL').disabled=false;
                                      $('TEXT_TESTMAIL_SENT').style.display='block';
                                    },
                                    parameters: {
                                      xtb_module: 'SendTestMail',
                                      XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                      KIND_OF_EMAIL: $('KIND_OF_EMAIL').value,
                                      FROM_NAME: $('MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_NAME').value,
                                      FROM_ADDR: $('MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_ADDR').value,
                                      SUBJECT: $('MODULE_XTBOOSTER_EMAILTEMPLATE_SUBJECT').value,
                                      MAIL_CONTENT: ($('KIND_OF_EMAIL').value=='html_email')?$('MODULE_XTBOOSTER_EMAILTEMPLATE_HTML').value:$('MODULE_XTBOOSTER_EMAILTEMPLATE').value
                                    }
                                  }
                                  );
                                }
                              </script>
                              <?php echo xtc_draw_form('xtbooster', 'xtbooster.php', '', 'post', 'enctype="multipart/form-data"'); ?>
                                <input type="hidden" name="xtb_module" value="conf" />
                                <table border="0" cellpadding="2" cellspacing="0" width="100%">
                                  <tr class="dataTableRow">
                                    <td colspan="3" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_AUTHORIZATION?></td>
                                  </tr>
                                  <tr class="dataTableRow">
                                    <td class="dataTableContent" style="width:190px;"><?php echo TXT_XTBSHOPKEY?>:</td>
                                    <td class="dataTableContent"><input type="text" name="MODULE_XTBOOSTER_SHOPKEY" value="<?php echo $xtb_config['MODULE_XTBOOSTER_SHOPKEY'] ?>" size="32" maxlength="32"></td>
                                    <td class="dataTableContent"><?php if($xtb_config['MODULE_XTBOOSTER_SHOPKEY']=='') echo TXT_SHOPKEY_DESCR?>&nbsp;</td>
                                  </tr>
                                  <?php
                                  if($xtb_config['MODULE_XTBOOSTER_SHOPKEY']!='') {
                                    ?>
                                    <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                      <td colspan="3" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_LOCATION?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_STANDORT?>:</td>
                                      <td class="dataTableContent"><input type="text" name="MODULE_XTBOOSTER_STDSTANDORT" value="<?php echo $xtb_config['MODULE_XTBOOSTER_STDSTANDORT'] ?>" size="32" maxlength="55"></td>
                                      <td class="dataTableContent"><?php echo TXT_STANDORT_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_STANDORTPLZ?>:</td>
                                      <td class="dataTableContent"><input type="text" name="MODULE_XTBOOSTER_STDPLZ" value="<?php echo $xtb_config['MODULE_XTBOOSTER_STDPLZ'] ?>" size="12" maxlength="12"></td>
                                      <td class="dataTableContent"><?php echo TXT_STANDORTPLZ_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_STANDORTCOUNTRY?>:</td>
                                      <td class="dataTableContent">
                                        <select name="MODULE_XTBOOSTER_DEFAULTCOUNTRY">
                                          <?php foreach($supported_countries as $k=>$v) { ?>
                                            <option value="<?php echo $k; ?>"<?php echo ($k==$default_country)?' selected="selected"':''; ?>><?php echo $v?></option>
                                          <?php } ?>
                                        </select>
                                      </td>
                                      <td class="dataTableContent">&nbsp;</td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_STDEBAYSITE?>:</td>
                                      <td class="dataTableContent">
                                        <select name="MODULE_XTBOOSTER_DEFAULTEBAYSITE" id='EBAY_SITE' onchange="onChangeStdeBaySite(this)">
                                          <?php foreach($supported_ebay_sites as $k=>$v) { ?>
                                            <option value="<?php echo $k; ?>"<?php echo ($k==$default_ebay_site)?' selected="selected"':''; ?>><?php echo $v['country']?></option>
                                          <?php } ?>
                                        </select>
                                      </td>
                                      <td class="dataTableContent">&nbsp;</td>
                                    </tr>
                                    <tr class="dataTableRow" id='data_vatpercent' style="display:<?php echo !in_array($default_ebay_site,array(77,16,192))?'none':''?>;">
                                      <td class="dataTableContent"><?php echo TXT_VATPERCENT?>:</td>
                                      <td class="dataTableContent"><input type="text" name="MODULE_XTBOOSTER_VATPERCENT" value="<?php echo $vatpercent; ?>" size="4" maxlength="5">%</td>
                                      <td class="dataTableContent"><?php echo TXT_VATPERCENT_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_DISPATCHTIMEMAX?>:</td>
                                      <td class="dataTableContent">
                                        <select name="MODULE_XTBOOSTER_DISPATCHTIMEMAX">
                                          <?php foreach($supported_dispatchtimes as $k=>$v) { ?>
                                            <option value="<?php echo $k; ?>"<?php echo ($k==$dispatch_time_max)?' selected="selected"':''; ?>><?php echo $v?></option>
                                          <?php } ?>
                                        </select>
                                      </td>
                                      <td class="dataTableContent">&nbsp;</td>
                                    </tr>
                                    <tr class="dataTableRow" id='data_returnswithin' style="display:<?php echo $default_ebay_site==77?'none':''?>;">
                                      <td class="dataTableContent"><?php echo TXT_RETURNSWITHIN?>:</td>
                                      <td class="dataTableContent">
                                        <select name="MODULE_XTBOOSTER_RETURNSWITHIN">
                                          <?php foreach($supported_returnswithin as $k=>$v) { ?>
                                            <option value="<?php echo $k; ?>"<?php echo ($k==$returns_within)?' selected="selected"':''; ?>><?php echo $v?></option>
                                          <?php } ?>
                                        </select>
                                      </td>
                                      <td class="dataTableContent">&nbsp;</td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent" style="vertical-align:top;"><?php echo TXT_PAYMENTMETHODS?>:</td>
                                      <td  class="dataTableContent">
                                        <select id='PAYMENTMETHODS' name="MODULE_XTBOOSTER_STDPAYMENTMETHODS[]" size="6" multiple></select>
                                        <div style="padding:3px;color:gray;"><?php echo TXT_MULTIPLECHOICE?></div>
                                        <div style="font-weight:bold;margin-top:3px;margin-bottom:2px;"><?php echo TXT_PAYPAL_ADDRESS?>:</div>
                                        <input type="text" name="MODULE_XTBOOSTER_STDPAYPAL_ADDRESS" value="<?php echo $paypal_address; ?>" size="30" maxlength="255">
                                      </td>
                                      <td class="dataTableContent">&nbsp;</td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td colspan="3" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_TEMPLATES?></td>
                                    </tr>
                                    <?php
                                    $desc_languages_query = xtc_db_query("SELECT DISTINCT l.code,l.name FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_LANGUAGES . " l WHERE pd.language_id = l.languages_id AND pd.products_description != ''");
                                    if(xtc_db_num_rows($desc_languages_query) > 1) {
                                      ?>
                                      <tr class="dataTableRow">
                                        <td class="dataTableContent"><?php echo TXT_TEMPLATES_LANGUAGE?>:</td>
                                        <td class="dataTableContent">
                                          <select name="MODULE_XTBOOSTER_TEMPLATES_LANGUAGE" id="MODULE_XTBOOSTER_TEMPLATES_LANGUAGE">
                                            <?php
                                            while($desc_languages = xtc_db_fetch_array($desc_languages_query)) { ?>
                                              <option value="<?php echo $desc_languages['code'] ?>"<?php echo ($desc_languages['code']==$TEMPLATES_LANGUAGE)?' selected="selected"':'' ?>><?php echo $desc_languages['name']?></option>
                                            <?php } ?>
                                          </select>
                                        </td>
                                        <td class="dataTableContent"><?php echo TXT_TEMPLATES_LANGUAGE_DESC?></td>
                                      </tr>
                                    <?php // ende if(xtc_db_num_rows > 1)
                                    } else {
                                      // descriptions nur in 1 Sprache vorhanden                                     
                                      $desc_languages = xtc_db_fetch_array($desc_languages_query);
                                      echo "<input type=\"hidden\" name=\"MODULE_XTBOOSTER_TEMPLATES_LANGUAGE\" id=\"MODULE_XTBOOSTER_TEMPLATES_LANGUAGE\" value= \"".$desc_languages['code']."\"/>";}
                                      ?>
                                      <tr class="dataTableRow">
                                        <td colspan="3" class="smallText" style="font-weight:bold;color:white;background:#1c4f8e;font-size:12px;padding:5px;"><?php echo TXT_EMAIL_TEMPLATE?></td>
                                      </tr>
                                      <tr id="TR_EMAIL_TEMPLATE" class="dataTableRow">
                                        <td class="dataTableContent" colspan="2">
                                          <table cellpadding="2" cellspacing="0" style="border:0px;width:100%;">
                                            <tr>
                                              <td class="dataTableContent"><strong><?php echo TXT_SEND?></strong></td>
                                              <td class="dataTableContent">
                                              <select name='KIND_OF_EMAIL' id='KIND_OF_EMAIL' onchange="
                                                          if(this.value=='html_email') {
                                                            $('DIV_TXT_MAIL').style.display='none'; 
                                                            $('DIV_HTML_MAIL').style.display='block';
                                                            $('TR_EMAILTEMPLATE_SUBJECT').style.display='';
                                                            $('TR_EMAILTEMPLATE_FROM_NAME').style.display='';
                                                            $('TR_EMAILTEMPLATE_FROM_ADDR').style.display='';
                                                            $('TR_EMAILTEMPLATE_MSG').style.display='';
                                                            $('TR_EMAILTEMPLATE_BCC').style.display='';
                                                            $('DIV_EMAIL_VARIABLES').style.display='';
                                                            $('DIV_EMAIL_SOURCECODE_NOTE').style.display='';
                                                            if(!emailFCKeditorCreated)
                                                            { emailFCKEditor.ReplaceTextarea(); emailFCKeditorCreated = true; }
                                                          } else if(this.value=='no_email') {
                                                            $('DIV_TXT_MAIL').style.display='none'; 
                                                            $('DIV_HTML_MAIL').style.display='none';
                                                            $('TR_EMAILTEMPLATE_SUBJECT').style.display='none';
                                                            $('TR_EMAILTEMPLATE_FROM_NAME').style.display='none';
                                                            $('TR_EMAILTEMPLATE_FROM_ADDR').style.display='none';
                                                            $('TR_EMAILTEMPLATE_MSG').style.display='none';
                                                            $('TR_EMAILTEMPLATE_BCC').style.display='none';
                                                            $('DIV_EMAIL_VARIABLES').style.display='none';
                                                            $('DIV_EMAIL_SOURCECODE_NOTE').style.display='none';
                                                          } else {
                                                            $('DIV_TXT_MAIL').style.display=''; 
                                                            $('DIV_HTML_MAIL').style.display='none';
                                                            $('TR_EMAILTEMPLATE_SUBJECT').style.display='';
                                                            $('TR_EMAILTEMPLATE_FROM_NAME').style.display='';
                                                            $('TR_EMAILTEMPLATE_FROM_ADDR').style.display='';
                                                            $('TR_EMAILTEMPLATE_MSG').style.display='';
                                                            $('TR_EMAILTEMPLATE_BCC').style.display='';
                                                            $('DIV_EMAIL_VARIABLES').style.display='';
                                                            $('DIV_EMAIL_SOURCECODE_NOTE').style.display='none';
                                                          }">
                                                <?php 
                                                if($extra_features['HtmlMail']){
                                                  ?>
                                                  <option <?php if('html_email'==$KIND_OF_EMAIL) echo "selected" ?> value='html_email'><?php echo TXT_HTML_EMAIL ?></option>
                                                  <?php
                                                }
                                                ?>
                                                <option <?php if(('text_email'==$KIND_OF_EMAIL) || empty($KIND_OF_EMAIL)) echo "selected" ?> value='text_email'><?php echo TXT_TEXT_EMAIL ?></option>
                                                <option <?php if('no_email'==$KIND_OF_EMAIL) echo "selected" ?> value='no_email'><?php echo TXT_NO_EMAIL ?></option>
                                              </select>
                                            </td>
                                          </tr>
                                          <tr id="TR_EMAILTEMPLATE_FROM_NAME" style="display:<?php echo $email_display?>">
                                            <td class="dataTableContent"><strong><?php echo TXT_EMAIL_TEMPLATE_FROM_NAME?>:</strong></td><td class="dataTableContent"><input size="40" maxlength="255" type="text" name="MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_NAME" id="MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_NAME" value="<?php echo encode_htmlspecialchars(stripslashes($from_name)); ?>"></td>
                                          </tr>
                                          <tr id="TR_EMAILTEMPLATE_FROM_ADDR" style="display:<?php echo $email_display?>">
                                            <td class="dataTableContent"><strong><?php echo TXT_EMAIL_TEMPLATE_FROM_ADDR?>:</strong></td><td class="dataTableContent"><input size="40" maxlength="255" type="text" name="MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_ADDR" id="MODULE_XTBOOSTER_EMAILTEMPLATE_FROM_ADDR" value="<?php echo encode_htmlspecialchars(stripslashes($from_addr)); ?>"></td>
                                          </tr>
                                          <tr id="TR_EMAILTEMPLATE_SUBJECT" style="display:<?php echo $email_display?>">
                                            <td class="dataTableContent"><strong><?php echo TXT_EMAIL_TEMPLATE_SUBJECT?>:</strong></td>
                                            <td class="dataTableContent"><input size="40" maxlength="255" type="text" name="MODULE_XTBOOSTER_EMAILTEMPLATE_SUBJECT" id="MODULE_XTBOOSTER_EMAILTEMPLATE_SUBJECT" value="<?php echo encode_htmlspecialchars(stripslashes($subject)); ?>"></td>
                                          </tr>
                                          <tr id="TR_EMAILTEMPLATE_MSG" style="display:<?php echo $email_display?>">
                                            <td class="dataTableContent" colspan="2"><strong><?php echo TXT_EMAIL_TEMPLATE_MSG?>:</strong></td>
                                          </tr>
                                          <tr>
                                            <td class="dataTableContent" colspan="2">
                                              <div id="DIV_TXT_MAIL" style="display:block"><textarea name='MODULE_XTBOOSTER_EMAILTEMPLATE' id='MODULE_XTBOOSTER_EMAILTEMPLATE' cols="80" rows="12" wrap="virtual"><?php echo encode_htmlspecialchars(stripslashes($emailtemplate)); ?></textarea></div>
                                              <div id="DIV_HTML_MAIL" style="display:block"><textarea name='MODULE_XTBOOSTER_EMAILTEMPLATE_HTML' id='MODULE_XTBOOSTER_EMAILTEMPLATE_HTML' cols="80" rows="12" wrap="virtual"><?php echo encode_htmlspecialchars(stripslashes($emailtemplate_html)); ?></textarea></div>
                                            </td>
                                            <script type="text/javascript">
                                              // erst hier, denn oben sind die divs & das Textarea nicht sichtbar
                                              // aber nicht inline, denn ein 'Create' ausserhalb dieses JS-Bereichs
                                              // (beim select der email-Art) funktioniert nicht.
                                              var emailFCKEditor = new FCKeditor('MODULE_XTBOOSTER_EMAILTEMPLATE_HTML','100%',320);
                                              emailFCKEditor.BasePath = "<?php echo DIR_WS_MODULES .'fckeditor/'?>";
                                              emailFCKEditor.Config["LinkBrowserURL"] = "<?php echo DIR_WS_ADMIN.'fck_wrapper.php?Connector='.DIR_WS_FILEMANAGER.'connectors/php/connector.php&ServerPath='. DIR_WS_CATALOG . '&Type=media' ?>";
                                              emailFCKEditor.Config["ImageBrowserURL"] = "<?php echo DIR_WS_ADMIN.'fck_wrapper.php?Connector='.DIR_WS_FILEMANAGER.'connectors/php/connector.php&ServerPath='. DIR_WS_CATALOG . '&Type=images' ?>";
                                              emailFCKEditor.Config["AutoDetectLanguage"] = false;
                                              emailFCKEditor.Config["DefaultLanguage"] = "de";
                                              <?php if('block' == $html_mail_display) {?>
                                                emailFCKEditor.ReplaceTextarea();
                                                emailFCKeditorCreated = true;
                                              <?php } else {?>
                                                emailFCKeditorCreated = false;
                                              <?php }?>
                                              $('DIV_HTML_MAIL').style.display='<?php echo $html_mail_display ?>';
                                              $('DIV_TXT_MAIL').style.display='<?php echo $txt_mail_display ?>'; 
                                            </script>
                                          </tr>
                                          <tr id="TR_EMAILTEMPLATE_BCC" style="display:<?php echo $email_display?>">
                                            <td class="dataTableContent"><strong><?php echo TXT_EMAIL_BCC_TO_SHOP?>:</strong></td>
                                            <td class="dataTableContent">
                                              <select name="MODULE_XTBOOSTER_EMAIL_BCC" id="MODULE_XTBOOSTER_EMAIL_BCC" <?php if(!$extra_features['changeBcc']) echo "disabled"; ?>>
                                                <option <?php if(('true'==$BCC_TO_SHOP) || empty($BCC_TO_SHOP)) echo "selected" ?> value='true'><?php echo TXT_YES ?></option>
                                                <?php if($extra_features['changeBcc']) { ?>
                                                  <option <?php if('false'==$BCC_TO_SHOP) echo "selected" ?> value='false'><?php echo TXT_NO ?></option>
                                                <?php } ?>
                                              </select>
                                            </td>
                                          </tr>
                                        </table>
                                      </td>
                                      <td valign="top" class="dataTableContent" style="vertical-align:top;">
                                        <div id="DIV_EMAIL_VARIABLES" style="display:<?php echo $email_display?>">
                                          <strong><?php echo TXT_VARIABLES?>:</strong><br /><br />
                                          #NAME# = <?php echo TXT_EMAIL_TEMPLATE_NAMEOFBUYER?><br />
                                          #ARTICLE_TITLE# = <?php echo TXT_ARTICLE_TITLE?><br />
                                          #ARTICLE_SUBTITLE# = <?php echo TXT_ARTICLE_SUBTITLE?><br />
                                          #ARTICLE_DESCRIPTION# = <?php echo TXT_TRADE_TEMPLATE_DESCRIPTION?><br />
                                          #ENDPRICE# = <?php echo TXT_EMAIL_AUCTIONED_PRICE?><br />
                                          #ARTICLE_NUMBER# = <?php echo TXT_LONG_ART_NO?><br />
                                          #ITEMID# = <?php echo TXT_EMAIL_TEMPLATE_AUCTIONNUMBER?><br />
                                          #ARTICLE_VPE# = <?php echo TXT_PACKAGING_UNIT?><br />
                                          <?php if($extra_features['PicturesInHtmlMail'] != 0) {?>
                                            #PICTURE_1# = <?php echo TXT_PICTURE_1?><br />
                                            #PICTURE_2# = <?php echo TXT_PICTURE_2?><br />
                                            #PICTURE_<strong>N</strong># = <?php echo TXT_PICTURE_N?><br />
                                          <?php } ?>
                                          <?php if((int)($extra_features['PicturesInHtmlMail'] != 0))
                                            echo "(".TXT_UP_TO.' '.$extra_features['PicturesInHtmlMail'].' '.TXT_PICTURES.")<br />\n";
                                          ?>
                                          #URL# = <?php echo TXT_EMAIL_TEMPLATE_ADDRESSOFSHOP?><br />
                                          <hr>
                                          <div id="DIV_EMAIL_SOURCECODE_NOTE" style="display:<?php echo $html_mail_display ?>"><?php echo TXT_NOTE_USE_BUTTON_SOURCECODE?></div>
                                          <br />
                                          <br />
                                          <br />
                                          <input type="BUTTON" value="<?php echo TXT_TESTMAIL?>" id="BUTTON_SEND_TESTMAIL" onclick="sendTestMail();" /><br />
                                          <br />
                                          <div id="TEXT_TESTMAIL_SENT" style="display:none"><?php echo TXT_TESTMAIL_SENT ?></div>
                                        </div>
                                        &nbsp;
                                      </td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td colspan="3" class="smallText" style="font-weight:bold;color:white;background:#1c4f8e;font-size:12px;padding:5px;"><?php echo TXT_TRADE_TEMPLATE?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent" colspan="2">
                                        <table cellpadding="2" cellspacing="0" style="border:0px;width:100%;">
                                          <tr>
                                            <td class="dataTableContent" colspan="2"><textarea name='MODULE_XTBOOSTER_TRADETEMPLATE' id='MODULE_XTBOOSTER_TRADETEMPLATE' cols="150" rows="10" wrap="virtual"><?php echo encode_htmlspecialchars($tradetemplate); ?></textarea></td>
                                          </tr>
                                        </table>
                                      </td>
                                      <td valign="top" class="dataTableContent" style="vertical-align:top;">
                                        <strong><?php echo TXT_VARIABLES?>:</strong><br /><br />
                                        #ARTICLE_TITLE# = <?php echo TXT_ARTICLE_TITLE?><br />
                                        #ARTICLE_SUBTITLE# = <?php echo TXT_ARTICLE_SUBTITLE?><br />
                                        #ARTICLE_DESCRIPTION# = <?php echo TXT_TRADE_TEMPLATE_DESCRIPTION?><br />
                                        #ARTICLE_PRICE# = <?php echo TXT_ARTICLE_PRICE?><br />
                                        #ARTICLE_NUMBER# = <?php echo TXT_LONG_ART_NO?><br />
                                        #ARTICLE_VPE# = <?php echo TXT_PACKAGING_UNIT?><br />
                                        #PICTURE_1# = <?php echo TXT_PICTURE_1?><br />
                                        #PICTURE_2# = <?php echo TXT_PICTURE_2?><br />
                                        #PICTURE_<strong>N</strong># = <?php echo TXT_PICTURE_N?><br />
                                        <hr>
                                        <?php echo TXT_NOTE_USE_BUTTON_SOURCECODE?>
                                      </td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td colspan="3" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_OTHERS?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_MULTIONLYONSTOCK ?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_MULTIONLYONSTOCK'>
                                          <option<?php if($multi_onlyonstock=='true') { echo " selected"; } ?> value='true'><?php echo TXT_YES ?></option>
                                          <option<?php if($multi_onlyonstock=='false') { echo " selected"; } ?> value='false'><?php echo TXT_NO ?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_MULTIONLYONSTOCK_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_MULTIREVERSECATS ?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_MULTIREVERSECATS'>
                                          <option<?php if($multi_reversecats=='true') { echo " selected"; } ?> value='true'><?php echo TXT_YES ?></option>
                                          <option<?php if($multi_reversecats=='false') { echo " selected"; } ?> value='false'><?php echo TXT_NO ?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_MULTIREVERSECATS_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_HITCOUNTER?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_STDHITCOUNTER'>
                                          <option<?php if($hitcounter=='NoHitCounter') { echo " selected"; } ?> value='NoHitCounter'><?php echo TXT_NO_COUNTER?></option>
                                          <option<?php if($hitcounter=='BasicStyle') { echo " selected"; } ?> value='BasicStyle'><?php echo TXT_STANDARD_COUNTER?></option>
                                          <option<?php if($hitcounter=='GreenLED') { echo " selected"; } ?> value='GreenLED'><?php echo TXT_GREEN_LED_COUNTER?></option>
                                          <option<?php if($hitcounter=='HiddenStyle') { echo " selected"; } ?> value='HiddenStyle'><?php echo TXT_HIDDEN_COUNTER?></option>
                                          <option<?php if($hitcounter=='RetroStyle') { echo " selected"; } ?> value='RetroStyle'><?php echo TXT_RETRO_COUNTER?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_HITCOUNTER_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_STOCKWARNING?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_STOCKWARNING'>
                                          <option<?php if($stockwarning=='true') { echo " selected"; } ?> value='true'><?php echo TXT_ACTIVE ?></option>
                                          <option<?php if($stockwarning=='false') { echo " selected"; } ?> value='false'><?php echo TXT_DEACTIVE ?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_STOCKWARNING_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td colspan="3" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_AFTERBUY?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_REDIRECTOR?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_REDIRECT'>
                                          <option<?php if($redirect=='basket') { echo " selected"; } ?> value='basket'><?php echo TXT_REDIRECT_BASKET ?></option>
                                          <option<?php if($redirect=='product') { echo " selected"; } ?> value='product'><?php echo TXT_REDIRECT_PRODUCT ?></option>
                                          <option<?php if($redirect=='create_account') { echo " selected"; } ?> value='create_account'><?php echo TXT_REDIRECT_CREATE_ACCOUNT ?></option>
                                          <option<?php if($redirect=='create_guest_account') { echo " selected"; } ?> value='create_guest_account'><?php echo TXT_REDIRECT_CREATE_GUEST_ACCOUNT ?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_REDIRECTOR_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_CHANGEQUANTITY?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_CHANGEQUANTITY'>
                                          <option<?php if($change_qtys=='true') { echo " selected"; } ?> value='true'><?php echo TXT_YES ?></option>
                                          <option<?php if($change_qtys=='false') { echo " selected"; } ?> value='false'><?php echo TXT_NO ?></option>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_CHANGEQUANTITY_DESCR?></td>
                                    </tr>
                                    <tr class="dataTableRow">
                                      <td class="dataTableContent"><?php echo TXT_DEFAULT_CUSTOMER_GROUP?>:</td>
                                      <td class="dataTableContent">
                                        <select name='MODULE_XTBOOSTER_DEFAULTCUSTOMERGROUP'>
                                          <option>++ <?php echo TXT_PLEASECHOOSE?> ++</option>
                                          <?php
                                          $customer_status = xtc_db_query("SELECT * FROM " . TABLE_CUSTOMERS_STATUS . " WHERE language_id=".(int)$_SESSION['languages_id']." AND customers_status_id!=0");
                                          while($d=xtc_db_fetch_array($customer_status)) {
                                            ?>
                                            <option<?php if($default_customer_group==$d['customers_status_id']) { echo " selected"; } ?> value='<?php echo $d['customers_status_id'] ?>'><?php echo utf8_encode($d['customers_status_name']); ?></option>
                                            <?php
                                          }
                                          ?>
                                        </select>
                                      </td>
                                      <td class="dataTableContent"><?php echo TXT_DEFAULT_CUSTOMER_GROUP_DESCR?></td>
                                    </tr>
                                    <?php
                                  }
                                  ?>
                                </table>
                                <?php
                                if($xtb_config['MODULE_XTBOOSTER_SHOPKEY']!='') {
                                  ?>
                                  <script> FetchPaymentMethods(); </script>
                                  <?php
                                }
                                ?>
                                <br/>
                                <input type='submit' value='<?php echo BTN_SAVE?>' />
                                <br/>
                                <br/>
                              </form>
                              <?php
                              break;
                            case 'RelistItem':
                              $jobs = $_POST['items'];
                              ?>
                              <div id='jso_relist'>
                                <div class="smallText" style="font-size:arial;font-size:11px;padding:4px;border-bottom:1px solid white;">
                                  <div id='moment_relist' style="font-weight:bold;font-size:13px;margin-bottom:5px;"><?php echo TXT_BE_PATIENT_WHILE_SUBMITTING_AUCTIONS?></div>
                                  <div id='status_relist' style="font-weight:bold;color:green;margin-bottom:5px;"><?php echo TXT_ZERO_OF.' '.sizeof($jobs).' '.TXT_AUCTIONS_SUBMITTED.' (0%)'?></div>
                                </div>
                              </div>
                              <script>
                                Effect.Pulsate('moment_relist', { pulses: 3, duration: 7 });
                                var c=0;
                                var requests = new Array();
                                var callbacks = 0;
                                var callback_errors = 0;
                                function callback(response) {
                                  if(response.search(/SUCCESS/)==-1) {
                                    $('status_relist').style.color="orange";
                                    callback_errors++;
                                  } else {
                                    callbacks++;
                                  }
                                  p = (callbacks+callback_errors)/(requests.length/100);
                                  $('status_relist').update((callbacks+callback_errors)+" <?php echo TXT_OF?> "+requests.length+" <?php echo TXT_AUCTIONS_SUBMITTED?> ("+p+"%) - ("+callback_errors+" <?php echo TXT_FAILED?>)")
                                  if(p==100) {
                                    if(callback_errors==0) {
                                      $('status_relist').update("<?php echo TXT_ALL_AUCTIONS_RELISTED?>");
                                      $('moment_relist').update("<?php echo TXT_CONGRATULATIONS?>");
                                    } else if(callbacks==0) {
                                      $('status_relist').style.color="red";
                                      $('status_relist').update("<?php echo TXT_ERROR_NO_AUCTIONS_RELISTED?>");
                                      $('moment_relist').update("<?php echo TXT_WRONG_DATA?>!");
                                    } else {
                                      $('status_relist').style.color="orange";
                                      $('status_relist').update("<?php echo TXT_ONLY?> "+callbacks+" <?php echo TXT_OF?> "+(callbacks+callback_errors)+" <?php echo TXT_AUCTIONS_RELISTED  ?>!");
                                      $('moment_relist').update("<?php echo TXT_WARNING_NOT_ALL_AUCTIONS_RELISTED?>!");
                                    }
                                  }
                                }
                                function putall() {
                                  for(var i=0;i<requests.length;i++) {
                                    new Ajax.Updater("jso_relist", "xtbooster.php", { 
                                      method: 'post',
                                      onSuccess: function(transport) { 
                                        callback(transport.responseText);
                                      },
                                      parameters: {
                                        request: requests[i],
                                        xtb_module: 'relist_ajx'
                                      },
                                      insertion: Insertion.Bottom
                                    } );
                                  }
                                }
                                <?php
                                $divid=0;
                                foreach($jobs as $item)  {
                                  ?>
                                  requests[<?php echo $divid?>]='<?php echo base64_encode(serialize($item)); ?>';
                                  <?php
                                  $divid++;
                                }
                                ?>
                                putall();
                              </script>
                              <?php  
                              break;
                            case 'add':
                              if(isset($_POST['add'])) {
                              }
                              ?>
                              <?php
                              if(!isset($_POST['add'])||@$r['RESULT']!='SUCCESS') {
                                ?>
                                <div id='content_title' style="font-family:arial;"><?php echo TXT_EBAYPRODUCTS_ADD?></div>
                                <?php
                                if($_POST['current_product_id']==''&&$_GET['mode']!='multi_xtb') {
                                  ?>
                                  <div style='padding:8px;'><a href="categories.php" style='font-size:11px;line-height:18px;'><?php echo TXT_SHORTADDINSTRUCTIONS?></a></div>
                                  <?php
                                } else {
                                  $multi_xtb = ($_GET['mode']=='multi_xtb')?true:false;
                                  $any_products_quantity = 1;
                                  if($multi_xtb) {
                                    $multi_products = $_SESSION['xtb1']['multi_xtb'];
                                    $items = array();
                                    $any_description_too_long = false;
                                    foreach($multi_products as $v) {
                                      $products_query = xtc_db_query("SELECT products_quantity FROM " . TABLE_PRODUCTS . " as p, " . TABLE_PRODUCTS_DESCRIPTION . " as pd WHERE p.products_id = pd.products_id AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND p.products_id = '".$v."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                      $x = xtc_db_fetch_array($products_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                      if($x['products_quantity']<1)
                                        $any_products_quantity = $x['products_quantity'];
                                      if(strlen(trim($x['products_short_description'])) > 54) 
                                        $any_description_too_long = true;
                                      $items[$v] = $v;
                                    }
                                    $multi_products = $items;
                                    if(sizeof($items)==1) {
                                      $multi_xtb=false;
                                      list($_POST['current_product_id']) = each($items);
                                    }
                                  }
                                  if(!$multi_xtb) {
                                    $products_query = xtc_db_query("SELECT * FROM " . TABLE_PRODUCTS . " as p, " . TABLE_PRODUCTS_DESCRIPTION . " as pd left join ".TABLE_PRODUCTS_IMAGES." as pi ON (pi.products_id = pd.products_id) WHERE p.products_id = pd.products_id AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND p.products_id = '".$_POST['current_product_id']."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                    $images = array();
                                    $x = xtc_db_fetch_array($products_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                    // Weitere Produkt-Bilder
                                    $images[0]=$x['products_image'];
                                    if($x['image_nr']!='')
                                      $images[$x['image_nr']]=$x['image_name'];
                                    while($x1 = xtc_db_fetch_array($products_query))
                                      $images[$x1['image_nr']] = $x1['image_name']; 
                                    if($_SESSION['language_charset']!='utf-8')
                                      foreach($x as $key=>$value)
                                        $x[$key] = utf8_encode($value);
                                  }
                                  // Store-Info abrufen
                                  $requestx = "ACTION:  UserHaseBayStore";
                                  $resx = $xtb->exec($requestx);
                                  $resx = $xtb->parse($resx);
                                  $hasebaystore = $resx['HASEBAYSTORE'];
                                  $ebaystoreurl = $resx['EBAYSTOREURL'];
                                  if($hasebaystore) {
                                    $requestx = "ACTION:  UserGeteBayStoreName";
                                    $resx = $xtb->exec($requestx);
                                    $resx = $xtb->parse($resx);
                                    $ebaystorename = $resx['EBAYSTORENAME'];
                                    //  print_r($resx);
                                  }
                                  // Trade Template abrufen..
                                  $requestx = "ACTION:  TradeTemplateFetch";
                                  $resx = $xtb->exec($requestx);
                                  $resx = $xtb->parse($resx);
                                  $tradetemplate = $resx['TEMPLATE'];
                                  $stockwarning = $resx['STOCKWARNING'];
                                  $hitcounter = $resx['HITCOUNTER'];
                                  $redirect = $resx['REDIRECT_TO'];
                                  $change_qtys = $resx['CHANGE_QTYS'];
                                  $paymentmethods = explode(",",$resx['PAYMENTMETHODS']);
                                  $paypal_address = $resx['PAYPAL_ADDRESS'];
                                  $latest_version = $resx['LATEST_VERSION'];
                                  $default_customer_group = $resx['DEFAULT_CUSTOMER_GROUP'];
                                  $supported_ebay_sites = unserialize($resx['SUPPORTED_EBAY_SITES']);
                                  $supported_countries = unserialize($resx['SUPPORTED_COUNTRIES']);
                                  $default_ebay_site = $resx['DEFAULT_EBAY_SITE'];
                                  $default_country = $resx['DEFAULT_COUNTRY'];
                                  $dispatch_time_max = $resx['DISPATCH_TIME_MAX'];
                                  $returns_within = $resx['RETURNS_WITHIN'];
                                  $supported_dispatchtimes = unserialize($resx['SUPPORTED_DISPATCH_TIME_MAX']);
                                  $supported_returnswithin = unserialize($resx['SUPPORTED_RETURNSWITHIN']);
                                  $vatpercent = $resx['VATPERCENT'];
                                  require '../includes/configure.php'; // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                  ?>
                                  <script type="text/javascript">
                                    function toggleCurrencies(x) {
                                      var cur = $('CURRENCY');
                                      var spl = x.split(",");
                                      var y = spl[1].split("|");
                                      while(cur.length)
                                        cur.remove(0);
                                      if(y.length) 
                                        for(var i=0;i<y.length;i++) 
                                          cur.appendChild(new Element('option', { 'value':y[i] }).update(y[i]));
                                      else 
                                        cur.appendChild(new Element('option', { 'value':spl[1] }).update(spl[1]));
                                    }
                                    function FetchListingDurationOptions(v) {
                                      var EBAY_SITE = $F('EBAY_SITE');
                                      EBAY_SITE = EBAY_SITE.split(",");
                                      $('ListingDurationOptions').update('');
                                      new Ajax.Updater("ListingDurationOptions", "xtbooster.php", { 
                                        method: 'post',
                                        onCreate: function(t) {
                                          xtb_dimensions();
                                          $('screen').setStyle({'display':''});
                                          $('pleasewait').setStyle({'display':'','top':(xtb_pageYOffset+((xtb_innerHeight/2)-40))+'px'});
                                          $$('html')[0].setStyle({'overflow':'hidden'}); $$('body')[0].setStyle({'overflow':'hidden'});
                                        },
                                        onLoaded: function(t) {
                                          $('screen').setStyle({'display':'none'});
                                          $('pleasewait').setStyle({'display':'none'});
                                          $$('html')[0].setStyle({'overflow':'auto'}); $$('body')[0].setStyle({'overflow':'auto'});
                                        },
                                        parameters: {
                                          xtb_module: 'FetchListingDurationOptions',
                                          XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                          EBAY_SITE: EBAY_SITE[0],
                                          TYPE: v
                                        },
                                        insertion: Insertion.Bottom
                                      }
                                      );
                                      if(v!='Chinese') {
                                        $('BUYITNOW_ACTIVE').checked=false;
                                        $('BUYITNOWROW').setStyle({'display':'none'});
                                      } else {
                                        $('BUYITNOWROW').setStyle({'display':''});
                                      }
                                      if(v!='FixedPriceItem' && v!='StoresFixedPrice') {
                                        $('BEST_OFFER').value=0;
                                        $('BEST_OFFER_ROW').setStyle({'display':'none'});
                                      } else {
                                        $('BEST_OFFER_ROW').setStyle({'display':''});
                                      }
                                    }
                                    function FetchShippingDetails() {
                                      var EBAY_SITE = $F('EBAY_SITE');
                                      EBAY_SITE = EBAY_SITE.split(",");
                                      new Ajax.Request("xtbooster.php", { 
                                        method: 'post',
                                        onCreate: function(t) {
                                          xtb_dimensions();
                                          $('screen').setStyle({'display':''});
                                          $('pleasewait').setStyle({'display':'','top':(xtb_pageYOffset+((xtb_innerHeight/2)-40))+'px'});
                                          $$('html')[0].setStyle({'overflow':'hidden'}); $$('body')[0].setStyle({'overflow':'hidden'});
                                        },
                                        onLoaded: function(t) {
                                          $('screen').setStyle({'display':'none'});
                                          $('pleasewait').setStyle({'display':'none'});
                                          $$('html')[0].setStyle({'overflow':'auto'}); $$('body')[0].setStyle({'overflow':'auto'});
                                        },
                                        onComplete: function(transport) {
                                          var o = transport.responseText.split("||");
                                          //var shippingtype_prefill=o[0];
                                          //shippingtype0_prefill = shippingtype_prefill.replace(/option value=\'DE_COD\|\'/,"option selected value='DE_COD|'");
                                          //$('SHIPPINGTYPE').update(shippingtype0_prefill);
                                          $('SHIPPINGTYPE').update(o[0]);
                                          $('SHIPPINGTYPE1').update(o[0]);
                                          $('SHIPPINGTYPE2').update(o[0]);
                                          $('SHIPPINGTYPE3').update(o[0]);
                                          $('SHIPPINGTYPE4').update(o[0]);
                                          $('SHIPPINGTYPE5').update(o[0]);
                                          $('SHIPTOLOCATIONS').update(o[1]);
                                          $('SHIPTOLOCATIONS1').update(o[1]);
                                          $('SHIPTOLOCATIONS2').update(o[1]);
                                          $('SHIPTOLOCATIONS3').update(o[1]);
                                          $('SHIPTOLOCATIONS4').update(o[1]);
                                          $('SHIPTOLOCATIONS5').update(o[1]);
                                        },
                                        parameters: {
                                          xtb_module: 'FetchShippingDetails',
                                          XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                          EBAY_SITE: EBAY_SITE[0]
                                        }
                                      }
                                      );
                                    }
                                    function in_array(needle,haystack) {
                                      for(var i=0;i<haystack.length;i++) if(needle==haystack[i]) return true;  return false;
                                    }
                                    function FetchPaymentMethods() {
                                      var i;
                                      var EBAY_SITE = $F('EBAY_SITE');
                                      EBAY_SITE = EBAY_SITE.split(",");
                                      $('PAYMENTMETHODS').update('');
                                      new Ajax.Updater("PAYMENTMETHODS", "xtbooster.php", { 
                                        method: 'post',
                                        onCreate: function(t) {
                                          xtb_dimensions();
                                          $('screen').setStyle({'display':''});
                                          $('pleasewait').setStyle({'display':'','top':(xtb_pageYOffset+((xtb_innerHeight/2)-40))+'px'});
                                          $$('html')[0].setStyle({'overflow':'hidden'}); $$('body')[0].setStyle({'overflow':'hidden'});
                                        },
                                        onLoaded: function(t) {
                                          $('screen').setStyle({'display':'none'});
                                          $('pleasewait').setStyle({'display':'none'});
                                          $$('html')[0].setStyle({'overflow':'auto'}); $$('body')[0].setStyle({'overflow':'auto'});
                                        },
                                        onComplete: function(transport) {
                                          var i=0;
                                          var paymentmethods = new Array();
                                          <?php
                                          foreach($paymentmethods as $k=>$v)
                                            echo "\t\t\t\tpaymentmethods[i++]='".$v."';\n"; 
                                          ?>
                                          for(i=$('PAYMENTMETHODS').options.length-1;i>=0;i--) {
                                            $('PAYMENTMETHODS').options[i].selected = in_array($('PAYMENTMETHODS').options[i].value,paymentmethods);
                                            if($('PAYMENTMETHODS').options[i].value=='PayPal'&&'<?php echo $paypal_address; ?>'=='') 
                                              $('PAYMENTMETHODS').remove(i);
                                          }
                                        },
                                        parameters: {
                                          xtb_module: 'FetchPaymentMethods',
                                          XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                          EBAY_SITE: EBAY_SITE[0]
                                        }
                                      }
                                      );
                                    }
                                    function FetchAttributes(mode)  {
                                      var EBAY_SITE = $F('EBAY_SITE');
                                      EBAY_SITE = EBAY_SITE.split(",");
                                      // Einstellungen für Kategorie 2 (mode=2) oder Kategorie 1 (sonst) setzen.
                                      if (mode==2)  {
                                        var TableName = 'TABLE_ATTRIBUTES_2';
                                        var AttributesListFieldName  = 'ATTRIBUTES2';
                                        var CatFieldName = document.getElementById('CAT_SECONDARY');
                                      } else {
                                        var TableName = 'TABLE_ATTRIBUTES';
                                        var CatFieldName = document.getElementById('CAT_PRIMARY');
                                        var AttributesListFieldName  = 'ATTRIBUTES1';
                                      }
                                      var AttributesTableName = document.getElementById(TableName);
                                      if(Prototype.Browser.IE) 
                                        AttributesTableName = AttributesTableName.firstChild;
                                      if ( AttributesTableName.hasChildNodes() )
                                        while ( AttributesTableName.childNodes.length >= 1 )
                                          AttributesTableName.removeChild( AttributesTableName.firstChild );       
                                      new Ajax.Request("xtbooster.php", { 
                                        method: 'post',
                                        onCreate: function(t) {
                                          xtb_dimensions();
                                          $('screen').setStyle({'display':''});
                                          $('pleasewait').setStyle({'display':'','top':(xtb_pageYOffset+((xtb_innerHeight/2)-40))+'px'});
                                          $$('html')[0].setStyle({'overflow':'hidden'}); $$('body')[0].setStyle({'overflow':'hidden'});
                                        },
                                        onLoaded: function(t) {
                                          $('screen').setStyle({'display':'none'});
                                          $('pleasewait').setStyle({'display':'none'});
                                          $$('html')[0].setStyle({'overflow':'auto'}); $$('body')[0].setStyle({'overflow':'auto'});
                                        },
                                        onComplete: function(transport) {
                                          var r = transport.responseText.split('||');
                                          if(r[1]=='')
                                            return;
                                          var allAttributes = new Array();
                                          var category_id = r[0];
                                          // Überschriftenzeile erzeugen
                                          if ((r.length>2) || ((r[1].length>0) && (r[1]!="1")))  {
                                            var attributeHeaderRow = new Element('tr', {'class':'attributes-even'});
                                            var attributeHeaderCol = new Element('td', {'class':'smallText','colspan':'2','style':'font-weight:bold;font-size:12px;color:white;padding:10px;background:#555'}).update("<?php echo EXTRA_ATTRIBUTES_FOR_CATEGORY?> "+category_id+":");
                                            attributeHeaderRow.appendChild(attributeHeaderCol);
                                            AttributesTableName.appendChild(attributeHeaderRow);
                                          }
                                          var c = 0;
                                          for (var i=1;i<r.length;i++)  {
                                            attribute = r[i].split("//");
                                            var attributeID = attribute[0];
                                            var attributeName = attribute[1];
                                            var attributeValues = attribute[2];
                                            // ID in Liste aufnehmen
                                            allAttributes.push(attributeID);
                                            // Tabelle um eine Zeile erweitern
                                            var attributeRow = new Element('tr',{'class':(i%2==0?'attributes-even':'attributes-odd')});
                                            var attributeCol1 = new Element('td',{'class':'smallText','style':'font-weight:bold;'}).update(attributeName);
                                            var attributeCol2 = new Element('td',{'class':'smallText'});
                                            if (attributeValues.length)  {
                                              // Es sind vorgegebene Werte zu dem Attribut vorhanden => Dropdown
                                              attributeValues = attributeValues.split("|");              
                                              var attributeInputField = new Element('select', { 'name':'add[ATTRIBUTES' + mode + '][' + attributeID + ']', 'id':'ATTRIBUTES' + mode + '[' + attributeID + ']' });
                                              attributeCol2.appendChild(attributeInputField);
                                              var j = 0;
                                              for (j=0;j<attributeValues.length;j++)  {
                                                attributeValue = attributeValues[j];
                                                attributeValue = attributeValue.split("::");
                                                if (attributeValue.length==2)  {  // Gibt es wirklich ein Name-ID-Paar?
                                                  var attributeOptionField = new Element('option', { 'class': 'smallText', 'value':attributeValue[0] }).update(attributeValue[1]);
                                                  attributeInputField.appendChild(attributeOptionField);
                                                }
                                              }
                                            } else  {
                                              // Es gibt keine vorgegebenen Werte => Eingabefeld
                                              var attributeInputField = new Element('input', { 'type':'text','name':'add[ATTRIBUTES' + mode + '][' + attributeID + ']', 'id':'ATTRIBUTES' + mode + '[' + attributeID + ']'  });
                                              attributeCol2.appendChild(attributeInputField);
                                            }    
                                            attributeRow.appendChild(attributeCol1);
                                            attributeRow.appendChild(attributeCol2);
                                            AttributesTableName.appendChild(attributeRow);
                                            c++;
                                          }  // Ende der Attribut-Schleife
                                          // Tabelle einblenden
                                          if(c>0) {
                                            // Attributliste anfügen
                                            var attributeListField = document.createElement('input');
                                            attributeListField.setAttribute('name', AttributesListFieldName);
                                            attributeListField.setAttribute('type', 'hidden');
                                            $('DIV_ATTRIBUTES').appendChild(attributeListField);
                                            attributeListField.value=allAttributes.join(",");
                                          }
                                        },
                                        parameters: {
                                          xtb_module: 'FetchAttributes',
                                          XTB_VERSION: '<?php echo XTBOOSTER_VERSION?>',
                                          CATEGORY_ID: CatFieldName.value,
                                          EBAY_SITE: EBAY_SITE[0]
                                        }
                                      }
                                      );
                                    }
                                    function onChangeEbaySite(x) {
                                      var c = x.split(",");
                                      toggleCurrencies(x);
                                      FetchPaymentMethods();
                                      FetchShippingDetails();
                                      FetchAttributes(1);
                                      FetchAttributes(2);
                                      toggleAdditionalPrices($('QUANTITY').value);
                                    }
                                    function onChangeCategory1(category_id)  {
                                      FetchAttributes(1);
                                    }
                                    function onChangeCategory2(category_id)  {
                                      FetchAttributes(2);
                                    }  
                                    function onChangeAuctionType(x) {    
                                      FetchListingDurationOptions(x);
                                    }
                                    function toggleAdditionalPrices(t) {
                                      var i=0;
                                      var a = new Array();
                                      a[0] = $('_SHIPPINGSERVICEADDITIONALCOST');
                                      a[1] = $('_SHIPPINGSERVICEADDITIONALCOST1');
                                      a[2] = $('_SHIPPINGSERVICEADDITIONALCOST2');
                                      a[3] = $('_SHIPPINGSERVICEADDITIONALCOST3');
                                      a[4] = $('_SHIPPINGSERVICEADDITIONALCOST4');
                                      a[5] = $('_SHIPPINGSERVICEADDITIONALCOST5');
                                      if(t>1) {
                                        for(i=0;i<a.length;i++)
                                          a[i].setStyle({'display':''});
                                      } else {
                                        for(i=0;i<a.length;i++)
                                          a[i].setStyle({'display':'none'});
                                      }
                                      <?php
                                      if(!$multi_xtb&&$stockwarning=='true') {
                                        ?>
                                        if(<?php echo $x['products_quantity']?><t)
                                          alert("<?php echo TXT_WARNING_AMOUNT_OF_ITEMS?> ("+t+") <?php echo TXT_ABOVE_STOCK?> (<?php echo $x['products_quantity']?>).");
                                        <?php
                                      }
                                      ?>
                                    }
                                  </script>
                                  <?php
                                  if($stockwarning=='true') {
                                    if(!$multi_xtb&&$x['products_quantity']<1) {
                                      echo "&nbsp;&nbsp;<div class='smallText' style='font-weight:bold;color:red;font-size:13px;margin-bottom:20px;'>".TXT_ITEM_OUT_OF_STOCK."</div>";
                                    } elseif($multi_xtb&&$any_products_quantity<1) {
                                      echo "&nbsp;&nbsp;<div class='smallText' style='font-weight:bold;color:red;font-size:13px;margin-bottom:20px;'>".TXT_ONE_OF_ITEMS_OUT_OF_STOCK."</div>";
                                    }
                                  }
                                  if($multi_xtb && $any_description_too_long)
                                    echo "&nbsp;&nbsp;<div class='smallText' style='font-weight:bold;color:red;font-size:13px;margin-bottom:20px;'>".TXT_ONE_OF_DESCRIPTIONS_TOO_LONG."</div>";
                                  if(XTBOOSTER_VERSION!='#_version#')
                                    if($latest_version>XTBOOSTER_VERSION)
                                      echo "&nbsp;&nbsp;<div class='smallText' onclick='window.open(\"http://www.xsbooster.com/xtb/download\");' style='cursor:pointer;padding:2px;background-color:green;font-weight:bold;color:white;font-size:11px;margin-bottom:20px;'>".TXT_NEW_XTB_VERSION_AVAILABLE." [".TXT_CURRENT_XTB_VERSION.": ".$latest_version.", ".TXT_YOUR_XTB_VERSION.": ".XTBOOSTER_VERSION."]</div>";
                                  ?>
                                  <script>
                                    var TXT_PLEASE_CHOOSE_AUCTION_TYPE="<?php echo TXT_PLEASE_CHOOSE_AUCTION_TYPE?>";
                                    var TXT_ONE_UNIT_PER_CHINESE_AUCTION="<?php echo TXT_ONE_UNIT_PER_CHINESE_AUCTION?>";
                                    var TXT_AT_LEAST_TWO_UNITS_PER_DUTCH_AUCTION="<?php echo TXT_AT_LEAST_TWO_UNITS_PER_DUTCH_AUCTION?>";
                                    var TXT_PLEASE_CHOOSE_PRIMARY_CATEGORY="<?php echo TXT_PLEASE_CHOOSE_PRIMARY_CATEGORY?>";
                                    var TXT_PLEASE_SET_SHIPPING_COSTS="<?php echo TXT_PLEASE_SET_SHIPPING_COSTS?>";
                                    var TXT_PLEASE_SET_START_PRICE="<?php echo TXT_PLEASE_SET_START_PRICE?>";
                                    var TXT_PLEASE_USE_POINT_NOT_COMMA="<?php echo TXT_PLEASE_USE_POINT_NOT_COMMA?>";
                                    var TXT_BE_PATIENT_WHILE_SUBMITTING_AUCTIONS="<?php echo TXT_BE_PATIENT_WHILE_SUBMITTING_AUCTIONS?>";
                                    var TXT_OF="<?php echo TXT_OF?>";
                                    var TXT_ZERO_OF="<?php echo TXT_ZERO_OF?>";
                                    var TXT_AUCTIONS_SUBMITTED="<?php echo TXT_AUCTIONS_SUBMITTED?>";
                                    var TXT_FAILED="<?php echo TXT_FAILED?>";
                                    var TXT_ALL_AUCTIONS_SUBMITTED="<?php echo TXT_ALL_AUCTIONS_SUBMITTED?>";
                                    var TXT_CONGRATULATIONS="<?php echo TXT_CONGRATULATIONS?>";
                                    var TXT_ERROR_NO_AUCTIONS_SUBMITTED="<?php echo TXT_ERROR_NO_AUCTIONS_SUBMITTED?>";
                                    var TXT_WRONG_DATA="<?php echo TXT_WRONG_DATA?>";
                                    var TXT_WARNING_NOT_ALL_AUCTIONS_SUBMITTED="<?php echo TXT_WARNING_NOT_ALL_AUCTIONS_SUBMITTED?>";
                                  </script>
                                    <?php echo xtc_draw_form('sd', 'xtbooster.php', '', 'post', 'id="xsb_add_form" onsubmit="xsb.post(this);return false;"'); ?>
                                    <input type="hidden" name="xtb_module" id="xtb_module" value="add">
                                    <input type="hidden" name="current_product_id" id="current_product_id" value="<?php echo $_POST['current_product_id']?>">
                                    <input type="hidden" name="multi_xtb" id="multi_xtb" value="<?php echo $multi_xtb?'1':'0'; ?>">
                                    <?php
                                    $rowi=0; 
                                    ?>
                                    <link rel="stylesheet" type="text/css" media="all" href="includes/xsbooster/calendar-win2k-cold-1.css" />
                                    <script type="text/javascript" src="includes/xsbooster/calendar.js"></script>
                                    <script type="text/javascript" src="includes/xsbooster/calendar-en.js"></script>
                                    <script type="text/javascript" src="includes/xsbooster/calendar-setup.js"></script>
                                    <?php 
                                    if(!$multi_xtb) {
                                      $nowysiwyg=true;
                                      if(!preg_match("/safari/i",$_SERVER['HTTP_USER_AGENT'])) {
                                        $nowysiwyg=false;
                                        if($xtb_config['MODULE_XTBOOSTER_SHOPKEY']!='') {
                                          $out = xtc_wysiwyg('content_manager', 'de', $langID = ''); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                          echo $out = str_replace("cont", "add[DESCRIPTION]", $out);
                                          ?>
                                          <script type="text/javascript"></script>
                                          <?php
                                        }
                                      }
                                    }
                                    ?>
                                    <div id='debug'></div>
                                    <table border="0" cellpadding="2" cellspacing="0" width="100%" id="TABLE_MAIN">
                                      <?php
                                      if(!$multi_xtb) {
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="width:200px;font-weight:bold;" valign="top"><?php echo TXT_ARTNO?>:</td>
                                          <td class="smallText"><?php echo $x['products_model']?></td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_TITLE?>:</td>
                                          <td class="smallText"><input type="text" name="add[TITLE]" value="<?php echo encode_htmlspecialchars(strip_tags($x['products_name']))?>" size="30" maxlength="55"></td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;"valign="top"><?php echo TXT_SUBTITLE?>:</td>
                                          <td class="smallText">
                                            <input type="text" name="add[SUBTITLE]" id="SUBTITLE" value="<?php echo ($products_short_description=strip_tags($x['products_short_description']))?>" size="30" maxlength="54">
                                            <input type="checkbox" value="1" name="add[SUBTITLE_USE]" id="SUBTITLE_USE" <?php if($products_short_description!='') { echo ' checked="checked"'; } ?> /> <?php echo TXT_SHORTDESC_WARNING ?>
                                          </td>
                                        </tr>
                                        <?php
                                        if(strlen(strip_tags($x['products_name'])) > 79) {
                                          ?>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText">&nbsp;</td>
                                            <td class="smallText" style="font-weight:bold;color:red"valign="top"><?php echo TXT_WARN_TITLE_TOO_LONG?></td>
                                          </tr>
                                          <?php
                                        }
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_DESCRIPTION?>:</td>
                                          <td class="smallText">
                                            <?php
                                            $desc = $tradetemplate;
                                            if(preg_match("/#ARTICLE_DESCRIPTION#/",$desc)) {
                                              $desc = str_replace("#ARTICLE_DESCRIPTION#", $x['products_description'], $desc);
                                            }
                                            if(preg_match("/#ARTICLE_TITLE#/",$desc)) {
                                              $desc = str_replace("#ARTICLE_TITLE#", $x['products_name'], $desc);
                                            }
                                            if(preg_match("/#ARTICLE_SUBTITLE#/",$desc)) {
                                              $desc = str_replace("#ARTICLE_SUBTITLE#", $x['products_short_description'], $desc);
                                            }
                                            if(preg_match("/#ARTICLE_NUMBER#/",$desc)) {
                                              $desc = str_replace("#ARTICLE_NUMBER#", $x['products_model'], $desc);
                                            }
                                            foreach($images as $pi=>$image) {
                                              $pi++;
                                              if(preg_match("/src *= *\"*#PICTURE_".$pi."#\"*/", $desc)) {
                                                if((0 === strpos($image,'http://'))||(0 === strpos($image,'https://'))) {
                                                  $desc = str_replace("#PICTURE_".$pi."#", $image, $desc);
                                                } else {
                                                  $desc = str_replace("#PICTURE_".$pi."#", HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES."$image" , $desc);
                                                }
                                              } elseif(preg_match("/#PICTURE_".$pi."#/", $desc)) {
                                                if((0 === strpos($image,'http://'))||(0 === strpos($image,'https://'))) {
                                                  $desc = str_replace("#PICTURE_".$pi."#", "<img src=\"".$image."\" style=\"border:0;\" alt=\"\" title=\"\" />", $desc);
                                                } else {
                                                  $desc = str_replace("#PICTURE_".$pi."#", "<img src=\"".HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image."\" style=\"border:0;\" alt=\"\" title=\"\" />", $desc);
                                                }
                                              }
                                            }
                                            // Relative Bildnamen aus der Produktbescheibung oder Template mit der Shop-URL versehen
                                            if (preg_match('#src=(?![\'"]?(?:https?:)?//)([\'"])?#', $desc)) {
                                              $desc=preg_replace('#src=(?![\'"]?(?:https?:)?//)([\'"])?\/#', 'src=$1'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG, $desc); 
                                              $desc=preg_replace('#src=(?![\'"]?(?:https?:)?//)([\'"])?#', 'src=$1'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG, $desc); 
                                            }
                                            // $desc = encode_htmlspecialchars($desc);
                                            echo xtc_draw_textarea_field('add[DESCRIPTION]', 'soft', '103', '20', $desc); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                            //echo "<script type= \"text/javascript\"> $('add[DESCRIPTION]').value = \"".addslashes($desc)."\"; </script>\n";
                                            ?>
                                            <?php
                                            if($nowysiwyg) { ?>
                                              <div style="padding:3px;color:gray;"><?php echo TXT_HTMLALLOWED?></div>
                                              <?php
                                            }
                                            ?>
                                            <div style="padding:3px;color:gray;"><?php echo TXT_YOU_MAY_INSERT_MORE_PICTURES_HERE?></div>
                                          </td>
                                        </tr>
                                        <?php
                                      } else {
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_QUANTITY?>:</td>
                                          <td class="smallText"><?php echo sizeof($multi_products); ?> </td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_TITLE?>:</td>
                                          <td class="smallText"><em><?php echo TXT_AUTOMATIC?></em></td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_SUBTITLE?>:</td>
                                          <td class="smallText"><input type="checkbox" name="add[AUTO_SUBTITLE]" id="AUTO_SUBTITLE" value="1"><?php echo TXT_SHORTDESC_AUTO?></td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_DESCRIPTION?>:</td>
                                          <td class="smallText"><em><?php echo TXT_AUTOMATIC?></em></td>
                                        </tr>
                                        <?php
                                      }
                                      ?>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_EBAYSITE?>:</td>
                                        <td class="smallText">
                                          <?php $currencies = array(); ?>
                                          <select name="add[EBAY_SITE]" id="EBAY_SITE" onchange="onChangeEbaySite(this.value);">
                                            <?php
                                            foreach($supported_ebay_sites as $k=>$v) { 
                                              ?>
                                              <option value="<?php echo $k; ?>,<?php echo implode("|",$v['currencies']); ?>" <?php echo ($k==$default_ebay_site)?' selected="selected"':''; ?>><?php echo $v['country']?></option>
                                              <?php
                                            }
                                            ?>
                                          </select>    
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_AUCTIONTYPE?>:</td>
                                        <td class="smallText">
                                          <select name="add[TYPE]" id='TYPE' style="width:250px;" onchange="onChangeAuctionType(this.value);">
                                            <option value="">++ <?php echo TXT_PLEASECHOOSE?> ++</option>
                                            <option value="FixedPriceItem"<?php echo $_POST['add']['TYPE']=='FixedPriceItem'?' SELECTED':''; ?>><?php echo TXT_FIXPRICEAUCTION?></option>
                                            <option value="Chinese"<?php echo $_POST['add']['TYPE']=='Chinese'?' SELECTED':''; ?>><?php echo TXT_CHINESEAUCTION?></option>
                                            <option value="Dutch"<?php echo $_POST['add']['TYPE']=='Dutch'?' SELECTED':''; ?>><?php echo TXT_DUTCHAUCTION?></option>
                                            <?php if($hasebaystore) { ?>
                                              <option value="StoresFixedPrice"><?php echo TXT_STOREFIXEDPRICE?></option>
                                            <?php } ?>
                                          </select>
                                          <?php if($_POST['add']['TYPE']!='') { ?>
                                            <script>
                                              FetchListingDurationOptions('<?php echo $_POST['add']['TYPE'];?>');
                                            </script>
                                          <?php } ?>
                                        </td>
                                      </tr>
                                      <tr id='xCAT_PRIMARY' class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_CAT_PRIMARY?>:</td>
                                        <td class="smallText">
                                          <input type="text" name="add[CAT_PRIMARY]" id='CAT_PRIMARY' name='CAT_PRIMARY' value="<?php echo $_POST['add']['CAT_PRIMARY']; ?>" size="10" maxlength="10" onblur="onChangeCategory1(this.value);">
                                          <input type="hidden" name="add[CAT_PRIMARY_DESCR]" id='CAT_PRIMARY_DESCR' name='CAT_PRIMARY_DESCR' value="">
                                          <input type="button" value="<?php echo TXT_CHOOSE?>" onclick="window.open('xtbooster.php?xtb_module=cats&id=CAT_PRIMARY&EBAY_SITE='+$('EBAY_SITE').value, 'categoryChoose', 'width=400,height=500,left=50,top=50,resizable=true,scrollbars=yes');" />
                                          <?php
                                          $requestx = "ACTION:  FetchFavoriteCategories\nTYPE: XTB_EBAY_CAT_PRIMARY\n";
                                          $resx = $xtb->exec($requestx);
                                          $resx = $xtb->parse($resx);
                                          $categories = unserialize($resx['CATEGORIES']);
                                          if(sizeof($categories)) {
                                            ?>
                                            &nbsp;<?php echo TXT_OR?>&nbsp;
                                            <select style='width:140px;' name="dummy1" onchange="$('CAT_PRIMARY').value = this.value!=''?this.value:''; onChangeCategory1(this.value);">
                                              <option value=""><?php echo TXT_TOP10?></option><option value="">--------------------</option>
                                              <?php
                                              foreach($categories as $categoryid=>$categoryname) {
                                                ?>
                                                <option value="<?php echo $categoryid?>"><?php echo $categoryname?></option>
                                                <?php
                                              }
                                              ?>
                                            </select>  
                                            <?php
                                          }
                                          ?>
                                        </td>
                                      </tr>
                                      <tr id='xCAT_SECONDARY' class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_CAT_SECONDARY?>:</td>
                                        <td class="smallText">
                                          <input type="text" name="add[CAT_SECONDARY]" id='CAT_SECONDARY' name='CAT_SECONDARY' value="<?php echo $_POST['add']['CAT_SECONDARY']; ?>" size="10" maxlength="10" onblur="onChangeCategory2(this.value);">
                                          <input type="hidden" name="add[CAT_SECONDARY_DESCR]" id='CAT_SECONDARY_DESCR' name='CAT_SECONDARY_DESCR' value="">
                                          <input type="button" value="<?php echo TXT_CHOOSE?>" onclick="window.open('xtbooster.php?xtb_module=cats&id=CAT_SECONDARY&EBAY_SITE='+$('EBAY_SITE').value, 'categoryChoose', 'width=400,height=500,left=50,top=50,resizable=true,scrollbars=yes');" />
                                          <?php
                                          $requestx = "ACTION:  FetchFavoriteCategories\nTYPE: XTB_EBAY_CAT_SECONDARY\n";
                                          $resx = $xtb->exec($requestx);
                                          $resx = $xtb->parse($resx);
                                          $categories = unserialize($resx['CATEGORIES']);
                                          if(sizeof($categories)) {
                                            ?>
                                            &nbsp;<?php echo TXT_OR?>&nbsp;
                                            <select style='width:140px;' name="dummy2" onchange="$('CAT_SECONDARY').value = this.value!=''?this.value:''; onChangeCategory2(this.value);">
                                              <option value=""><?php echo TXT_TOP10?></option>
                                              <option value="">--------------------</option>
                                              <?php
                                              foreach($categories as $categoryid=>$categoryname) {
                                                ?>
                                                <option value="<?php echo $categoryid?>"><?php echo $categoryname?></option>
                                                <?php
                                              }
                                              ?>
                                            </select>  
                                            <?php
                                          }
                                          ?>
                                          <div style="padding:3px;color:gray;"><?php echo TXT_OPTIONAL?></div>
                                        </td>
                                      </tr>
                                      <?php
                                      if($hasebaystore) {
                                        ?>
                                        <tr id='xCAT_STORE_PRIMARY' class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;color:orange;"><?php echo TXT_CAT_STORE_PRIMARY?>:</td>
                                          <td class="smallText"><input type="text" name="add[CAT_STORE_PRIMARY]" id='CAT_STORE_PRIMARY' value="<?php echo $_POST['add']['CAT_STORE_PRIMARY']; ?>" size="10" maxlength="10">
                                            <input type="button" value="<?php echo TXT_CHOOSE?>" onclick="window.open('xtbooster.php?xtb_module=cats&id=CAT_STORE_PRIMARY&EBAY_SITE='+$('EBAY_SITE').value, 'categoryChoose', 'width=400,height=500,left=50,top=50,resizable=true,scrollbars=yes');" />
                                            <div style="padding:3px;color:gray;"><?php echo TXT_OPTIONAL?></div>
                                          </td>
                                        </tr>
                                        <tr id='xCAT_STORE_SECONDARY' class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;color:orange;"><?php echo TXT_CAT_STORE_SECONDARY?>:</td>
                                          <td class="smallText"><input type="text" name="add[CAT_STORE_SECONDARY]" id='CAT_STORE_SECONDARY' value="<?php echo $_POST['add']['CAT_STORE_SECONDARY']; ?>" size="10" maxlength="10">
                                            <input type="button" value="<?php echo TXT_CHOOSE?>" onclick="window.open('xtbooster.php?xtb_module=cats&id=CAT_STORE_SECONDARY&EBAY_SITE='+$('EBAY_SITE').value, 'categoryChoose', 'width=400,height=500,left=50,top=50,resizable=true,scrollbars=yes');" />
                                            <div style="padding:3px;color:gray;"><?php echo TXT_OPTIONAL?></div>
                                          </td>
                                        </tr>
                                        <?php
                                      }
                                      ?>
                                      <?php
                                      if(!$multi_xtb) {
                                        $im = array();
                                        foreach($images as $image) {
                                          if(substr(DIR_WS_CATALOG_POPUP_IMAGES,-1)!='/'&&$image[0]!='/') {
                                            $im[] = HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES."/".$image;
                                          } else {
                                            $im[] = HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$image;
                                          }
                                        }
                                        reset($images);
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;"><?php echo TXT_PICTUREURL?>:</td>
                                          <!-- Schauen dass absolute Adressen auch uebernommen werden + beruecksichtigen dass eBay https nicht nimmt-->
                                          <td class="smallText"><input type="text" name="add[PICTUREURL]" value="<?php if($_POST['add']['PICTUREURL']!='') { echo $_POST['add']['PICTUREURL']; } elseif($x['products_image']!='') { if((0 === strpos($x['products_image'],'http://'))||(0 === strpos($x['products_image'],'https://'))) echo str_replace('https','http',$x['products_image']); else echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$x['products_image']; } ?>" size="50" maxlength="255"></td>
                                        </tr>
                                        <?php 
                                      } else {
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_PICTUREURL?>:</td>
                                          <td class="smallText"><em><?php echo TXT_AUTOMATIC?></em></td>
                                        </tr>
                                        <?php
                                      }
                                      ?>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_AUCTION_STARTS_ON?></td>
                                        <td class="smallText">
                                          <input type="text" name="add[SCHEDULETIME]" id='f_trigger_c' value="<?php echo ($_POST['add']['SCHEDULETIME']!='')?$_POST['add']['SCHEDULETIME']:strftime(TIME_FORMAT); ?>" size="25" maxlength="25" onfocus="this.select();lcs(this)" onclick="event.cancelBubble=true;this.select();lcs(this)">
                                          <script type="text/javascript">
                                            Calendar.setup({
                                              inputField     :    "f_trigger_c",     // id of the input field
                                              ifFormat       :    "<?php echo TIME_FORMAT?>",      // format of the input field
                                              showsTime      :    true,
                                              timeFormat     :    "24",    
                                              button         :    "f_trigger_c",  // trigger for the calendar (button ID)
                                              align          :    "Bl",           // alignment (defaults to "Bl")
                                              singleClick    :    true
                                            });
                                          </script>
                                          <br />
                                          <div style="padding:3px;color:gray;"><?php echo TXT_WARNING_SCHEDULETIME?></div>
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_BUYER_BIDDER_LIST?>:</td>
                                        <td class="smallText">
                                          <select name='add[PRIVATE_LISTING]'>
                                            <option value='0'><?php echo TXT_PUBLIC?></option>
                                            <option value='1'><?php echo TXT_PRIVATE?></option>
                                          </select>
                                        </td>
                                      </tr>
                                      <?php
                                      if(!$multi_xtb) {
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;"><?php echo TXT_STARTPRICE?>:</td>
                                          <?php
                                          $tax_query = xtc_db_query("select tax_rate from " . TABLE_TAX_RATES . " where tax_class_id = '".$x['products_tax_class_id']."'"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                          $tax = xtc_db_fetch_array($tax_query); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                          $price = $x['products_price']*($tax['tax_rate']+100)/100;
                                          ?>
                                          <td class="smallText"><input type="text" name="add[STARTPRICE]" id="STARTPRICE" value="<?php echo ($_POST['add']['STARTPRICE']!='')?$_POST['add']['STARTPRICE']:round($price,2);?>" size="10" maxlength="10"> (<?php echo TXT_IE?> 9999.99)</td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" id='BUYITNOWROW' style='display:none;'>
                                          <td class="smallText" style="font-weight:bold;"><input type="checkbox" value="1" name="add[BUYITNOW_ACTIVE]" id="BUYITNOW_ACTIVE"> <strong style="color:green;"><?php echo TXT_BUYITNOWPRICE?></span>:</td>
                                          <td class="smallText"><input type="text" name="add[BUYITNOWPRICE]" id="BUYITNOWPRICE" value="<?php echo ($_POST['add']['BUYITNOWPRICE']!='')?$_POST['add']['BUYITNOWPRICE']:round($price,2);?>" size="10" maxlength="10"> (<?php echo TXT_IE?> 9999.99)</td>
                                        </tr>
                                        <?php
                                      } else {
                                        ?>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_STARTPRICE?>:</td>
                                          <td class="smallText"><em><strong><?php echo TXT_SHOPPRICE_DISCOUNT?> <input size="3" maxlength="3" type="text" name="add[STARTPRICE_DISCOUNT]" id="STARTPRICE_DISCOUNT" value="<?php echo ($_POST['add']['STARTPRICE_DISCOUNT']!='')?$_POST['add']['STARTPRICE_DISCOUNT']:'0';?>"> <?php echo TXT_SHOPPRICE_PERCENTEBAYDISCOUNT?></strong></em></td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" id='BUYITNOWROW' style='display:none;'>
                                          <td class="smallText" style="font-weight:bold;" valign="top"><input type="checkbox" value="1" name="add[BUYITNOW_ACTIVE]" id="BUYITNOW_ACTIVE"> <strong style="color:green;"><?php echo TXT_BUYITNOWPRICE?></span>:</td>
                                          <td class="smallText"><em><strong><?php echo TXT_SHOPPRICE_DISCOUNT?> <input size="3" maxlength="3" type="text" name="add[BUYITNOW_DISCOUNT]" id="BUYITNOW_DISCOUNT" value="<?php echo ($_POST['add']['BUYITNOW_DISCOUNT']!='')?$_POST['add']['BUYITNOW_DISCOUNT']:'0';?>"> <?php echo TXT_SHOPPRICE_PERCENTEBAYDISCOUNT?></strong></em></td>
                                        </tr>
                                        <?php
                                      }
                                      ?>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" id='BEST_OFFER_ROW' style='display:none;'>
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_BEST_OFFER?>:</td>
                                        <td class="smallText">
                                          <select name='add[BEST_OFFER]'>
                                            <option value='0'><?php echo TXT_NO?></option>
                                            <option value='1'><?php echo TXT_YES?></option>
                                          </select>
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_CURRENCY?>:</td>
                                        <td class="smallText">
                                          <select name="add[CURRENCY]" id="CURRENCY">
                                            <option value="EUR">EUR</option>
                                          </select>    
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_PAYMENTMETHODS?>:</td>
                                        <td class="smallText">
                                          <select id='PAYMENTMETHODS' name="add[PAYMENTMETHODS][]" size="6" multiple>
                                            <option value="PaymentSeeDescription"<?php echo (in_array("PaymentSeeDescription",$paymentmethods))?' selected="selected"':''; ?>><?php echo TXT_PM_PaymentSeeDescription?></option>
                                            <option value="CashOnPickup"<?php echo in_array("CashOnPickup",$paymentmethods)?' selected="selected"':'' ?>><?php echo TXT_PM_CashOnPickup?></option>
                                            <option value="COD"<?php echo in_array("COD",$paymentmethods)?' selected="selected"':'' ?>><?php echo TXT_PM_CashOnDelivery?></option>
                                            <option value="CCAccepted"<?php echo in_array("CCAccepted",$paymentmethods)?' selected="selected"':'' ?>><?php echo TXT_PM_CCAccepted?></option>
                                            <option value="MoneyXferAccepted"<?php echo in_array("MoneyXferAccepted",$paymentmethods)?' selected="selected"':'' ?>><?php echo TXT_PM_MoneyXferAccepted?></option>
                                            <option value="ELV"<?php echo in_array("ELV",$paymentmethods)?' selected="selected"':'' ?>><?php echo TXT_PM_ELV?></option>
                                            <?php
                                            if(trim($paypal_address)!='') {
                                              ?>
                                              <option value="PayPal"<?php echo in_array("PayPal",$paymentmethods)?' selected="selected"':'' ?>>PayPal (-&gt; <?php echo $paypal_address?>)</option>
                                              <?php
                                            }
                                            ?>
                                          </select>
                                          <?php 
                                          if(trim($paypal_address)!='') {
                                            ?>
                                            <input type="hidden" name="add[PAYPAL_ADDRESS]" value="<?php echo $paypal_address?>">
                                            <?php
                                          }
                                          ?>
                                          <div style="padding:3px;color:gray;"><?php echo TXT_MULTIPLECHOICE?></div>
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_QUANTITY?>:</td>
                                        <td class="smallText"><input type="text" name="add[QUANTITY]" id="QUANTITY" onblur='toggleAdditionalPrices(this.value);' value="<?php echo isset($request['QUANTITY'])?$request['QUANTITY']:'1'; ?>" size="3" maxlength="6"> (<?php echo TXT_WARNING_DUTCH?>)</td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_RUNTIME?>:</td>
                                        <td class="smallText">
                                          <div id='ListingDurationOptions'>++ <?php echo TXT_CHOOSEAUCTIONTYPEFIRST?> ++</div>
                                        </td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_STANDORT?>:</td>
                                        <td class="smallText"><input type="text" name="add[LOCATION]" value="<?php echo @$request['LOCATION']!=''?$request['LOCATION']:$xtb_config['MODULE_XTBOOSTER_STDSTANDORT']; ?>" size="30" maxlength="45"></td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;"><?php echo TXT_STANDORTPLZ?>:</td>
                                        <td class="smallText"><input type="text" name="add[POSTALCODE]" value="<?php echo @$request['POSTALCODE']!=''?$request['POSTALCODE']:$xtb_config['MODULE_XTBOOSTER_STDPLZ']; ?>" size="10" maxlength="10"></td>
                                      </tr>
                                      <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                        <td class="smallText" style="font-weight:bold;" valign="top"><?php echo TXT_HITCOUNTER?>:</td>
                                        <td class="smallText">
                                          <select name='add[HITCOUNTER]'>
                                            <option<?php if($hitcounter=='NoHitCounter') { echo " selected"; } ?> value='NoHitCounter'><?php echo TXT_NO_COUNTER?></option>
                                            <option<?php if($hitcounter=='BasicStyle') { echo " selected"; } ?> value='BasicStyle'><?php echo TXT_STANDARD_COUNTER?></option>
                                            <option<?php if($hitcounter=='GreenLED') { echo " selected"; } ?> value='GreenLED'><?php echo TXT_GREEN_LED_COUNTER?></option>
                                            <option<?php if($hitcounter=='HiddenStyle') { echo " selected"; } ?> value='HiddenStyle'><?php echo TXT_HIDDEN_COUNTER?></option>
                                            <option<?php if($hitcounter=='RetroStyle') { echo " selected"; } ?> value='RetroStyle'><?php echo TXT_RETRO_COUNTER?></option>
                                          </select>
                                        </td>
                                      </tr>
                                      <!-- Zusatzattributauswahl einblenden -->
                                    </table>
                                    <table border="0" cellpadding="2" cellspacing="0" width="100%" id="TABLE_ATTRIBUTES" style=""></table>
                                    <table border="0" cellpadding="2" cellspacing="0" width="100%" id="TABLE_ATTRIBUTES_2" style=""></table>
                                      <div id="DIV_ATTRIBUTES" style="display:none;"></div>
                                      <table border="0" cellpadding="2" cellspacing="0" width="100%" id="TABLE_MAIN2">
                                        <!-- / Zusatzattributauswahl -->
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td colspan="2" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;"><?php echo TXT_OPTION_GALLERY?>:</td>
                                        </tr>
                                        <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                          <td class="smallText" style="font-weight:bold;" align="right" valign="top"><input<?php echo (preg_match("/Gallery/i",$request['GALLERYTYPE']))?' checked="checked"':''; ?> type="checkbox" name="add[GALLERYTYPE]" id="GALLERYTYPE" value="Gallery"></td>
                                          <td class="smallText"><strong>Gallery</strong><div style='font-size:9px;color:gray;'><?php echo TXT_GALLERY_DESCR?></div></td>
                                        </tr>
                                        <?php
                                          if(!$multi_xtb) {
                                            ?>
                                            <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                              <td class="smallText" style="font-weight:bold;"><?php echo TXT_GALLERY_PICTURE?>:</td>
                                              <!-- Schauen dass absolute Adressen auch uebernommen werden + beruecksichtigen dass eBay https nicht nimmt-->
                                              <td class="smallText"><input type="text" name="add[GALLERY_PICTUREURL]" id="GALLERY_PICTUREURL" value="<?php if($x['products_image']!='') { if((0 === strpos($x['products_image'],'http://'))||(0 === strpos($x['products_image'],'https://'))) echo str_replace('https','http',$x['products_image']); else echo HTTP_CATALOG_SERVER.DIR_WS_CATALOG_POPUP_IMAGES.$x['products_image']; } ?>" size="50" maxlength="255"></td>
                                            </tr>
                                            <?php
                                          }
                                          ?>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td colspan="2" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;"><?php echo TXT_OPTION_GENERAL?>:</td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText" style="font-weight:bold;" align="right" valign="top"><input<?php echo (preg_match("/BoldTitle/i",$request['LISTINGENHANCEMENTS']))?' checked="checked"':''; ?> type="checkbox" name="add[LISTINGENHANCEMENTS]" id="LISTINGENHANCEMENTS" value="BoldTitle"></td>
                                            <td class="smallText"><strong>BoldTitle</strong><div style='font-size:9px;color:gray;'><?php echo TXT_BOLD_DESCR?></div></td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText" style="font-weight:bold;" align="right" valign="top"><input<?php echo (preg_match("/Border/i",$request['LISTINGENHANCEMENTS']))?' checked="checked"':''; ?> type="checkbox" name="add[LISTINGENHANCEMENTS]" id="LISTINGENHANCEMENTS" value="Border"></td>
                                            <td class="smallText"><strong>Border</strong><div style='font-size:9px;color:gray;'><?php echo TXT_BORDER_DESCR?></div></td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText" style="font-weight:bold;" align="right" valign="top"><input<?php echo (preg_match("/Highlight/i",$request['LISTINGENHANCEMENTS']))?' checked="checked"':''; ?> type="checkbox" name="add[LISTINGENHANCEMENTS]" id="LISTINGENHANCEMENTS" value="Highlight"></td>
                                            <td class="smallText"><strong>Highlight</strong><div style='font-size:9px;color:gray;'><?php echo TXT_HIGHLIGHT_DESCR?></div></td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText" style="font-weight:bold;" align="right" valign="top"><input<?php echo (preg_match("/Featured/i",$request['LISTINGENHANCEMENTS']))?' checked="checked"':''; ?> type="checkbox" name="add[LISTINGENHANCEMENTS]" id="LISTINGENHANCEMENTS" value="Featured"></td>
                                            <td class="smallText"><strong>Featured</strong><div style='font-size:9px;color:gray;'><?php echo TXT_FEATURED_DESCR?></div></td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td colspan="2" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;"><?php echo TXT_VERSAND?>:</td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE]' id='SHIPPINGTYPE' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS]' id='SHIPTOLOCATIONS' style='width:100px;'></select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS]' id='SHIPPINGCOSTS' size="10" maxlength="20" value="" title="<?php echo TXT_HOWTO_SET_SHIPPINGCOSTS_EQ_WEIGHT?>">&nbsp;(<?php echo TXT_EG?> 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST]' id='SHIPPINGSERVICEADDITIONALCOST' size="10" maxlength="10" value="">
                                              </span>
                                              &nbsp;&nbsp;<a href="JavaScript:void(0);" onclick='$("SHP1").style.display="";' style='text-decoration:underline;'><?php echo TXT_MORE_SHIPPINGTYPES?></a>
                                            </td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" style="display:none" id='SHP1'>
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE1]' id='SHIPPINGTYPE1' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS1").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS1'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS1]' id='SHIPTOLOCATIONS1' style='width:100px;'>
                                                </select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS1]' id='SHIPPINGCOSTS1' size="10" maxlength="20" value="">&nbsp;(z.B. 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST1'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST1]' id='SHIPPINGSERVICEADDITIONALCOST1' size="10" maxlength="10" value="">
                                              </span>
                                              &nbsp;&nbsp;<a href="JavaScript:void(0);" onclick='$("SHP2").style.display="";' style='text-decoration:underline;'><?php echo TXT_MORE_SHIPPINGTYPES?></a>
                                            </td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" style="display:none" id='SHP2'>
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE2]' id='SHIPPINGTYPE2' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS2").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS2'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS2]' id='SHIPTOLOCATIONS2' style='width:100px;'>
                                                </select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS2]' id='SHIPPINGCOSTS2' size="10" maxlength="20" value="">&nbsp;(z.B. 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST2'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST2]' id='SHIPPINGSERVICEADDITIONALCOST2' size="10" maxlength="10" value="">
                                              </span>
                                              &nbsp;&nbsp;<a href="JavaScript:void(0);" onclick='$("SHP3").style.display="";' style='text-decoration:underline;'><?php echo TXT_MORE_SHIPPINGTYPES?></a>
                                            </td>
                                          </tr>
                                          <!-- 3 -->
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" style="display:none" id='SHP3'>
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE3]' id='SHIPPINGTYPE3' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS3").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS3'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS3]' id='SHIPTOLOCATIONS3' style='width:100px;'>
                                                </select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS3]' id='SHIPPINGCOSTS3' size="10" maxlength="20" value="">&nbsp;(z.B. 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST3'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST3]' id='SHIPPINGSERVICEADDITIONALCOST3' size="10" maxlength="10" value="">
                                              </span>  
                                              &nbsp;&nbsp;<a href="JavaScript:void(0);" onclick='$("SHP4").style.display="";' style='text-decoration:underline;'><?php echo TXT_MORE_SHIPPINGTYPES?></a>
                                            </td>
                                          </tr>
                                          <!-- 4 -->
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" style="display:none" id='SHP4'>
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE4]' id='SHIPPINGTYPE4' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS4").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS4'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS4]' id='SHIPTOLOCATIONS4' style='width:100px;'>
                                                </select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS4]' id='SHIPPINGCOSTS4' size="10" maxlength="20" value="">&nbsp;(z.B. 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST4'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST4]' id='SHIPPINGSERVICEADDITIONALCOST4' size="10" maxlength="10" value="">
                                              </span>  
                                              &nbsp;&nbsp;<a href="JavaScript:void(0);" onclick='$("SHP5").style.display="";' style='text-decoration:underline;'><?php echo TXT_MORE_SHIPPINGTYPES?></a>
                                            </td>
                                          </tr>
                                          <!-- 5 -->
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>" style="display:none" id='SHP5'>
                                            <td class="smallText" style="font-weight:bold;" valign="top">
                                              <select name='add[SHIPPINGTYPE5]' id='SHIPPINGTYPE5' style='width:200px;' onchange='y=$("_SHIPTOLOCATIONS5").style;x=this.value.split("|");if(x[1]==1) y.display=""; else y.display="none";'>
                                              </select>
                                              <div style='display:none;vertical-align:top;' id='_SHIPTOLOCATIONS5'>
                                                &nbsp;&nbsp;<strong><?php echo TXT_SHIP_TO?></strong>
                                                <select name='add[SHIPTOLOCATIONS5]' id='SHIPTOLOCATIONS5' style='width:100px;'>
                                                </select>
                                              </div>
                                            </td>
                                            <td class="smallText">
                                              <?php echo TXT_SHIPPINGCOSTS?>: <input type="text" name='add[SHIPPINGCOSTS5]' id='SHIPPINGCOSTS5' size="10" maxlength="20" value="">&nbsp;(z.B. 3.50)&nbsp;
                                              <span style='display:none;' id='_SHIPPINGSERVICEADDITIONALCOST5'>
                                                &nbsp;<?php echo TXT_EACH_ONE_MORE?>: <input type="text" name='add[SHIPPINGSERVICEADDITIONALCOST5]' id='SHIPPINGSERVICEADDITIONALCOST5' size="10" maxlength="10" value="">
                                              </span>  
                                            <td>
                                          </tr>
                                          <script>onChangeEbaySite($('EBAY_SITE').value);</script>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td colspan="2" class="smallText" style="font-weight:bold;font-size:12px;color:white;background-color:#555;padding:10px;padding:10px;"><?php echo TXT_OPTION_CHECKOUT?>:</td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText"><?php echo TXT_REDIRECTOR?>:</td>
                                            <td class="smallText">
                                              <select name='add[REDIRECT_USER_TO]'>
                                                <option<?php if($redirect=='basket') { echo " selected"; } ?> value='basket'><?php echo TXT_REDIRECT_BASKET ?></option>
                                                <option<?php if($redirect=='product') { echo " selected"; } ?> value='product'><?php echo TXT_REDIRECT_PRODUCT ?></option>
                                                <option<?php if($redirect=='create_account') { echo " selected"; } ?> value='create_account'><?php echo TXT_REDIRECT_CREATE_ACCOUNT ?></option>
                                                <option<?php if($redirect=='create_guest_account') { echo " selected"; } ?> value='create_guest_account'><?php echo TXT_REDIRECT_CREATE_GUEST_ACCOUNT ?></option>
                                              </select>
                                            </td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText"><?php echo TXT_CHANGEQUANTITY?>:</td>
                                            <td class="smallText">
                                              <select name='add[ALLOW_USER_CHQTY]'>
                                                <option<?php if($change_qtys=='true') { echo " selected"; } ?> value='true'><?php echo TXT_YES ?></option>
                                                <option<?php if($change_qtys=='false') { echo " selected"; } ?> value='false'><?php echo TXT_NO ?></option>
                                              </select>
                                              <span style="color:gray;"><?php echo TXT_CHANGEQUANTITY_DESCR?></span>
                                            </td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText">&nbsp;</td>
                                            <td class="smallText">&nbsp;</td>
                                          </tr>
                                          <tr class="attributes<?php echo $rowi++%2==0?'-even':'-odd'; ?>">
                                            <td class="smallText">&nbsp;</td>
                                              <?php
                                              if(!$multi_xtb) {
                                                ?>
                                                <td class="smallText"><input type="submit" value="<?php echo TXT_ADDITEM?>"></td>
                                                <?php
                                              } else {
                                                ?>
                                                <td class="smallText">
                                                  <input type="submit" value="<?php echo TXT_ADDITEMS?>">
                                                  <div><?php echo TXT_WARNING_ADDITEMS?></div>
                                                </td>
                                                <?php
                                              }
                                              ?>
                                            </td>
                                          </tr>
                                        </table>
                                </form>
                                <?php
                              }}
                              break;
                            case 'list':
                            default:
                              ?>
                              <div id='content_title' style="font-family:arial;margin:0;"><?php echo TXT_EBAYPRODUCTS_VIEW?></div>
                              <?php
                              if($_SERVER['REQUEST_METHOD']=='POST') {
                                $seite = $_POST['seite'];
                                $offset = $_POST['offset'];
                                $qu = $_POST['qu'];
                                $filter = $_POST['filter'];
                                $filter_datum = $_POST['filter_datum'];
                                $datum_von_m = $_POST['datum_von_m'];
                                $datum_von_j = $_POST['datum_von_j'];
                                $datum_bis_m = $_POST['datum_bis_m'];
                                $datum_bis_j = $_POST['datum_bis_j'];
                                $display_type = $_POST['display_type'];
                              } else {
                                $seite = $_GET['seite'];
                                $offset = $_GET['offset'];
                                $qu = $_GET['qu'];
                                $filter = $_GET['filter'];
                                $filter_datum = $_GET['filter_datum'];
                                $datum_von_m = $_GET['datum_von_m'];
                                $datum_von_j = $_GET['datum_von_j'];
                                $datum_bis_m = $_GET['datum_bis_m'];
                                $datum_bis_j = $_GET['datum_bis_j'];
                                $display_type = $_GET['display_type'];
                              }
                              if(!isset($filter))
                                $filter = 0;
                              ?>
                              <div id='navi'>
                                <ul class='navi'>
                                  <li style='float:left;padding:5px;'<?php echo $filter==0?' class="active"':''?> onclick="location.href='xtbooster.php?xtb_module=list&filter=0'"><a href="xtbooster.php?xtb_module=list&filter=0"><?php echo TXT_NEW_RUNNING_AUCTIONS?></a></li>
                                  <li style='float:left;padding:5px;'<?php echo $filter==1?' class="active"':''?> onclick="location.href='xtbooster.php?xtb_module=list&filter=1'"><a href="xtbooster.php?xtb_module=list&filter=1"><?php echo TXT_SUCCESSFULL_RUNNING_AUCTIONS?></a></li>
                                  <li style='float:left;padding:5px;'<?php echo $filter==2?' class="active"':''?> onclick="location.href='xtbooster.php?xtb_module=list&filter=2'"><a href="xtbooster.php?xtb_module=list&filter=2"><?php echo TXT_SUCCESSFULL_AUCTIONS?></a></li>
                                  <li style='float:left;padding:5px;'<?php echo $filter==3?' class="active"':''?> onclick="location.href='xtbooster.php?xtb_module=list&filter=3'"><a href="xtbooster.php?xtb_module=list&filter=3"><?php echo TXT_FAILED_AUCTIONS?></a></li>
                                  <li style='float:left;padding:5px;'<?php echo $filter==4?' class="active"':''?> onclick="location.href='xtbooster.php?xtb_module=list&filter=4'"><a href="xtbooster.php?xtb_module=list&filter=4"><?php echo TXT_SALES_CHRONOLOGICAL?></a></li>
                                </ul>
                                <div class="cb"></div>
                              </div>
                              <div class="smallText" style="padding:5px;color:gray;">
                                <?php
                                $requestx = "ACTION:  FetchUsageStatistics\n";
                                $resx = $xtb->exec($requestx);
                                $resx = $xtb->parse($resx);
                                $LISTINGS_TM = $resx['LISTINGS_TM'];
                                $LISTINGS_LM = $resx['LISTINGS_LM'];
                                echo TXT_TOTAL_LISTINGS_THIS_MONTH.": ".$LISTINGS_TM."&nbsp;&nbsp;&nbsp;";
                                echo TXT_TOTAL_LISTINGS_LAST_MONTH.": ".$LISTINGS_LM."\n";
                                ?>
                              </div>
                              <div style="border-bottom:1px solid gray;margin-bottom:20px;"></div>
                              <div class='smallText'>
                                <?php
                                $i=0;
                                $add = "";
                                // $items_per_site werden weiter unten festgelegt!
                                // eBay schickt Notifications bei Verkaeufen, oder wenn eine Festpreis-Auktion ohne Verkauf zu Ende ist (ItemUnsold). 
                                // Wenn aber nur ein Teil der Artikel verkauft wurde, und die Zeit vorbei ist, bzw. bei nicht-Fetpreiisauktionen, kommt KEINE Notification => muss man hier abfangen
                                // 60 sek. spaeter fuer moegliche Zeitunterschiede zum eBay-Server
                                xtc_db_query("UPDATE xtb_auctions SET _EBAY_STATUS='successful' WHERE DURATION!='GTC' AND _EBAY_END_TIME<=UNIX_TIMESTAMP(NOW())-60 AND _EBAY_QUANTITY_BUYED>0");
                                xtc_db_query("UPDATE xtb_auctions SET _EBAY_STATUS='unsuccessful' WHERE DURATION!='GTC' AND _EBAY_END_TIME<=UNIX_TIMESTAMP(NOW())-60 AND _EBAY_QUANTITY_BUYED=0");
                                // Listenansicht ermöglichen
                                if ($display_type=='list') {
                                  $items_per_site = 100000;
                                } else {
                                  $items_per_site = 30;
                                }
                              if(!isset($seite))
                                $seite = 1;
                              if(!isset($offset))
                                $offset=($seite-1)*$items_per_site;
                              if(!isset($qu))
                                $qu = "";
                              else 
                                $qu = addslashes(trim($qu));
                              switch($filter) {
                                default:
                                  // aktiv, noch ohne Verkaeufe
                                case 0:
                                  $add = "(_EBAY_STATUS='active' AND _EBAY_QUANTITY_BUYED=0)"; break;
                                  // aktiv, erfolgreich 
                                case 1:
                                  $add = "(_EBAY_STATUS='active' AND _EBAY_QUANTITY_BUYED>0)"; break;
                                  // beendet, erfolgreich 
                                case 2:
                                  $add = "(_EBAY_STATUS='successful')"; break;
                                  // beendet, erfolglos
                                case 3:
                                  $add = "(_EBAY_STATUS='unsuccessful')"; break;
                                  // eBay-Verkäufe chronologisch
                                case 4:
                                  $add = "(_EBAY_QUANTITY_BUYED>0)"; break;
                              }
                              if($qu!='') {
                                $add .= " AND (_EBAY_ITEM_ID LIKE '%$qu%' OR products_model LIKE '%$qu%' OR TITLE LIKE '%$qu%')";
                              }
                              if ($filter_datum!='')  {
                                $ts_von = mktime(0,0,1,$datum_von_m,1,$datum_von_j);
                                $ts_bis = mktime(23,59,59,$datum_bis_m+1,0,$datum_von_j);
                                $add .= " AND (_EBAY_START_TIME>='".$ts_von."')";
                                $add .= " AND (_EBAY_START_TIME<='".$ts_bis."')";
                              }
                              if ($filter == '4') {
                                $products_query0 = xtc_db_query("SELECT TYPE FROM xtb_transactions t, xtb_auctions a LEFT JOIN ".TABLE_PRODUCTS." USING(products_id) WHERE $add and a._EBAY_ITEM_ID=t.XTB_ITEM_ID order by t.XTB_EBAY_TS DESC"); # This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                $products_query = xtc_db_query("SELECT *, MD5(CONCAT(t.XTB_ITEM_ID,'',t.XTB_EBAY_USERID)) as HASH FROM xtb_transactions t, xtb_auctions a LEFT JOIN ".TABLE_PRODUCTS." USING(products_id) WHERE $add and a._EBAY_ITEM_ID=t.XTB_ITEM_ID order by t.XTB_EBAY_TS DESC LIMIT $offset,$items_per_site"); # This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                              } else {
                                $products_query0 = xtc_db_query("SELECT TYPE FROM xtb_auctions LEFT JOIN ".TABLE_PRODUCTS." USING(products_id) WHERE $add order by XTB_ITEM_ID DESC"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                                $products_query = xtc_db_query("SELECT * FROM xtb_auctions LEFT JOIN ".TABLE_PRODUCTS." USING(products_id) WHERE $add order by XTB_ITEM_ID DESC LIMIT $offset,$items_per_site"); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
                              }
                              $num_rows = xtc_db_num_rows($products_query0);
                              $sites = ceil($num_rows/$items_per_site);
                              ?>
                              <div>
                                <?php echo xtc_draw_form('xtb', 'xtbooster.php', '', 'get', 'style="border:0;margin:0;padding:0;"'); ?>
                                  <input type="hidden" name="xtb_module" value="list" />
                                  <input type="hidden" name="filter" value="<?php echo $filter?>" />
                                  <strong><?php echo TXT_AUCTION_SEARCH?>:</strong>&nbsp;&nbsp;
                                  <input type="text" name="qu" value="<?php echo $qu?>" />
                                  <input type="submit" value="<?php echo TXT_GO?> &raquo;" />
                                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                  <strong><?php echo TXT_DISPLAY_TYPE?>: </strong><input type="radio" name="display_type" value="sites"<?php echo ($display_type=='sites'?' CHECKED="CHECKED"':($display_type==''?' CHECKED="CHECKED"':''))?>> <?php echo TXT_PAGE_BY_PAGE?>&nbsp;<input type="radio" name="display_type" value="list"<?php echo ($display_type=='list'?' CHECKED="CHECKED"':'')?>> <?php echo TXT_FULL_LIST?>
                                  <br/><br/>
                                  <strong><?php echo TXT_DATE_FILTER?></strong>&nbsp;&nbsp;
                                  <?php echo TXT_FROM?>: 
                                  <select name="datum_von_m">
                                    <?php
                                    foreach ($months as $k=>$v)    {
                                      ?>
                                      <option value="<?php echo $k?>"<?php echo ($datum_von_m==$k?' SELECTED="SELECTED"':(($datum_von_m==0 AND $k==date('n'))?' SELECTED="SELECTED"':''))?>><?php echo $v?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <select name="datum_von_j">
                                    <option value="0"><?php echo TXT_YEAR?></option>
                                    <?php
                                    $year_start = date('Y', xsb_db_first(xtc_db_query("SELECT MIN(_EBAY_START_TIME) as MinTime FROM xtb_auctions")));
                                    for ($y=date('Y');$y>=$year_start;$y--)  {
                                      ?>
                                      <option value="<?php echo $y?>"<?php echo ($datum_von_j==$y?' SELECTED="SELECTED"':(($datum_von_j==0 AND $y==date('Y'))?' SELECTED="SELECTED"':''))?>><?php echo $y?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  &nbsp;&nbsp;
                                  <?php echo TXT_TO?>: 
                                  <select name="datum_bis_m">
                                    <?php
                                    foreach ($months as $k=>$v)    {
                                      ?>
                                      <option value="<?php echo $k?>"<?php echo ($datum_bis_m==$k?' SELECTED="SELECTED"':(($datum_bis_m==0 AND $k==date('n'))?' SELECTED="SELECTED"':''))?>><?php echo $v?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <select name="datum_bis_j">
                                    <option value="0"><?php echo TXT_YEAR?></option>
                                    <?php
                                    $year_start = date('Y', xsb_db_first(xtc_db_query("SELECT MIN(_EBAY_START_TIME) as MinTime FROM xtb_auctions")));
                                    for ($y=date('Y');$y>=$year_start;$y--)  {
                                      ?>
                                      <option value="<?php echo $y?>"<?php echo ($datum_bis_j==$y?' SELECTED="SELECTED"':(($datum_bis_j==0 AND $y==date('Y'))?' SELECTED="SELECTED"':''))?>><?php echo $y?></option>
                                      <?php
                                    }
                                    ?>
                                  </select>
                                  <input type="submit" name="filter_datum" value="<?php echo TXT_GO?> &raquo;" />
                                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                </form>
                              </div>
                              <br/>
                              <div style="border-bottom:1px solid gray;margin-top:10px;margin-bottom:10px;"></div>
                              <strong><?php echo TXT_CURRENT_PAGE?></strong>
                              &nbsp;&nbsp;
                              <?php
                              for($s=1;$s<=$sites;$s++) {
                                if($s>1)
                                  echo " | ";
                                echo "<a".($seite==$s?' style="font-weight:bold;"':'')." href='xtbooster.php?xtb_module=list&amp;filter=$filter&amp;seite=$s&amp;qu=$qu&amp;offset=".(($s-1)*$items_per_site)."'>$s</a>";
                              }
                              ?>
                              <div style="border-bottom:1px solid gray;margin-top:10px;margin-bottom:10px;"></div>
                              <?php echo xtc_draw_form('xtb_list', 'xtbooster.php', '', 'post'); ?>
                                <table border="0" cellpadding="2" cellspacing="1" width="100%">
                                  <tr>
                                    <td class="smallText" style="font-weight:bold;">pID</td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_ARTNO?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_TITLE?></td>
                                    <td class="smallText" style="font-weight:bold;">eBay ID</td>
                                    <td class="smallText" style="font-weight:bold;">eBay Site</td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_AUCTIONTYPE?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_STARTPRICE?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_BUYITNOWPRICE?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_QUANTITY_OFFERED?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_QUANTITY_BUYED?></td>
                                    <td class="smallText" style="font-weight:bold;"><?php echo TXT_RUNTIME?> (<?php echo TXT_EBAYTIME?>)</td>
                                    <td class="smallText" style="font-weight:bold;width:20px;"></td>
                                    <td class="smallText" style="font-weight:bold;">&nbsp;</td>
                                  </tr>
                                  <?php
                                  while($x = xtc_db_fetch_array($products_query)) {
                                    // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de) 
                                    if($character_set_client!='utf8')
                                      foreach($x as $key=>$value)
                                        $x[$key] = is_utf8($value) ? $value : utf8_encode($value);
                                    switch($x['TYPE']) {
                                      case 'FixedPriceItem':
                                        $x['TYPE']=TXT_FIXPRICEAUCTION; break;
                                      case 'Chinese':
                                        $x['TYPE']=TXT_CHINESEAUCTION; break;
                                      case 'Dutch':
                                        $x['TYPE']=TXT_DUTCHAUCTION; break;
                                      case 'StoresFixedPrice':
                                        $x['TYPE']=TXT_STOREFIXEDPRICE; break;
                                    }
                                    $_EBAY_ITEM_ID = "<a target='_blank' style='text-decoration:underline;' href='http://cgi.".(USE_SANDBOX?'sandbox.':'')."ebay.de/ws/eBayISAPI.dll?ViewItem&item=".$x['_EBAY_ITEM_ID']."'>".$x['_EBAY_ITEM_ID']."</a>";
                                    ?>
                                    <tr class="attributes-<?php echo $i++%2?'even':'odd'; ?>"<?php echo (($x['_EBAY_END_TIME']<time() && $x['DURATION']!='GTC')||$x['QUANTITY']<=$x['_EBAY_QUANTITY_BUYED'])?' style="color:gray;"':''; ?>>
                                      <td class="smallText" valign="top"><a href="categories.php?cPath=0&pID=<?php echo $x['products_id']; ?>&action=new_product"><?php echo $x['products_id']; ?></a></td>
                                      <td class="smallText" valign="top"><?php echo $x['products_model']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['TITLE']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $_EBAY_ITEM_ID; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['_EBAY_MARKETPLACE']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['TYPE']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['STARTPRICE']." ".$x['CURRENCY']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['BUYITNOWPRICE']." ".$x['CURRENCY']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['QUANTITY']; ?></td>
                                      <td class="smallText" valign="top"><?php echo $x['_EBAY_QUANTITY_BUYED']; ?></td>
                                      <td class="smallText" valign="top"><?php echo strftime(TIME_FORMAT,$x['_EBAY_START_TIME'])." ".TXT_TO."<br>".strftime(TIME_FORMAT,$x['_EBAY_END_TIME']); ?></td>
                                      <td class="smallText" valign="top">
                                      <?php
																			if ( $filter=='1' || $filter=='2' ) {
																				$sql = "SELECT t.XTB_ITEM_ID,t.XTB_EBAY_USERID,t.XTB_KEY,MD5(CONCAT(t.XTB_ITEM_ID,'',t.XTB_EBAY_USERID)) as HASH, t.XTB_CHECKOUT_TS,t.XTC_ORDER_ID,t.XTB_EBAY_NAME,t.XTB_EBAY_TS FROM xtb_transactions as t LEFT JOIN xtb_auctions as a ON (a._EBAY_ITEM_ID=t.XTB_ITEM_ID) WHERE t.XTB_ITEM_ID=".$x['_EBAY_ITEM_ID'];
																				$q = xtc_db_query($sql); // This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de)
																				if(xtc_db_num_rows($q)) {
																					?>
																					<table border="0" cellpadding="2" cellspacing="0">
																						<tr>
																							<td class="smallText"><?php echo TXT_ORDER?></td>
																							<td class="smallText"><?php echo TXT_DATE_OF_PURCHASE?></td>
																							<td class="smallText"><?php echo TXT_CHECKOUT?></td>
																							<td class="smallText"><?php echo TXT_CUSTOMER?></td>
																						</tr>
																						<?php
																						while($tx = xtc_db_fetch_array($q)) {
																							// This line includes GNU/GPL licensed code written by xt:Commerce GmbH (www.xtcommerce.de) 
																							if($tx['XTB_CHECKOUT_TS']!=0) {
																								?>
																								<tr>
																									<td class="smallText" valign="top"><a style="text-decoration:underline;" href="orders.php?page=1&oID=<?php echo $tx['XTC_ORDER_ID']?>&action=edit" target="_blank"><?php echo $tx['XTC_ORDER_ID']?></a></td>
																									<td class="smallText" valign="top"><?php echo $tx['XTB_EBAY_TS']?strftime(TIME_FORMAT_BR,$tx['XTB_EBAY_TS']):'-'; ?></td>
																									<td class="smallText" valign="top"><?php echo $tx['XTB_CHECKOUT_TS']?strftime(TIME_FORMAT_BR,$tx['XTB_CHECKOUT_TS']):'-'; ?></td>
																									<td class="smallText" valign="top" title="<?php echo $tx['XTB_EBAY_USERID'];?>"><?php echo $tx['XTB_EBAY_NAME']?></td>
																								</tr>
																								<?php
																							} else {
																								$url = HTTP_SERVER.DIR_WS_CATALOG."callback/xtbooster/xtbcallback.php?item=".$tx['HASH']."&key=".$tx['XTB_KEY']; //DokuMan - Moved xtbcallback.php to callback directory
																								?>
																								<tr>
																									<td class="smallText" valign="top">-</td>
																									<td class="smallText" valign="top"><?php echo $tx['XTB_EBAY_TS']?strftime(TIME_FORMAT_BR,$tx['XTB_EBAY_TS']):'-'; ?></td>
																									<td class="smallText" valign="top"><a href="<?php echo $url?>"><img style="border:0;" src="//www.xsbooster.com/img/cart.gif" title="<?php echo TXT_MANUAL_CHECKOUT?> (<?php echo $tx['XTB_EBAY_USERID']?>)" alt="<?php echo TXT_MANUAL_CHECKOUT?> (<?php echo $tx['XTB_EBAY_USERID']?>)"></a></td>
																									<td class="smallText" valign="top" title="<?php echo $tx['XTB_EBAY_USERID'];?>"><?php echo $tx['XTB_EBAY_NAME']?></td>
																								</tr>
																								<?php
																							}
																						}
																						?>
																					</table>
																					<?php
																				}
																			} elseif ($filter == '4') {
																				?>
																				<table border="0" cellpadding="2" cellspacing="0">
																					<tr>
																						<td class="smallText"><?php echo TXT_ORDER?></td>
																						<td class="smallText"><?php echo TXT_DATE_OF_PURCHASE?></td>
																						<td class="smallText"><?php echo TXT_CHECKOUT?></td>
																						<td class="smallText"><?php echo TXT_CUSTOMER?></td>
																					</tr>
																					<?php

																					if($x['XTB_CHECKOUT_TS']!=0)
																					{
																						?>
																						<tr>
																							<td class="smallText" valign="top"><a style="text-decoration:underline;" href="orders.php?page=1&oID=<?php echo $x['XTC_ORDER_ID']?>&action=edit" target="_blank"><?php echo $x['XTC_ORDER_ID']?></a></td>
																							<td class="smallText" valign="top"><?php echo $x['XTB_EBAY_TS']?strftime("%d.%m.%Y,<br/>%H:%M:%S Uhr",$x['XTB_EBAY_TS']):'-'; ?></td>
																							<td class="smallText" valign="top"><?php echo $x['XTB_CHECKOUT_TS']?strftime("%d.%m.%Y,<br/>%H:%M:%S Uhr",$x['XTB_CHECKOUT_TS']):'-'; ?></td>
																							<td class="smallText" valign="top" title="<?php echo $t['XTB_EBAY_USERID'];?>"><?php echo utf8_decode($x['XTB_EBAY_NAME'])."<br>".utf8_decode($x['XTB_EBAY_USERID']);?></td>
																						</tr>
																						<?php
																					}
																					else
																					{
																						$url = HTTP_SERVER.DIR_WS_CATALOG."callback/xtbooster/xtbcallback.php?item=".$x['HASH']."&key=".$x['XTB_KEY']; //DokuMan - Moved xtbcallback.php to callback directory
																						?>
																						<tr>
																							<td class="smallText" valign="top">-</td>
																							<td class="smallText" valign="top"><?php echo $x['XTB_EBAY_TS']?strftime("%d.%m.%Y,<br/>%H:%M:%S Uhr",$x['XTB_EBAY_TS']):'-'; ?></td>
																							<td class="smallText" valign="top"><a href="<?php echo $url?>"  target="_new"><img style="border:0;" src="//www.xsbooster.com/img/cart.gif" title="<?php echo TXT_MANUAL_CHECKOUT?> (<?php echo $x['XTB_EBAY_USERID']?>)" alt="<?php echo TXT_MANUAL_CHECKOUT?> (<?php echo $x['XTB_EBAY_USERID']?>)"></a></td>
																							<td class="smallText" valign="top" title="<?php echo $x['XTB_EBAY_USERID'];?>"><?php echo utf8_decode($x['XTB_EBAY_NAME'])."<br>".utf8_decode($x['XTB_EBAY_USERID']);?></td>
																						</tr>
																						<?php
																					}
																					?>
																				</table>
                                      <?php
                                      }
                                      ?>
                                   </td>
                                   <td><?php if ($filter==3) { ?><input type="checkbox" name="items[]" id="items_<?php echo (int)$CheckboxIndex?>" value="<?php echo $x['XTB_ITEM_ID']?>"><?php } ?></td>
                                   <?php
                                   // Checkboxname für die "Check All"-Funktion in einer Liste speichern
                                   $CheckboxIndex++;
                                   ?>
                                 </tr>
                                 <?php
                               }
                               ?>
                             </table>
                             <?php
                             break;
                          }
                    }
                    ?>
                  </div>
                  <?php
                  if (($xtb_module=='list') && ($filter==3))  {
                    ?>
                    <script type="text/javascript">
                      function ChangeCheckboxes(c)  {
                        var i;
                        for (i=0;i<c;i++)  {
                          $('items_'+i).checked=$('CheckAll').checked;
                        }
                      }
                    </script>
                    <div style="width:100%; text-align:right; font-size:11px; color:black; font-family:Arial;">
                      <?php echo TXT_CHECKED?> &nbsp;<input type="submit" name="ACTION_Relist" value="<?php echo TXT_RELIST?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <?php echo TXT_CHECK_ALL?> <input type="checkbox" name="CheckAll" id="CheckAll" value="1" onclick="ChangeCheckboxes(<?php echo $CheckboxIndex?>);" />&nbsp;&nbsp;<br />
                      <?php echo TXT_WARN_RELIST_COSTS?>
                    </div>
                    <input type="hidden" name="filter" value="<?php echo $filter?>" />
                    <input type="hidden" name="seite" value="<?php echo $seite?>" />
                  </form>
                  <?php
                } // Ende if $xtb_module=='list'
                ?>
                <div style="padding:5px;font-size:11px;color:gray;font-family:Arial;"><?php echo TXT_COPYRIGHT.' (v'.XTBOOSTER_VERSION.')' ?></div>
                <div onclick='window.open("http://developer.ebay.com/join/benefits/logo/");' style='cursor:pointer;width:68px;height:53px;border:0;margin:10px;background-image:url(//www.xsbooster.com/img/ebay-logo-compapp.gif);background-repeat:none;'></div>
              </td>
            </tr>
          </table>
        </td>
        <!-- body_text_eof //-->
      </tr>
    </table>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
