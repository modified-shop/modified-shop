<?php
/* --------------------------------------------------------------
  $Id$

  modified eCommerce Shopsoftware
  http://www.modified-shop.org

  Copyright (c) 2009 - 2013 [www.modified-shop.org]
  --------------------------------------------------------------
  based on:
  (c) 2013 Falk Wolsky
  
  Released under the GNU General Public License
  --------------------------------------------------------------*/

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

// include needed classes
require_once (DIR_WS_CLASSES . 'order.php');
require_once (DIR_FS_CATALOG . 'includes/classes/xtcPrice.php');
require_once (DIR_FS_INC . 'xtc_get_attributes_model.inc.php');

class easybillcsv {

	var $code, $title, $description, $enabled, $sort_order;

	function __construct() {

    $this->code = 'easybillcsv';
    $this->title = MODULE_EASYBILL_CSV_TEXT_TITLE;
    $this->description = MODULE_EASYBILL_CSV_TEXT_DESCRIPTION;
    $this->sort_order = '1';
    $this->enabled = ((MODULE_EASYBILL_CSV_STATUS == 'True') ? true : false);

    $this->from_order_date = isset($_POST['first_order_date']) ? $_POST['first_order_date'] : date("Y-m-d", time());
    $this->export = isset($_POST['export']) ? $_POST['export'] : 'no';

    // orders status
    $this->from_orders_status = DEFAULT_SHIPPING_STATUS_ID;
    if (count($_POST['orders_status']) > 0) {
      $this->from_orders_status = implode("', '", $_POST['orders_status']);
    }

    // customers status
    $this->from_customers_status = DEFAULT_CUSTOMERS_STATUS_ID;
    if (count($_POST['customers_status']) > 0) {
      $this->from_customers_status = implode("', '", $_POST['customers_status']);
    } 
	}

