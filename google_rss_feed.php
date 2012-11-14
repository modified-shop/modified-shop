<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   Copyright 2009 - Daniel Siekiera / Sebastian Schramm
   xt-shopservice.de - Ein Projekt von Webdesign-Erfurt.de
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

include ('includes/application_top.php');

define('PRODUCTS_ZUSTAND','Neu');

// cache settings
define('CACHEDAYS',1);
define('CACHEFILE', 'export/googlebase.cache');

// cache check
if(file_exists(DIR_FS_DOCUMENT_ROOT.CACHEFILE) && time() - filemtime(DIR_FS_DOCUMENT_ROOT.CACHEFILE) < CACHEDAYS*24*3600) {
  // show cached content
  echo file_get_contents(DIR_FS_DOCUMENT_ROOT.CACHEFILE);
  exit();
}
// start caching
ob_start();

$Title = "Produkt Feed von " . HTTP_SERVER;
$Description = "Alle Produkte von " . HTTP_SERVER;
$copyright = HTTP_SERVER;
$SiteLink = HTTP_SERVER .DIR_WS_CATALOG ;
$group_check = "";
$fsk_lock = "";
$refID = "";

if(GOOGLE_RSS_FEED_REFID !='' && GOOGLE_RSS_FEED_REFID !='0') {
  $refID = "?refID=".GOOGLE_RSS_FEED_REFID;
}

if(GROUP_CHECK == 'true') {
  $group_check = " and p.group_permission_".$_SESSION['customers_status']['customers_status_id']."=1 ";
}

if($_SESSION['customers_status']['customers_fsk18_display'] == '0') {
  $fsk_lock = ' and p.products_fsk18!=1';
}

$query = "SELECT p.products_id,
                 p.products_ean,
                 pd.products_name,
                 pd.products_short_description,
                 pd.products_description,
                 p.products_price,
                 p.products_image,
                 p.manufacturers_id,
                 p.products_weight,
                 p.products_model,
                 p.products_quantity,
                 p.group_permission_1,
                 p.products_date_added,
                 p.products_tax_class_id,
                 s.specials_new_products_price,
                 s.status
            FROM products AS p
       LEFT JOIN products_description AS pd
              ON (p.products_id = pd.products_id)
       LEFT JOIN specials AS s
              ON (p.products_id = s.products_id)
           WHERE p.products_status = '1'
             AND pd.language_id = '2'
                 ".$group_check."
                 ".$fsk_lock."
        ORDER BY p.products_date_added DESC" ;

$listing_query = xtDBquery($query);

require (DIR_WS_CLASSES.'order.php');
$order = new order;

