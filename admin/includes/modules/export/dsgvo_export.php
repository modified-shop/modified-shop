<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

require_once (DIR_WS_CLASSES.'order.php');


class dsgvo_export
{
    var $code, $title, $description, $enabled, $properties;

    function __construct() 
    {
        $this->code = 'dsgvo_export';
        $this->title = MODULE_DSGVO_EXPORT_TEXT_TITLE;
        $this->description = MODULE_DSGVO_EXPORT_TEXT_DESCRIPTION;
        $this->sort_order = ((defined('MODULE_DSGVO_EXPORT_SORT_ORDER')) ? MODULE_DSGVO_EXPORT_SORT_ORDER : '');
        $this->enabled = ((defined('MODULE_DSGVO_EXPORT_STATUS') && MODULE_DSGVO_EXPORT_STATUS == 'true') ? true : false);
        $this->properties['btn_edit'] = BUTTON_START;
    }

    function process($file) 
    {
        global $messageStack;
        
        $dsgvo_export = array();
        $customers_query = xtc_db_query("SELECT c.customers_id,
                                                c.customers_cid as cid,
                                                c.customers_vat_id as vat_id,
                                                c.customers_firstname as firstname,
                                                c.customers_lastname as lastname,
                                                c.customers_email_address as email_address,
                                                c.customers_date_added as date_added,
                                                c.customers_last_modified as last_modified
                                           FROM ".TABLE_CUSTOMERS." c
                                          WHERE LOWER(c.customers_email_address) = '".xtc_db_input(strtolower($_POST['customer_email_address']))."'
                                       GROUP BY c.customers_id");
        if (xtc_db_num_rows($customers_query)) {
          while ($customers = xtc_db_fetch_array($customers_query)) {
            $customers_id = $customers['customers_id'];
            $customers = $this->remove_key($customers, 'customers_id');
            
            $dsgvo_export['customers']['customer'][] = $customers;

            // address book
            $address_book_query = xtc_db_query("SELECT ab.entry_company as company,
                                                       ab.entry_firstname as firstname,
                                                       ab.entry_lastname as lastname,
                                                       ab.entry_street_address as street_address,
                                                       ab.entry_suburb as suburb,
                                                       ab.entry_postcode as postcode,
                                                       ab.entry_city as city,
                                                       z.zone_name as state,
                                                       c.countries_name as country,
                                                       ab.address_date_added as date_added,
                                                       ab.address_last_modified as last_modified
                                                  FROM ".TABLE_ADDRESS_BOOK." ab
                                                  JOIN ".TABLE_COUNTRIES." c
                                                       ON c.countries_id = ab.entry_country_id
                                             LEFT JOIN ".TABLE_ZONES." z
                                                       ON z.zone_id = ab.entry_zone_id
                                                          AND z.zone_country_id = ab.entry_country_id
                                                 WHERE ab.customers_id = '".(int)$customers_id."'");
            while ($address_book = xtc_db_fetch_array($address_book_query)) {
              $dsgvo_export['address_books']['address_book'][] = $address_book;
            }
            
            // login
            $customers_info_query = xtc_db_query("SELECT customers_info_date_of_last_logon as date_last_login,
                                                         customers_info_number_of_logons as number_of_logins 
                                                    FROM ".TABLE_CUSTOMERS_INFO." 
                                                   WHERE customers_info_id = '".(int)$customers_id."'");
            while ($customers_info = xtc_db_fetch_array($customers_info_query)) {
              $dsgvo_export['informations']['total'][] = $customers_info;
            }
            
            $address_book_query = xtc_db_query("SELECT customers_ip as ip,
                                                       customers_ip_date as date,
                                                       customers_host as host,
                                                       customers_advertiser as advertiser,
                                                       customers_referer_url as referer_url
                                                  FROM ".TABLE_CUSTOMERS_IP."
                                                 WHERE customers_id = '".(int)$customers_id."'");
            while ($address_book = xtc_db_fetch_array($address_book_query)) {
              $dsgvo_export['informations']['log'][] = $address_book;
            }
            
            $reviews_query = xtc_db_query("SELECT r.customers_name as name,
                                                  r.reviews_rating as rating,
                                                  pd.products_name as product,
                                                  rd.reviews_text as text,
                                                  r.date_added,
                                                  r.last_modified
                                             FROM ".TABLE_REVIEWS." r
                                             JOIN ".TABLE_REVIEWS_DESCRIPTION." rd
                                                  ON r.reviews_id = rd.reviews_id
                                        LEFT JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd
                                                  ON r.products_id = pd.products_id
                                                     AND pd.language_id = rd.languages_id
                                            WHERE customers_id = '".(int)$customers_id."'");
            while ($reviews = xtc_db_fetch_array($reviews_query)) {
              $dsgvo_export['reviews']['review'][] = $reviews;
            }
            
            $orders_query = xtc_db_query("SELECT *
                                            FROM ".TABLE_ORDERS."
                                           WHERE customers_id = '".(int)$customers_id."'");
            while ($orders = xtc_db_fetch_array($orders_query)) {
              $order = new order($orders['orders_id']);
              
              $dsgvo_export['orders']['order'][$orders['orders_id']] = array(
                'customer' => $this->clean_data($order->customer),
                'delivery' => $this->clean_data($order->delivery),
                'billing' => $this->clean_data($order->billing),
                'products' => array(),
              );
              
              foreach ($order->products as $product) {
                $dsgvo_export['orders']['order'][$orders['orders_id']]['products']['product'][] = $this->clean_data($product);
              }
            }
            
          }
        }
        
        $orders_query = xtc_db_query("SELECT *
                                        FROM ".TABLE_ORDERS."
                                       WHERE LOWER(customers_email_address) = '".xtc_db_input(strtolower($_POST['customer_email_address']))."'");
        if (xtc_db_num_rows($customers_query)) {
          while ($orders = xtc_db_fetch_array($orders_query)) {
            $order = new order($orders['orders_id']);
            
            $dsgvo_export['orders']['order'][$orders['orders_id']] = array(
              'customer' => $this->clean_data($order->customer),
              'delivery' => $this->clean_data($order->delivery),
              'billing' => $this->clean_data($order->billing),
              'products' => array(),
            );
            
            if ($order->info['comments'] != '') {
              $dsgvo_export['orders']['order'][$orders['orders_id']]['comment'] = $order->info['comments'];
            }
            
            foreach ($order->products as $product) {
              $dsgvo_export['orders']['order'][$orders['orders_id']]['products']['product'][] = $this->clean_data($product);
            }
          }
        }

        $newsletter_query = xtc_db_query("SELECT customers_firstname as firstname,
                                                 customers_lastname as lastname,
                                                 date_added,
                                                 ip_date_added,
                                                 date_confirmed,
                                                 ip_date_confirmed
                                            FROM ".TABLE_NEWSLETTER_RECIPIENTS."
                                           WHERE LOWER(customers_email_address) = '".xtc_db_input(strtolower($_POST['customer_email_address']))."'");
        if (xtc_db_num_rows($newsletter_query)) {
          $newsletter = xtc_db_fetch_array($newsletter_query);
          $dsgvo_export['newsletter']['customer'] = $newsletter;          
        } 

        $newsletter_history_query = xtc_db_query("SELECT customers_action as action,
                                                         ip_address as ip,
                                                         date_added
                                                    FROM ".TABLE_NEWSLETTER_RECIPIENTS_HISTORY."
                                                   WHERE LOWER(customers_email_address) = '".xtc_db_input(strtolower($_POST['customer_email_address']))."'");
        if (xtc_db_num_rows($newsletter_history_query)) {
          while ($newsletter_history = xtc_db_fetch_array($newsletter_history_query)) {
            $dsgvo_export['newsletter']['history'][$newsletter_history['action']] = $this->clean_data($newsletter_history);
          }
        }
                        
        if (count($dsgvo_export) > 0) {  
          array_walk_recursive($dsgvo_export, function(&$value, $key) {
              if ($value == '') {
                $value = array();
              }
          }); 
          
          $output = fopen("php://output", 'w');
          header("Content-Type:text/xml"); 
          header("Content-Disposition:attachment; filename=customer_export.xml");   
          
          $xml = new DomDocument('1.0', strtoupper($_SESSION['language_charset']));
          $xml->formatOutput = true;
          $xml->appendChild($this->convert('dsgvo', $dsgvo_export, $xml));
          echo $xml->saveXML();
          fclose($output);
          exit();
        } else {
          $messageStack->add_session('keine Kundendaten gefunden!', 'error');
        }
    }

    function convert($node_name, $arr, $xml)
    {
        $node = $xml->createElement($node_name);
        
        if(is_array($arr)){
          foreach($arr as $key=>$value){
            if(is_array($value) && is_numeric(key($value))) {
              foreach($value as $k=>$v){
                $node->appendChild($this->convert($key, $v, $xml));
              }
            } else {
              $node->appendChild($this->convert($key, $value, $xml));
            }
            unset($arr[$key]); //remove the key from the array once done.
          }
        }
        
        if (!is_array($arr)) {
          $node->appendChild($xml->createTextNode($this->bool2str($arr)));
        }
        
        return $node;
    }

    function bool2str($v)
    {
        $v = $v === true ? 'true' : $v;
        $v = $v === false ? 'false' : $v;
        return $v;
    }

    function clean_data($array)
    {
        $array = $this->remove_key($array, 'gender');
        $array = $this->remove_key($array, 'address_format_id');
        $array = $this->remove_key($array, 'country_iso_2');
        $array = $this->remove_key($array, 'format_id');
        $array = $this->remove_key($array, 'address_format_id');
        $array = $this->remove_key($array, 'id');
        $array = $this->remove_key($array, 'ID');
        $array = $this->remove_key($array, 'status');
        $array = $this->remove_key($array, 'status_name');
        $array = $this->remove_key($array, 'status_image');
        $array = $this->remove_key($array, 'customers_status');
        $array = $this->remove_key($array, 'csID');
        $array = $this->remove_key($array, 'cIP');
        $array = $this->remove_key($array, 'orders_id');
        $array = $this->remove_key($array, 'price_origin');
        $array = $this->remove_key($array, 'allow_tax');
        $array = $this->remove_key($array, 'qty');
        $array = $this->remove_key($array, 'opid');
        $array = $this->remove_key($array, 'discount');
        $array = $this->remove_key($array, 'discount_made');
        $array = $this->remove_key($array, 'orders_products_attributes_id');
        $array = $this->remove_key($array, 'orders_id');
        $array = $this->remove_key($array, 'orders_products_id');
        $array = $this->remove_key($array, 'options_values_price');
        $array = $this->remove_key($array, 'price_prefix');
        $array = $this->remove_key($array, 'orders_products_options_id');
        $array = $this->remove_key($array, 'orders_products_options_values_id');
        $array = $this->remove_key($array, 'options_values_weight');
        $array = $this->remove_key($array, 'prefix');
        $array = $this->remove_key($array, 'products_options');
        $array = $this->remove_key($array, 'products_options_values');
        $array = $this->remove_key($array, 'weight_prefix');
        $array = $this->remove_key($array, 'price');
        $array = $this->remove_key($array, 'discount_made');
        $array = $this->remove_key($array, 'action');        
        
        return $array;
    }

    function remove_key($array, $key)
    {
        if (isset($array[$key])) {
          unset($array[$key]);
          if (isset($array['attributes'])) {
            $array['attributes'] = $this->clean_data($array['attributes']);
          }
        } elseif ($this->isAssoc($array) === false) {
          foreach ($array as $k => $product) {
            $array[$k] = $this->clean_data($product);
          }
        }
        
        return $array;
    }

    function isAssoc($array)
    {
        if (array() === $array) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }

    function display() 
    {
        return array('text' => MODULE_DSGVO_EXPORT_SEARCH_TITLE.
                               MODULE_DSGVO_EXPORT_SEARCH_DESC.
                               xtc_draw_input_field('customer_email_address', '', 'placeholder="E-Mail Adresse"').
                               '<br><br>' . xtc_button(BUTTON_EXPORT) . '&nbsp;' .
                               xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module='.$this->code))
                     );
    }

    function check() 
    {
        if(!isset($this->_check)) {
          $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_DSGVO_EXPORT_STATUS'");
          $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    function install() 
    {
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value,  configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_DSGVO_EXPORT_STATUS', 'true',  '6', '1', 'xtc_cfg_select_option(array(\'true\', \'false\'), ', now())");
    }

    function remove()
    {
        xtc_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE_DSGVO_EXPORT_%'");
    }

    function keys() 
    {
        return array();
    }
}
?>