	function process($file) {
    global $xtPrice, $messageStack;
	  	  
	  if ($this->export == 'cron') {
	    $url = HTTP_SERVER.DIR_WS_CATALOG.'api/easybill/easybillcsv.php?token='.MODULE_EASYBILL_CSV_CRON_TOKEN.'&customers_status='.implode(',', $_POST['customers_status']).'&orders_status='.implode(',', $_POST['orders_status']);
	    xtc_db_query("UPDATE ". TABLE_CONFIGURATION ." SET configuration_value = '".$url."' WHERE configuration_key = 'MODULE_EASYBILL_CSV_CRON_URL'");
	    xtc_db_query("UPDATE easybill_last_export SET last_exported = '".$_POST['first_order_date']."'");
	    xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=easybillcsv&action=edit'));
	  }
	  
		@xtc_set_time_limit(0);
                                
		$export_query = xtc_db_query("SELECT DISTINCT o.orders_id 
                                    FROM ".TABLE_ORDERS." o
                                    JOIN ".TABLE_ORDERS_STATUS_HISTORY." osh
                                      ON o.orders_id = osh.orders_id	
                                   WHERE (o.orders_status IN ('" . $this->from_orders_status . "') 
                                          OR osh.orders_status_id IN ('" . $this->from_orders_status . "'))
                                     AND (o.last_modified >= '". date( "Y-m-d H:i:s", strtotime($this->from_order_date)) . "'
                                          OR o.date_purchased >= '". date( "Y-m-d H:i:s", strtotime($this->from_order_date)) . "')
                                     AND o.customers_status IN ('" . $this->from_customers_status . "')
                                ORDER BY o.orders_id");

    $filecontent_header = array('order_number',
                                'purchase_date',
                                'shipping_date',
                                'payment_date',
                                'currency',
                                'order_shipping_price',
                                'payment_type',
                                'customer_number',
                                'email',
                                'phone_number',
                                'firstname',
                                'lastname',
                                'name',
                                'street',
                                'zipcode',
                                'city',
                                'state',
                                'country',
                                'vat_id',
                                'tax_type',
                                'shipping_firstname',
                                'shipping_lastname',
                                'shipping_name',
                                'shipping_street',
                                'shipping_zipcode',
                                'shipping_city',
                                'shipping_state',
                                'shipping_country',
                                'sku',
                                'item_type',
                                'item_number',
                                'title',
                                'quantity',
                                'item_price',
                                'vat_percent',
                                );

    if (xtc_db_num_rows($export_query) > 0) {
    
      // mark the last export
      xtc_db_query('UPDATE easybill_last_export SET last_exported = now()');

      $count=0;
      while ($export = xtc_db_fetch_array($export_query)) {
      
        $order = new order($export['orders_id']);
        $order->delivery['country_id'] = $this->get_country_id($order->delivery['country_iso_2']);
        $order->delivery['zone_id'] = xtc_not_null($order->delivery['state']) ? $this->get_zone_id($order->delivery['country_id'], $order->delivery['state']) : '';

        $xtPrice = new xtcPrice($order->info['currency'], $order->info['status']);
        
        $easybill_export[$count] =  array('order_number' => $order->info['order_id'],
                                          'purchase_date' => date("Y-m-d", strtotime($order->info['date_purchased'])),
                                          'shipping_date' => '',
                                          'payment_date' => '',
                                          'currency' => $order->info['currency'],
                                          'order_shipping_price' => '',
                                          'payment_type' => $this->get_payment_name($order->info['payment_class'], $order->info['language']),
                                          'customer_number' => xtc_not_null($order->customer['csID']) ? $order->customer['csID'] : $order->customer['id'],
                                          'email' => $order->customer['email_address'],
                                          'phone_number' => $order->customer['telephone'],
                                          'firstname' => $order->customer['firstname'],
                                          'lastname' => $order->customer['lastname'],
                                          'name' => $order->customer['company'],
                                          'street' => $order->customer['street_address'],
                                          'zipcode' => $order->customer['postcode'],
                                          'city' => $order->customer['city'],
                                          'state' => $order->customer['state'],
                                          'country' => $order->customer['country'],
                                          'vat_id' => $order->customer['vat_id'],
                                          'tax_type' => '',
                                          'shipping_firstname' => $order->delivery['firstname'],
                                          'shipping_lastname' => $order->delivery['lastname'],
                                          'shipping_name' => $order->delivery['company'],
                                          'shipping_street' => $order->delivery['street_address'],
                                          'shipping_zipcode' => $order->delivery['postcode'],
                                          'shipping_city' => $order->delivery['city'],
                                          'shipping_state' => $order->delivery['state'],
                                          'shipping_country' => $order->delivery['country']
                                          );

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
        
          $attributes = '';
          $attributes_total = 0;
          if ((isset ($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0)) {
            for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
              $attributes_model = $order->products[$i]['attributes'][$j]['attributes_model'];
              if ($attributes_model == '') {
                $attributes_model = xtc_get_attributes_model($order->products[$i]['id'], $order->products[$i]['attributes'][$j]['value'],$order->products[$i]['attributes'][$j]['option']);
              }
              $attributes_value = trim($order->products[$i]['attributes'][$j]['value']);
              //$products_array attributes output adjustments (overrides)
              $products_array[$i]['attributes'][$j]['value'] = $attributes_value;
              $products_array[$i]['attributes'][$j]['price'] = $attributes_price;
              if ($attributes_value != '') {
                $attributes .= ' ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $attributes_value;
                $attributes_total += $order->products[$i]['attributes'][$j]['price'];
              }
            }
          }

          if ($order->products[$i]['allow_tax'] == '1') {
            $xtPrice->show_price_tax = '1';
          }
        
          $easybill_export_positions[$count][$i] =  array('sku' => $order->products[$i]['id'],
                                                          'item_type' => 'item',
                                                          'item_number' => (xtc_not_null($order->products[$i]['model']) ? $order->products[$i]['model'] : $order->products[$i]['id']) . $attributes_model,
                                                          'title' => $order->products[$i]['name'] . (xtc_not_null($attributes) ? ' |'.$attributes : ''),
                                                          'quantity' => $order->products[$i]['qty'],
                                                          'item_price' => (($xtPrice->show_price_tax != '0') ? $order->products[$i]['price'] : $this->xtcAddTax($order->products[$i]['price'], $order->products[$i]['tax'])),
                                                          'vat_percent' => $order->products[$i]['tax'],
                                                          );
        } 
        
        if ($xtPrice->show_price_tax == '0' && $order->delivery['country_iso_2'] != 'DE') {
          $easybill_export[$count]['tax_type'] = $this->get_tax_type($order->delivery['country_iso_2']) ? 'intra-community-trade' : 'export';
        }
        
        for ($t=0, $n=sizeof($order->totals); $t<$n; $t++) {
          switch ($order->totals[$t]['class']) {
          
            case 'ot_subtotal':
            case 'ot_tax':
            case 'ot_subtotal_no_tax':
            case 'ot_total':
              // muss nicht übergeben werden
              break;
   
            case 'ot_shipping':
              $shipping_tax = $this->getShippingTax($order->info['shipping_class'], $order->delivery['country_id'], $order->delivery['country']);  
              $easybill_export[$count]['order_shipping_price'] = (($xtPrice->show_price_tax != '0') ? $order->totals[$t]['value'] : $this->xtcAddTax($order->totals[$t]['value'], $shipping_tax));
              //$easybill_export[$count]['order_shipping_price'] = $order->totals[$t]['value'];
              break;

            case 'ot_payment':
            case 'ot_billpay_fee':
            case 'ot_billpaybusiness_fee':
            case 'ot_billpaydebit_fee':
            case 'ot_billpaytc_surcharge':
            case 'ot_coupon':
            case 'ot_discount':
            case 'ot_gv':
            case 'ot_ps_fee':
            case 'ot_loworderfee':
            case 'ot_cod_fee':
            case 'ot_shippingfee':
              $ot_total_tax = $this->getOrderTotalTax($order->totals[$t]['class']);
              $easybill_export_positions[$count][$i] =  array('sku' => '',
                                                              'item_type' => 'discount',
                                                              'item_number' => '',
                                                              'title' => rtrim(strip_tags($order->totals[$t]['title'])),
                                                              'quantity' => '1',
                                                              'item_price' => $order->totals[$t]['value'],
                                                              'vat_percent' => $ot_total_tax,
                                                              );
              $i++;
              break;

            default:
              $default_tax = xtc_get_tax_rate(MODULE_EASYBILL_STANDARD_TAX_CLASS, $order->customer['country_id'], $order->customer['zone_id']);
              $easybill_export_positions[$count][$i] =  array('sku' => '',
                                                              'item_type' => 'item',
                                                              'item_number' => '',
                                                              'title' => rtrim(strip_tags($order->totals[$t]['title'])),
                                                              'quantity' => '1',
                                                              'item_price' => $order->totals[$t]['value'],
                                                              'vat_percent' => $default_tax,
                                                              );
              $i++;
              break;
          
          }
        }

        $easybill_export_temp = $easybill_export[$count];
        foreach ($easybill_export_positions[$count] as $positions) {
          $easybill_export[$count] = array_merge($easybill_export_temp, $positions);
          $count++;
        }
        unset($easybill_export_temp);
        
        if (is_array($easybill_export[$count])) {
          $count++;
        }
      }

      // create the File
      if (xtc_not_null($file) && strpos($file, '.csv') !== false) {
        $filename = $file;
      } else {
        $filename = 'easybill_' . time() . '.csv';
      }

      file_put_contents(DIR_FS_CATALOG.'export/'.$filename, encode_utf8('#! exported-by: modified-shop.org' . "\n"));
      file_put_contents(DIR_FS_CATALOG.'export/'.$filename, encode_utf8(implode(';', $filecontent_header) . "\n"), FILE_APPEND);      
      for ($w=0, $n=sizeof($easybill_export); $w<$n; $w++) {
        file_put_contents(DIR_FS_CATALOG.'export/'.$filename, '"' . encode_utf8(implode('";"', $this->convert($easybill_export[$w])) . '"' . "\n"), FILE_APPEND);
      }

      if ($this->export == 'yes') {
        $fp = fopen(DIR_FS_CATALOG.'export/'.$filename, "rb");
        $buffer = fread($fp, filesize(DIR_FS_CATALOG.'export/'.$filename));
        fclose($fp);
        header('Content-type: application/x-octet-stream');
        header('Content-disposition: attachment; filename='.$filename);
        echo $buffer;
        @unlink(DIR_FS_CATALOG.'export/'.$filename);
        exit();
      }
    } else {
      if (defined('RUN_MODE_ADMIN')) {
        $messageStack->add_session(MODULE_EASYBILL_CSV_ERROR, 'error');
      }
    }
	}

