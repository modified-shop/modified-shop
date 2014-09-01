<?php 
/* -----------------------------------------------------------------------------------------

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   ----------------------------------------------------------------------------------------- 
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
define('MODULE_PREISSUCHMASCHINE_TEXT_DESCRIPTION', '<hr noshade="noshade"><br><center><a href="http://www.preissuchmaschine.de/"><img src="http://bilder.preissuchmaschine.de/other/PSMLogoMid1.jpg" width="100" height="46" border="0" alt="Preissuchmaschine - Ihr Preisvergleich"></a></center><br><br>
<b>Export</b><br>PreisSuchmaschine.de<br><br>
<b>Trennzeichen</b><br>getrennt durch | (PIPE)<br><br>
<b>Format</b><br>- ProduktID<br>- Gewicht<br>- EAN<br>- Lagerbestand<br>- Hersteller<br>- ProduktBezeichnung<br>- ArtikelNr. (ggf. auch Hersteller-ArtikelNr.)<br>- Preis<br>- Produktbeschreibung (kurz)<br>- Produktbeschreibung (lang)<br>- Lieferzeit<br>- Produktlink<br>- FotoLink<br>- Kategoriename<br>- Vorkasse<br>- Nachnahme<br>- Rechnung<br>- Kreditkarte<br>- Lastschrift<br>- PayPal<br>- Moneybookers<br>- Giropay<br><br>
<b>Besonderheiten</b><br>- Automatisches Kampagnentracking innherhalb von XT-Commerce<br>- Unterst&uuml;tzt Google-Analytics Kampagnentracking<br><br>
<b>Modulversion</b><br>PreisSuchmaschine.de - <i>Juni 2010 - 2.1</i><br><br>
<b>Fragen</b><br>guenstiger.de GmbH<br>Vorsetzen 53<br>20459 Hamburg<br><br>Tel: 040 319 796-30<br>Fax: 040 319 796-39<br>E-Mail:<a href="mailto:post@metashopper.de?SUBJECT=Fragen zum XT:Commerce-Modul November 2009 - 2.0"><u>post@metashopper.de</u></a>');
define('MODULE_PREISSUCHMASCHINE_EMAIL','Sehr%20geehrtes%20Preissuchmaschine.de%20Team,%0A%0Ahierbei%20handelt%20es%20sich%20um%20eine%20%FCber%20das%20%22Preissuchmaschine.de%20-%20CSV%22%20Modul%20automatisch%20generierte%20E-Mail%20aus%20dem%20XT-Commerce%20Backoffice.%0A%0ABitte%20pr%FCfen%20sie%20meinen%20Shop%20<-SHOP->%20ob%20dieser%20bei%20Ihnen%20aufgenommen%20werden%20kann.%0A%0ADer%20Link%20zu%20der%20Produktdatenliste%20ist:%0A<-LINK->%0A%0ABitte%20geben%20Sie%20diese%20Informationen%20an%20den%20entsprechenden%20kaufm%E4nnischen%20Berater%20weiter.%0A%0AVielen%20Dank.');
define('MODULE_PREISSUCHMASCHINE_TEXT_TITLE', 'Preissuchmaschine.de - CSV');
define('MODULE_PREISSUCHMASCHINE_FILE_TITLE' , '<hr noshade>Dateiname:');
define('MODULE_PREISSUCHMASCHINE_FILE_DESC' , 'Das Modul legt diese generierte Preisliste/Produktdatenliste/Exportdatei bei der Speicherart \'Am Server Speichern\' automatisch im Unterverzeichnis Ihres Shops mit dem Namen \'export/\' (Das Verzeichnis ben&ouml;tigt folgende Rechte 0777) ab. Die Datei hat dort folgende Bezeichnung:');
define('MODULE_PREISSUCHMASCHINE_STATUS_DESC','Modulstatus');
define('MODULE_PREISSUCHMASCHINE_STATUS_TITLE','Status');
define('MODULE_PREISSUCHMASCHINE_EXPORT_YES','Für Preissuchmaschine.de online ablegen und auf den lokalen PC herunterladen');
define('MODULE_PREISSUCHMASCHINE_EXPORT_NO','Für Preissuchmaschine.de online ablegen');
define('MODULE_PREISSUCHMASCHINE_EXPORT_LINK','export/');

define('MODULE_PREISSUCHMASCHINE_EXPORT','Bitte diesen Export-Prozess AUF <b>KEINEN</b> FALL unterbrechen. Dieser kann vor allem bei Shops mit gr&ouml;&szlig;eren Datenbest&auml;nden einige Minuten in Anspruch nehmen.');
 
define('MODULE_PREISSUCHMASCHINE_psmgoogleHeader','<hr noshade><b>Google-Analytics:</b>');
define('MODULE_PREISSUCHMASCHINE_psmgoogle_DESC','Wenn Sie diese Option einschalten, werden an die Produktlinks automatisch Parameter angeh&auml;ngt, mit denen Sie den Erfolg ihrer Kooperation zus&auml;tzlich in Google-Analytics verfolgen k&ouml;nnen. Sie finden die Auswertung unter dem Men&uuml;punkt "Zugriffsquellen" -&gt; "Kampagnen" -&gt; "preissuchmaschine". ');
define('MODULE_PREISSUCHMASCHINE_psmgoogle_NO','Nicht aktivieren');
define('MODULE_PREISSUCHMASCHINE_psmgoogle_YES','Aktivieren');

define('MODULE_PREISSUCHMASCHINE_EXPORT_TYPE','<hr noshade><b>Speicherart / Produkte aktualisieren:</b>');
define('MODULE_PREISSUCHMASCHINE_CAMPAIGNS','<hr noshade><b>Kampagnen:</b> (automatisch)');
define('MODULE_PREISSUCHMASCHINE_CAMPAIGNS_LINK',HTTP_CATALOG_SERVER.DIR_WS_CATALOG."admin/stats_campaigns.php?report=2&startD=1&startM=".date("m")."&startY=".date("Y")."&status=0&campaign=psm&endD=".date("d")."&endM=".date("m")."&endY=".date("Y"));
define('MODULE_PREISSUCHMASCHINE_CAMPAIGNS_DESC','Durch unsere automatische Kampagneneinrichtung k&ouml;nnen Sie jederzeit die von der <a href="campaigns.php"><i>Kampagne</i></a> Preissuchmaschine.de in Ihren Onlineshop weitergeleiteten Nutzer auswerten. Sie sollten &uuml;ber folgenden Link den durch uns generierten Warenkorbumsatz jederzeit nachvollziehen k&ouml;nnen:<br><br><a style="text-decoration:underline" href=\''.MODULE_PREISSUCHMASCHINE_CAMPAIGNS_LINK.'\'><i><b>Kampagnen-Report</b></i></a><br><br>');
define('MODULE_PREISSUCHMASCHINE_EXPORT_LINK_SEND','Den vorangegangenen Link sollten Sie nun unter dem Punkt Preisliste/Produktdatenliste auf der Stammdatenerfassung der PreisSuchmaschine.de eintragen. Die Stammdatenerfassung kann man hier anfordern: <a style="text-decoration:underline" href=\'mailto:mitmachen@metashopper.de?SUBJECT=Anfrage - Interesse an einer eventuellen Onlinekooperation&BODY=' .  str_replace("<-SHOP->",HTTP_CATALOG_SERVER . DIR_WS_CATALOG,str_replace("<-LINK->",HTTP_CATALOG_SERVER . DIR_WS_CATALOG . MODULE_PREISSUCHMASCHINE_EXPORT_LINK . MODULE_PREISSUCHMASCHINE_FILE,MODULE_PREISSUCHMASCHINE_EMAIL)) .  '\'><br><br><i><b>zur Anfrage</b> </i></a>(sofern noch nicht erfolgt)');


$psmgoogle_input_query = xtc_db_query("select configuration_value from  " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PREISSUCHMASCHINE_PSMGOOGLEV' LIMIT 1");
$res = xtc_db_fetch_array($psmgoogle_input_query); 


if (isset($_POST['psmgoogle'])) {
  $_POST['psmgoogle'] == "yes"?$val="Y":$val="N";
  if( $res !== false ) {

    // update value if $_POST['freeshippinglimit_input'] != $freeshipping_comment_db
    if( $val != $res['configuration_value'] ) {
      xtc_db_query("update " . TABLE_CONFIGURATION . "
                set configuration_value = '" . $val . "'
                where configuration_key = 'MODULE_PREISSUCHMASCHINE_PSMGOOGLEV'");
    }
  } else {
    // insert data
    xtc_db_query("insert into " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            values ('MODULE_PREISSUCHMASCHINE_PSMGOOGLEV', '" . $val . "', 6, 1, '', now()) ");
  }
  $UseGoogle = $val=="Y";
} else {
  if( $res !== false ) {
    $resArray = xtc_db_fetch_array($res) ;
    $UseGoogle = $resArray['configuration_value'] == "Y";
  } else {
    $UseGoogle = false;
  }    
}
 
  class preissuchmaschine {
    var $code, $title, $description, $enabled;
    var $shippingtax = -1;
    var $shippingtaxcod = -1;
    var $shipping_free_shipping = -1;

    
	var $payment = array('MONEYORDER'   => array('active' => false,
													 'title' => 'Vorkasse'),
							 'COD' 			=> array('active' => false,
													 'title' => 'Nachnahme'),
							 'INVOICE' 		=> array('active' => false,
													 'title' => 'Rechnung'),
							 'CC' 			=> array('active' => false,
													 'title' => 'Kreditkarte'),
							 'BANKTRANSFER' => array('active' => false,
													 'title' => 'Lastschrift'),
							 'PAYPAL' 		=> array('active' => false,
													 'title' => 'PayPal'),
							 'MONEYBOOKERS' => array('active' => false,
													 'title' => 'Moneybookers')
							);

    
    
    function preissuchmaschine() {
      global $order;

      $this->code = 'preissuchmaschine';
      $this->title = MODULE_PREISSUCHMASCHINE_TEXT_TITLE;
      $this->description = MODULE_PREISSUCHMASCHINE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PREISSUCHMASCHINE_SORT_ORDER;
      $this->CAT=array();
      $this->PARENT=array();
      $this->enabled = ((MODULE_PREISSUCHMASCHINE_STATUS == 'True') ? true : false);

      // check which payment method (cod, cash etc. ...) is active
      $this->checkActivePayment();

      // check which payment option (default, per item, table) is active
      $this->checkStandardShippingCostsOption();      
      
      $this->TAX = array ();
      $zones_query = xtDBquery("SELECT tax_class_id as class FROM ".TABLE_TAX_CLASS);
      while ($zones_data = xtc_db_fetch_array($zones_query,true)) {
        
        // calculate tax based on shipping or deliverey country (for downloads)
        if (isset($_SESSION['billto']) && isset($_SESSION['sendto'])) {
        $tax_address_query = xtc_db_query("select ab.entry_country_id, ab.entry_zone_id from " . TABLE_ADDRESS_BOOK . " ab left join " . TABLE_ZONES . " z on (ab.entry_zone_id = z.zone_id) where ab.customers_id = '" . $_SESSION['customer_id'] . "' and ab.address_book_id = '" . ($this->content_type == 'virtual' ? $_SESSION['billto'] : $_SESSION['sendto']) . "'");
            $tax_address = xtc_db_fetch_array($tax_address_query);
        $this->TAX[$zones_data['class']]=xtc_get_tax_rate($zones_data['class'],$tax_address['entry_country_id'], $tax_address['entry_zone_id']);				
        } else {
        $this->TAX[$zones_data['class']]=xtc_get_tax_rate($zones_data['class']);		
        }
        
        
		  }
      
      // take the tax data from the db
      $getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
                     FROM " . TABLE_CONFIGURATION . "
                     WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_COD_FEE_TAX_CLASS';");

      $result = xtc_db_fetch_array($getValues);
      if( isset($result['table_values']) && $result['table_values'] != '') {
        $this->shippingtaxcod = $result['table_values'];
      }


      // take the tax data from the db
      $getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
                     FROM " . TABLE_CONFIGURATION . "
                     WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING';");

      $result = xtc_db_fetch_array($getValues);
      if( isset($result['table_values']) && $result['table_values'] != '') {
        if($result['table_values']) {
          // take the tax data from the db
          $getValues2 = xtc_db_query("SELECT `configuration_value` AS `table_values`
                         FROM " . TABLE_CONFIGURATION . "
                         WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER';");
          $result2 = xtc_db_fetch_array($getValues2);
          if( isset($result2['table_values']) && $result2['table_values'] != '') {
            $this->shipping_free_shipping = $result2['table_values'];
          }
        }
      }
      
        
    }

	/**
	 * Method sets the "table shipping costs" values
	 */
	function setPaymentTableValues() {
		$explodedValues = array();

		// take the tax data from the db
		$getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
								   FROM " . TABLE_CONFIGURATION . "
								   WHERE `configuration_key` LIKE 'MODULE_SHIPPING_TABLE_TAX_CLASS';");

		$result = xtc_db_fetch_array($getValues);
    if( isset($result['table_values']) && $result['table_values'] != '') {
      $this->shippingtax = $result['table_values'];
    }
    
    
		// take the data from the db
		$getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
								   FROM " . TABLE_CONFIGURATION . "
								   WHERE `configuration_key` LIKE 'MODULE_SHIPPING_TABLE_COST';");

		$result = xtc_db_fetch_array($getValues);

		// the result shouldnt be empty
		// otherwise $this->paymentTableValues stays empty
		// example string: 25:8.50,50:5.50,10000:0.00

		if( isset($result['table_values']) && $result['table_values'] != '') {
			// split die Value at the comma
			$explodedValues = explode(',', $result['table_values']);

			// run through the values and split again at the colon
			// the key is the weight / price and the value is the sc
			foreach($explodedValues as $values) {
				$tmpAr = array();
				$tmpAr = explode(":", $values);

				// are there only numbers?
				if( is_numeric($tmpAr[0]) && is_numeric($tmpAr[1]) ) {
					$this->paymentTableValues[$tmpAr[0]] = $tmpAr[1];
				}
				unset($tmpAr);
			}
		}

		// check what param is used for "table sc": weight or price
		$getPaymentTableMode = xtc_db_query("SELECT `configuration_value` AS `table_mode`
								   			 FROM " . TABLE_CONFIGURATION . "
								   			 WHERE `configuration_key` LIKE 'MODULE_SHIPPING_TABLE_MODE';");
		$result = xtc_db_fetch_array($getPaymentTableMode);
		if(isset($result['table_mode']) && $result['table_mode'] != '') {
			$this->paymentTableMode = $result['table_mode'];
		}
	}
    
    
    
	function checkStandardShippingCostsOption() {
		// free shipping?
    
		if($this->checkShippingCostOption('FREEAMOUNT') > 0  ) {
			$this->freeShipping = true;

			// catch the limit for free shipping
			$getFreeamountValue = xtc_db_query("SELECT `configuration_value` AS `freeShippingValue`
												FROM `configuration`
											 	WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FREEAMOUNT_AMOUNT';");

			$result = xtc_db_fetch_array($getFreeamountValue);

			// if the value of the free shipping value is not set, its 0.00 ( = always free)
			if(isset($result['freeShippingValue']) && is_numeric($result['freeShippingValue'])) {
				$this->freeShippingValue = $result['freeShippingValue'];
			} else {
				$this->freeShippingValue = 0.00;
			}
		}

		if($this->checkShippingCostOption('TABLE') > 0) {
			// table shipping cost
			$this->paymentTable = true;

			// set the values for table sc to get the correct sc for every offer
			$this->setPaymentTableValues();

		} elseif($this->checkShippingCostOption('ITEM') > 0) {
			// sc per item
			$this->paymentItem = true;

			// set the standard shipping costs
			$this->setStandardShippingCosts();
		} elseif($this->checkShippingCostOption('FLAT') > 0) {
			// flat sc
			$this->paymentFlat = true;

			// set the standard shipping costs
			$this->setStandardShippingCosts();
		}
	}
    
    function setStandardShippingCosts() {
		$shippingModul = '';

		if($this->paymentItem === true) {
			$shippingModul = 'MODULE_SHIPPING_ITEM_COST';
      $getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
                     FROM " . TABLE_CONFIGURATION . "
                     WHERE `configuration_key` LIKE 'MODULE_SHIPPING_ITEM_TAX_CLASS';");
		} else {
			$shippingModul = 'MODULE_SHIPPING_FLAT_COST';
      $getValues = xtc_db_query("SELECT `configuration_value` AS `table_values`
                     FROM " . TABLE_CONFIGURATION . "
                     WHERE `configuration_key` LIKE 'MODULE_SHIPPING_FLAT_TAX_CLASS';");
		}  

		// take the tax data from the db

		$result = xtc_db_fetch_array($getValues);
    if( isset($result['table_values']) && $result['table_values'] != '') {
      $this->shippingtax = $result['table_values'];
    }    
    
		$getStandardShippingCosts = xtc_db_query("SELECT `configuration_value` AS `standard_sc`
												  FROM `configuration`
										 		  WHERE `configuration_key` LIKE '{$shippingModul}';");

		$result = xtc_db_fetch_array($getStandardShippingCosts);

		// if $result['standard_sc'] is not set, $this->standardShippingCost stays empty (to be on the safe side)
		if(isset($result['standard_sc'])) {
			$this->standardShippingCost = $result['standard_sc'];
		} else {
			$this->standardShippingCost = '';
		}
	}
    
    
    function checkActivePayment() {
      // run through every payment method
      foreach($this->payment as $singlePayment => $status) {
        // is the pm active?
        $checkPayment = xtc_db_query("SELECT COUNT(*) AS `found`
                        FROM `configuration`
                        WHERE `configuration_key` LIKE 'MODULE_PAYMENT_{$singlePayment}_STATUS'
                        AND `configuration_value` LIKE 'True';");

        $result = xtc_db_fetch_array($checkPayment);
        // if the result is > 0, the pm is active
        if($result['found'] > 0) {
          $this->payment[$singlePayment]['active'] = true;
        }
      }
    }    

	 function checkShippingCostOption($option) {
	   // transform to uppercase
	   $option = strtoupper($option);
	   $checkOption = xtc_db_query("
	    SELECT COUNT(*) AS found
	    FROM configuration
	    WHERE configuration_key LIKE 'MODULE_SHIPPING_{$option}_STATUS'
	    AND configuration_value LIKE 'True';
	   ");
	   $result = xtc_db_fetch_array($checkOption);

	   if (isset($result['found']) && $result['found'] > 0) { // module is active,  check allowed countries
	     $countryOption = xtc_db_query("
	     SELECT COUNT(*) AS found
	     FROM configuration
	     WHERE configuration_key LIKE 'MODULE_SHIPPING_{$option}_ALLOWED' AND
	     (configuration_value LIKE '%DE%' OR configuration_value='');
	    ");
	    $countryOk = xtc_db_fetch_array($countryOption);

	    // if $countryOk['found'] is not set, 0 (country is not activated) will be returned
	    return (isset($countryOk['found'])) ? $countryOk['found'] : 0;
	   } else {
	    return 0;
	   }
	 }
    
    
    function process($file) {
        global $UseGoogle;

        @xtc_set_time_limit(0);
        require(DIR_FS_CATALOG.DIR_WS_CLASSES . 'xtcPrice.php');
        $xtPrice = new xtcPrice("EUR",1);

        $UseGoogle ? $psmgoogle_link="utm_source=preissuchmaschine&utm_medium=cpc&utm_campaign=preissuchmaschine&" : $psmgoogle_link="";

        
        
        $schema = 'ProduktID|Gewicht|EAN|Lagerbestand|Hersteller|ProduktBezeichnung|ArtikelNroderHerstellerArtikelNr|Preis|ProduktLangBeschreibung|ProduktKurzBeschreibung|Lieferzeit|Produktlink|FotoLink|Kategoriename|' ;
        // run through the payment method titles to display them in the header
        foreach($this->payment as $payment => $options) {
          // display only the payment methods that are active (if this is desired)
            $schema .= $options['title']  . "|";
        }        
        $schema .=  "\n";
        $export_query =xtc_db_query("SELECT
                             p.products_id,
                             pd.products_name,
                             pd.products_description,
                             pd.products_short_description,
                             p.products_weight,
                             p.products_ean,
                             p.products_quantity,
                             p.products_model,
                             p.products_shippingtime,
                             p.products_image,
                             p.products_price,
                             p.products_status,
                             p.products_discount_allowed,
                             p.products_tax_class_id,
                             IF(s.status, s.specials_new_products_price, NULL) AS specials_new_products_price,
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
                                         
                                         

	    // get product categorie
            $categorie_query=xtc_db_query("SELECT
                                            categories_id
                                            FROM ".TABLE_PRODUCTS_TO_CATEGORIES."
                                            WHERE products_id='".$products['products_id']."'");
             while ($categorie_data=xtc_db_fetch_array($categorie_query)) {
                    $categories=$categorie_data['categories_id'];
             }
 
            // remove trash in $products_description
            $products_description = strip_tags($products['products_description']);
            $products_description = str_replace(";",", ",$products_description);
            $products_description = str_replace("'",", ",$products_description);
            $products_description = str_replace("\n"," ",$products_description);
            $products_description = str_replace("\r"," ",$products_description);
            $products_description = str_replace("\t"," ",$products_description);
            $products_description = str_replace("\v"," ",$products_description);
            $products_description = str_replace("&quot,"," \"",$products_description);
            $products_description = str_replace("&qout,"," \"",$products_description);
            $products_description = str_replace("|",",",$products_description);
            $products_description = substr($products_description, 0, 253);

            // remove trash in $products_short_description
            $products_short_description = strip_tags($products['products_short_description']);
            $products_short_description = str_replace(";",", ",$products_short_description);
            $products_short_description = str_replace("'",", ",$products_short_description);
            $products_short_description = str_replace("\n"," ",$products_short_description);
            $products_short_description = str_replace("\r"," ",$products_short_description);
            $products_short_description = str_replace("\t"," ",$products_short_description);
            $products_short_description = str_replace("\v"," ",$products_short_description);
            $products_short_description = str_replace("&quot,"," \"",$products_short_description);
            $products_short_description = str_replace("&qout,"," \"",$products_short_description);
            $products_short_description = str_replace("|",",",$products_short_description);
            $products_short_description = substr($products_short_description, 0, 253);
           
            $cat = $this->buildCAT($categories);

            // creates pathes of images, if images are integrated
            if ($products['products_image'] != '')
              { 
	        $image_if_available = HTTP_CATALOG_SERVER . DIR_WS_CATALOG_POPUP_IMAGES .$products['products_image'];
	      }
              else
              {
	        $image_if_available = '';
	      }

        
            //create content
            $schema .= $products['products_id'] .'|'. 
                       number_format($products['products_weight'],2,',','.') .'|' . 
                       $products['products_ean'] .'|' . 
                       $products['products_quantity'] .'|' . 
                       $products['manufacturers_name'] .'|'. 
                       $products['products_name'] .'|' .
                       $products['products_model'] . '|' .
                       number_format($products_price,2,',','.'). '|' .
                       $products_description .'|'.
                       $products_short_description .'|'.
                       xtc_get_shipping_status_name($products['products_shippingtime']). '|' .
                       HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?'.$_POST['campaign']. $psmgoogle_link .xtc_product_link($products['products_id'], $products['products_name']). '|' .
                       $image_if_available . '|' .
                       substr($cat,0,strlen($cat)-2) ."|";

				       foreach($this->payment as $singlePayment => $options) {
                  // display only the payment fee that is active (if this is desired)
                  $sc = $this->getShippingCosts($singlePayment, $products_price, $products['products_weight'],$products['products_tax_class_id']);
                  $schema .=  $sc . "|";

                  // if there's one payment with sc > 0.00, display the sc free comment
                  // exception: cash on delivery
                  if( $singlePayment != 'COD' && $sc > 0.00 ) {
                    $showScFreeComment = true;
                  }
				        }                       
                $schema .= "\n";
        
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

	function getShippingCosts($payment, $price = null, $offerWeight = null, $taxClass = -1) {
		$shippingCost = '';

		// is the is payment active?
		if( $this->payment[$payment]['active'] === true ) {

			// is free delivery active and price equal or higher than the limit?
			if(($this->freeShipping) === true && ($price >= $this->freeShippingValue)) {
				$shippingCost = 0.00;
			}
      elseif (($this->shipping_free_shipping > 0) && ($price > $this->shipping_free_shipping)) {
        $shippingCost = 0.00;
      }
			// is at least one shipping option active?
			elseif(($this->paymentTable === true) || ($this->paymentItem === true) || ($this->paymentFlat === true) ) {

				// first of all we get the standard shipping costs (default sc, per item or table)

				// are the table shipping costs active? Check which table payment option is active
				if($this->paymentTable === true) {

					// run through the table values and check which weight / price matches the offer
					switch($this->paymentTableMode) {
						case 'weight':
							$offerCompareValue = $offerWeight;
						break;
						case 'price':
							$offerCompareValue = $price;
						break;
					}

					if(is_array($this->paymentTableValues) && $offerCompareValue != null) {

						foreach($this->paymentTableValues as $tableModeValue => $tablePrice) {
							// stop the loop if sth. matched
							if($offerCompareValue <= $tableModeValue) {
								$shippingCost = $tablePrice;
								break;
							}
						}

						// If no weight / price was matched accordingly, the last entry in the array is taken
						if($shippingCost == '') {
							end($this->paymentTableValues); // Zeiger an letzte Stelle bewegen
							$shippingCost = current($this->paymentTableValues); // Wert ausgeben auf den der Zeiger aktuell zeigt
							reset($this->paymentTableValues); // Setze Zeiger wieder in Ausgangsposition
						}

					} else {
						// if the table sc values are not correct or the weight / price is null => nothing shall appear in the csv
						$shippingCost = '';
					}
				} else {
					$shippingCost = $this->standardShippingCost;
				}
			}

			// calculate taxes
      if ($this->shippingtax > 0) {
            $tax = $this->TAX[$this->shippingtax];
            $shippingCost = xtc_add_tax($shippingCost, $tax);
      }
      
      
			// cod needs additional calculation
			// the additional cod_fee (if active) depends on the shipping option that is active as the fee can differ
			if($payment == 'COD') {
				 // check if extra fee for "Cash on Delivery" is active

				 // 1. get the db data
				$getCodExtraFeeStatus = xtc_db_query("SELECT `configuration_value` AS `cod_fee_status`
													  FROM `configuration`
													  WHERE `configuration_key` LIKE 'MODULE_ORDER_TOTAL_COD_FEE_STATUS';");

				$result = array();
				$result = xtc_db_fetch_array($getCodExtraFeeStatus);


				// 2. is the fee status active?
				if(isset($result['cod_fee_status']) && $result['cod_fee_status'] == 'true') {
					$modul = '';
					// which shipping option is active?
					if(($this->freeShipping) === true && ($price >= $this->freeShippingValue)) {
						$modul = 'MODULE_ORDER_TOTAL_FREEAMOUNT_FREE';
					} elseif($this->paymentTable === true) {
						$modul = 'MODULE_ORDER_TOTAL_COD_FEE_TABLE';
					} elseif($this->paymentItem === true) {
						$modul = 'MODULE_ORDER_TOTAL_COD_FEE_ITEM';
					} elseif($this->paymentFlat === true) {
						$modul = 'MODULE_ORDER_TOTAL_COD_FEE_FLAT';
					}

					$getCodCost = xtc_db_query("SELECT `configuration_value` AS `cod_cost`
												FROM `configuration`
												WHERE `configuration_key` LIKE '{$modul}';");

					unset($result);
					$result = array();
					$result = xtc_db_fetch_array($getCodCost);
					// Are there any costs?
					if(isset($result['cod_cost']) && $result['cod_cost'] != '') {
						// get the value for the country
						preg_match_all('/DE:([^,]+)?/', $result['cod_cost'], $match);

						// $match[1][0] contains the result in the form of (e.g.) 7.00 or 7
						// to make sure that mistakes like 7.00:9.99 (correct would be 7,00:9.99) are also handled, we check for the colon
						if(preg_match('/:/', $match[1][0])) {
							$tmpArr = explode(':', $match[1][0]);
							$codCost = $tmpArr[0];
						} else {
							$codCost = $match[1][0];
						}

      
						// de we ge a useful value?
						if(isset($codCost) && $codCost != NULL && is_numeric($codCost)) {
              // calculate taxes
              if ($this->shippingtaxcod > 0) {
                    $tax = $this->TAX[$this->shippingtaxcod];
                    $codCost = xtc_add_tax($codCost, $tax);
              }
              $shippingCost += $codCost;
						}
					}
          
          
          
				}

			}

			// format and round numbers
			$shippingCost = number_format($shippingCost, 2, ',', '.');
		}


		return $shippingCost;
	}
    
    
    function buildCAT($catID)
    {

        if (isset($this->CAT[$catID]))
        {
         return  $this->CAT[$catID];
        } else {
           $cat=array();
           $tmpID=$catID;

               while ($this->getParent($catID)!=0 || $catID!=0)
               {
                    $cat_select=xtc_db_query("SELECT categories_name FROM ".TABLE_CATEGORIES_DESCRIPTION." WHERE categories_id='".$catID."' and language_id='".$_SESSION['languages_id']."'");
                    $cat_data=xtc_db_fetch_array($cat_select);
                    $catID=$this->getParent($catID);
                    $cat[]=$cat_data['categories_name'];

               }
               $catStr='';
               for ($i=count($cat);$i>0;$i--)
               {
                  $catStr.=$cat[$i-1].' > ';
               }
               $this->CAT[$tmpID]=$catStr;
        return $this->CAT[$tmpID];
        }
    }

     
    function getParent($catID)
    {
      if (isset($this->PARENT[$catID]))
      {
       return $this->PARENT[$catID];
      } else {
       $parent_query=xtc_db_query("SELECT parent_id FROM ".TABLE_CATEGORIES." WHERE categories_id='".$catID."'");
       $parent_data=xtc_db_fetch_array($parent_query);
       $this->PARENT[$catID]=$parent_data['parent_id'];
       return  $parent_data['parent_id'];
      }
    }


    function display() {
    global $UseGoogle;
  $campaign_array = array(array('id' => '', 'text' => TEXT_NONE));
	$campaign_query = xtc_db_query("select campaigns_name, campaigns_refID from ".TABLE_CAMPAIGNS." order by campaigns_id");
  $PSMFound = false;
	while ($campaign = xtc_db_fetch_array($campaign_query)) {
	  $campaign_array[] = array ('id' => 'refID='.$campaign['campaigns_refID'].'&', 'text' => $campaign['campaigns_name'],);
    $PSMFound |= $campaign['campaigns_refID']=="psm";
	} 
  if (!$PSMFound) {
     xtc_db_query("INSERT INTO ".TABLE_CAMPAIGNS." VALUES (NULL, 'Preissuchmaschine (automatisch)', 'psm', '0', NOW(), NOW())");
  	 $campaign_array[] = array ('id' => 'refID=psm&', 'text' => "Preissuchmaschine (automatisch)",);
  }
 
    return array('text' =>  '<br>' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . MODULE_PREISSUCHMASCHINE_EXPORT_LINK . MODULE_PREISSUCHMASCHINE_FILE . '<br><br>' . MODULE_PREISSUCHMASCHINE_EXPORT_LINK_SEND . 
                          
                            MODULE_PREISSUCHMASCHINE_CAMPAIGNS.'<br>'.
                            MODULE_PREISSUCHMASCHINE_CAMPAIGNS_DESC.
                          	xtc_draw_pull_down_menu('campaign',$campaign_array, 'refID=psm&').'<br>'.                             
                            MODULE_PREISSUCHMASCHINE_psmgoogleHeader.'<br>'.
                            MODULE_PREISSUCHMASCHINE_psmgoogle_DESC.'<br>'.
                          	xtc_draw_radio_field('psmgoogle', 'no',!$UseGoogle).MODULE_PREISSUCHMASCHINE_psmgoogle_NO.'<br>'.
                            xtc_draw_radio_field('psmgoogle', 'yes',$UseGoogle).MODULE_PREISSUCHMASCHINE_psmgoogle_YES.'<br><br>'.
                            MODULE_PREISSUCHMASCHINE_EXPORT_TYPE.'<br>'.
                            MODULE_PREISSUCHMASCHINE_EXPORT.'<br>'.
                          	xtc_draw_radio_field('export', 'no',true).MODULE_PREISSUCHMASCHINE_EXPORT_NO.'<br>'.
                            xtc_draw_radio_field('export', 'yes',false).MODULE_PREISSUCHMASCHINE_EXPORT_YES.'<br><br>' . 
                            str_replace("Exportieren","Produkte aktualisieren",xtc_button(BUTTON_EXPORT)) .
                            xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=preissuchmaschine')));


    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = xtc_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PREISSUCHMASCHINE_STATUS'");
        $this->_check = xtc_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PREISSUCHMASCHINE_FILE', 'preissuchmaschine.csv',  '6', '1', '', now())");
      xtc_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) values ('MODULE_PREISSUCHMASCHINE_STATUS', 'True',  '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
}

    function remove() {
      xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PREISSUCHMASCHINE_STATUS', 'MODULE_PREISSUCHMASCHINE_FILE');
    }

  }
?>