$default_data = xtc_db_fetch_array(xtc_db_query(" SELECT ab.entry_postcode,
                                                         z.zone_name,z.zone_id,
                                                         ab.entry_country_id,
                                                         c.countries_id,
                                                         c.countries_name,
                                                         c.countries_iso_code_2,
                                                         c.countries_iso_code_3,
                                                         c.address_format_id
                                                    FROM address_book ab, zones z, countries c
                                                   WHERE ab.address_book_id = '1'
                                                     AND z.zone_id = ab.entry_zone_id
                                                     AND c.countries_id = ab.entry_country_id"));

$order->customer = array('postcode' => $default_data['entry_postcode'],
                         'state' => $default_data['zone_name'],
                         'zone_id' => $default_data['zone_id'],
                         'country' =>array('id' => $default_data['countries_id'],
                                           'title' => $default_data['countries_name'],
                                           'iso_code_2' => $default_data['countries_iso_code_2'],
                                           'iso_code_3' => $default_data['countries_iso_code_3'] ),
                                           'format_id' => $default_data['address_format_id']);

$order->delivery = array('postcode' => $default_data['entry_postcode'],
                         'state' => $default_data['zone_name'],
                         'zone_id' => $default_data['zone_id'],
                         'country' => array('id' => $default_data['countries_id'],
                                            'title' => $default_data['countries_name'],
                                            'iso_code_2' => $default_data['countries_iso_code_2'],
                                            'iso_code_3' => $default_data['countries_iso_code_3'] ),
                                            'format_id' => $default_data['address_format_id']);

$_SESSION['delivery_zone'] = $order->delivery['country']['iso_code_2'];

require (DIR_WS_CLASSES.'shipping.php');
$shipping = new shipping;
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
require_once (DIR_WS_CLASSES.'xtcPrice.php');
$xtPrice = new xtcPrice(DEFAULT_CURRENCY, $_SESSION['customers_status']['customers_status_id']);

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n\n";
echo "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\" xmlns:c=\"http://base.google.com/cns/1.0\">\n\n";
echo "<channel>\n";
echo "\t<title>$Title</title>\n";
echo "\t<link>$SiteLink</link>\n";

while ($listing = xtc_db_fetch_array($listing_query, true)) {
  $_SESSION['cart']->remove_all();
  $_SESSION['cart']->add_cart($listing['products_id'], 1, '', false);
  $total_weight = $_SESSION['cart']->show_weight();
  $total_count = $_SESSION['cart']->count_contents();
  $quotes = $shipping->quote();
  $link = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($listing['products_id'],$listing['products_name']),'NONSSL', false);

  if ($listing['specials_new_products_price'] > 0 && $listing['status'] == 1) {
    $price = $xtPrice->xtcGetPrice($listing['products_id'], $format = false, 1, $listing['products_tax_class_id'], $listing['specials_new_products_price']);
  } else {
    $price = $xtPrice->xtcGetPrice($listing['products_id'], $format = false, 1, $listing['products_tax_class_id'], $listing['products_price']);
  }

  $price = str_replace('0,00','0,01',$price); // Google akzeptiert keine Preise wie 0,00
  $price = $xtPrice->xtcFormat($price,true);
  $price = str_replace('&euro;','EUR',$price);

  $products_name = $listing['products_name'];
  $products_name = str_replace ("&", "&amp;", $products_name);
  $products_name = str_replace ("\n", " ", $products_name);

  if($listing['products_description'] !='') {
    $beschreibung = $listing['products_description'];
  } else if($listing['products_short_description'] !='') {
    $beschreibung = $listing['products_short_description'];
  } else {
    $beschreibung = $products_name;
  }

  if($listing['manufacturers_id'] > '0') {
    $marke = xtc_db_fetch_array(xtc_db_query("SELECT manufacturers_name 
                                                FROM ".TABLE_MANUFACTURERS."
                                               WHERE manufacturers_id = '".$listing['manufacturers_id']."'")
                                            );
  }

  // Kompatibel mit
  $cross_query = xtDBquery("SELECT p.products_fsk18,
                                   p.products_tax_class_id,
                                   p.products_id,
                                   p.products_image,
                                   p.products_model,
                                   pd.products_name,
                                   pd.products_short_description,
                                   p.products_fsk18,
                                   p.products_price,
                                   p.products_vpe,
                                   p.products_vpe_status,
                                   p.products_vpe_value,
                                   xp.sort_order
                              FROM ".TABLE_PRODUCTS_XSELL." xp,
                                   ".TABLE_PRODUCTS." p,
                                   ".TABLE_PRODUCTS_DESCRIPTION." pd
                             WHERE xp.xsell_id = '".$listing['products_id']."'
                               AND xp.products_id = p.products_id
                                   ".$fsk_lock.$group_check."
                               AND p.products_id = pd.products_id
                               AND pd.language_id = '2'
                               AND p.products_status = '1'
                          ORDER BY xp.sort_order asc");

  echo "\n\n\t<item>\n";

  // Produktname
  echo "\t\t<title>" . $products_name . "</title>\n";

  // Link
  echo "\t\t<link>" . $link . $refID . "</link>\n";

  // Beschreibung
  echo "\t\t<g:beschreibung><![CDATA[".$beschreibung."]]></g:beschreibung>\n";

  // Produkt ID
  echo "\t\t<g:id>" . $listing['products_id'] . "</g:id>\n";

  // EAN Nummer
  if(!empty($listing['products_ean'])) {
    echo "\t\t<g:ean>".$listing['products_ean']."</g:ean>\n";
  }

  // Gewicht
  if($listing['products_weight'] !='0.00') {
    echo "\t\t<g:gewicht>".$listing['products_weight']." kg</g:gewicht>\n";
  }

  // Herstellername
  if($listing['manufacturers_id'] > '0') {
    echo "\t\t<g:marke>".$marke['manufacturers_name']."</g:marke>\n";
  }

  // Produktbilder, jedoch nicht mehr als insgesamt 10
  if(!empty($listing['products_image'])) {
    echo "\t\t<g:bild_url>".HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$listing['products_image']."</g:bild_url>\n";
  }
  $images = xtc_get_products_mo_images($listing['products_id']);
  if($images) {
    $b = 0;
    foreach($images as $image) {
      $b++;
      echo "\t\t<g:bild_url>".HTTP_SERVER.DIR_WS_CATALOG.DIR_WS_ORIGINAL_IMAGES.$image['image_name']."</g:bild_url>\n";
      if($b==9) {
          break;
      }
    }
  }
  
  // Alternativ zur MPN die Modelnummer
  if(!empty($listing['products_model'])) {
    echo "\t\t<g:mpn>".$listing['products_model']."</g:mpn>\n";
  }
  
  // Fertiger Produktpreis
  echo "\t\t<g:preis>".$price."</g:preis>\n";

  // Menge
  if($listing['products_quantity'] > 0) {
    echo "\t\t<g:menge>".$listing['products_quantity']."</g:menge>\n";
  }

  // Zustand
  echo "\t\t<g:zustand>".PRODUCTS_ZUSTAND."</g:zustand>\n";

  // Kompatibel mit
  if(xtc_db_num_rows($cross_query)) {
    while ($xsell = xtc_db_fetch_array($cross_query, true)) {
      $marke_komp = xtc_db_fetch_array(xtc_db_query("SELECT manufacturers_name FROM ".TABLE_MANUFACTURERS." WHERE manufacturers_id = '".$xsell['products_id']."'"));
      echo "\t\t<g:kompatibel_mit>\n";
      echo "\t\t\t<g:titel>".$xsell['products_name']. "</g:titel>\n";
      echo "\t\t\t<g:mpn>".$xsell['products_model']. "</g:mpn>\n";
      if(!empty($marke_komp)) {
        echo "\t\t\t<g:marke>".$marke_komp['manufacturers_name']. "</g:marke>\n";
      }
      echo "\t\t</g:kompatibel_mit>\n";
    }
  }

  // Versandkosten
  $i = 1;
  foreach ($quotes AS $quote) {
    echo "\t\t<g:versand>\n";
    echo "\t\t\t<g:land>DE</g:land>\n";
    echo "\t\t\t<g:region></g:region>\n";
    echo "\t\t\t<g:service>".$quote['module']."</g:service>\n";
    echo "\t\t\t<g:preis>";
    if (isset($quote['tax']) && $quote['tax'] > 0) {
      echo round(($quote['methods'][0]['cost'] * ( 100 + $quote['tax'] ) / 100),2);
    } else {
      if (!empty($quote['methods'][0]['cost'])) {
        echo $quote['methods'][0]['cost'];
      } else {
        echo '0';
      }
    }
    echo "</g:preis>\n";
    echo "\t\t</g:versand>\n";
    if($i==10) {
      break;
    }
    $i++;
  }
  echo "\t</item>";
}

echo "</channel>\n";
echo "</rss>";
$_SESSION['cart']->reset(true);
unset($_SESSION['cart']);

// end caching
$content = ob_get_clean();

// save cache
$fh = fopen(DIR_FS_DOCUMENT_ROOT.CACHEFILE,'w');
fputs($fh, $content);
fclose($fh);

// show cached content
echo $content;
?>