  function convert($string) {
    if (is_array($string)) {
      foreach ($string as $key => $value) {
        $string[$key] = $this->convert($value);
      }
    } else {
      $string = decode_htmlentities($string);
      $string = str_replace('"', '""', $string);
    }
    return $string;
  }

  function xtcAddTax($nPrice, $tax) {
    global $xtPrice;
    
    $bPrice = $xtPrice->xtcAddTax($nPrice, $tax);
    $bPrice = round($bPrice, $xtPrice->currencies[$xtPrice->actualCurr]['decimal_places']);
    
    return $bPrice;
  }
    
  function get_payment_name($payment_method, $language) {
    if (file_exists(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/'.$payment_method.'.php')){
      include(DIR_FS_CATALOG.'lang/'.$language.'/modules/payment/'.$payment_method.'.php');
      $payment_method = constant(strtoupper('MODULE_PAYMENT_'.$payment_method.'_TEXT_TITLE'));
    }
    return $payment_method;
  }

  function getShippingTax($shipping_class, $country_id, $zone_id) {
  
    require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
    $shipping_class = explode('_', $shipping_class);
    if (defined(strtoupper('MODULE_SHIPPING_'.$shipping_class[0].'_TAX_CLASS'))) {
      return xtc_get_tax_rate(constant(strtoupper('MODULE_SHIPPING_'.$shipping_class[0].'_TAX_CLASS')), $country_id, $zone_id);
    } else {
      return '0';
    }
  }

  function getOrderTotalTax($type) {
  
    $type = explode('_', $type, 2);
    require_once (DIR_FS_INC.'xtc_get_tax_rate.inc.php');
    if (defined(strtoupper('MODULE_ORDER_TOTAL_'.$type[1].'_TAX_CLASS'))) {
      return xtc_get_tax_rate(constant(strtoupper('MODULE_ORDER_TOTAL_'.$type[1].'_TAX_CLASS')), $this->customer['country_id'], $this->customer['zone_id']);
    } else {
      return '0';
    }
  }
  
  function get_country_id($country_iso) {
    $country_query = xtc_db_query("SELECT countries_id 
                                     FROM ".TABLE_COUNTRIES." 
                                    WHERE countries_iso_code_2 = '".$country_iso."'");
    $country = xtc_db_fetch_array($country_query);
    
    return $country['countries_id'];
  }
  
  function get_zone_id($country_id, $state) {
    $zones_query = xtc_db_query("SELECT zone_id 
                                   FROM ".TABLE_ZONES." 
                                  WHERE zone_country_id = '".$country_id."' 
                                    AND zone_name = '".xtc_db_input($state)."'");
    $zones = xtc_db_fetch_array($zones_query);
    
    return $zones['zones_id'];
  }

  function get_tax_type($iso2code) {
    $eu_countries_query = xtDBquery("SELECT c.countries_iso_code_2
                                       FROM ".TABLE_COUNTRIES." c
                                       JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz 
                                            ON c.countries_id = gz.zone_country_id
                                      WHERE gz.geo_zone_id = 5
                                    ");

    if (xtc_db_num_rows($eu_countries_query, true)) {
      $eu_countries = array ();
      while ($eu_countries_values = xtc_db_fetch_array($eu_countries_query, true)) {
        $eu_countries[] = $eu_countries_values['countries_iso_code_2'];
      }
    }

    if (!in_array($iso2code, $eu_countries)) {
      return true;
    }
    return '';
  }

	function display() {

		$customers_status_params = 'multiple noStyling="1" style="-moz-appearance: -moz-gtk-info-bar;"';
		$customers_statuses_array = xtc_get_customers_statuses();

		$orders_status_params = 'multiple noStyling="1" style="-moz-appearance: -moz-gtk-info-bar;"';
		$orders_status = array();
		$orders_status_query=xtc_db_query("SELECT * FROM orders_status WHERE language_id='".(int)$_SESSION['languages_id']."'" );
		while ($orders_status_entry = xtc_db_fetch_array($orders_status_query)) {
			$orders_status[] = array('id' => $orders_status_entry['orders_status_id'], 'text'=> $orders_status_entry['orders_status_name']);
		}

		return array('text' => 
			MODULE_EASYBILL_CSV_ORDER_DATE_TITLE . '<br/>' . 
			MODULE_EASYBILL_CSV_ORDER_DATE_DESC . '<br/>' . 
			xtc_draw_input_field('first_order_date', $this->from_order_date, 'id="first_order_date" style="width:150px;"', true) .  '<br/><br/>' . 
        "<style type='text/css'>@import url('http://code.jquery.com/ui/1.10.1/themes/base/jquery-ui.css');</style>			
         <script type='text/javascript' src='http://code.jquery.com/jquery-1.9.1.js'></script>
         <script type='text/javascript' src='http://code.jquery.com/ui/1.10.1/jquery-ui.js'></script>
         <script>$(document).ready(function(){ $('#first_order_date').datepicker({ dateFormat: 'yy-mm-dd'});});</script>" . 
			
			MODULE_EASYBILL_CSV_ORDER_STATUS_TITLE . '<br/>' . 
			MODULE_EASYBILL_CSV_ORDER_STATUS_DESC . '<br/>' . 
			xtc_draw_pull_down_menu('orders_status[]', $orders_status, $this->from_orders_status, $orders_status_params, true) . '<br><br/>' . 
			
			MODULE_EASYBILL_CSV_CUSTOMER_STATUS_TITLE . '<br/>' . 
			MODULE_EASYBILL_CSV_CUSTOMER_STATUS_DESC . '<br/>' . 
			xtc_draw_pull_down_menu('customers_status[]', $customers_statuses_array, $this->from_customers_status, $customers_status_params, true) . '<br><br/>' .
			
			(xtc_not_null(MODULE_EASYBILL_CSV_CRON_URL) ? 
			  MODULE_EASYBILL_CSV_CRON_URL_TITLE . '<br/>' .
			  MODULE_EASYBILL_CSV_CRON_URL_DESC . '<br/><br/>' .
			  '<b>' . MODULE_EASYBILL_CSV_CRON_URL . '</b><br/><br/>' 
			: '') .
			
			MODULE_EASYBILL_CSV_EXPORT_TYPE.'<br/>' . 
			MODULE_EASYBILL_CSV_EXPORT.'<br/>' . 
			xtc_draw_radio_field('export', 'no', false).MODULE_EASYBILL_CSV_EXPORT_NO.'<br/>' .
			xtc_draw_radio_field('export', 'yes', true).MODULE_EASYBILL_CSV_EXPORT_YES.'<br/>' . 
			xtc_draw_radio_field('export', 'cron', false).MODULE_EASYBILL_CSV_EXPORT_CRON.'<br/><br/><br/>' . 
			
			xtc_button(BUTTON_START) . '&nbsp;&nbsp;' .
			xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=easybillcsv'))
		);

	}

	function check() {
		if (!isset($this->_check)) {
			$check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_EASYBILL_CSV_STATUS'");
			$this->_check = xtc_db_num_rows($check_query);
		}
		return $this->_check;
	}

	function install() {
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_CSV_STATUS', 'True', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) VALUES ('MODULE_EASYBILL_CSV_CRON_TOKEN', MD5(RAND()), '6', '2', now())");
		xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_EASYBILL_CSV_CRON_URL', '', '6', '2', 'xtc_cfg_textarea(', now())");
		xtc_db_query("CREATE TABLE IF NOT EXISTS `easybill_last_export` (`last_exported` datetime NOT NULL DEFAULT '0000-00-00 00:00:00', PRIMARY KEY (`last_exported`));");
		xtc_db_query("INSERT INTO easybill_last_export (last_exported) VALUES ('0000-00-00 00:00:00');");
	}

	function remove() {
		xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('MODULE_EASYBILL_CSV_STATUS', 'MODULE_EASYBILL_CSV_CRON_TOKEN', 'MODULE_EASYBILL_CSV_CRON_URL')");
		xtc_db_query("DROP TABLE IF EXISTS `easybill_last_export`;");
	}

	function keys() {
	  $keys = array();
	  if (xtc_not_null(MODULE_EASYBILL_CSV_CRON_URL) && !isset($_GET['action'])) {
	    $keys = array('MODULE_EASYBILL_CSV_CRON_URL');
	  }
		return $keys;
	}

}
?>