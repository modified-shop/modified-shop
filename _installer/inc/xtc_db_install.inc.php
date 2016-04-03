<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_db_install.inc.php 5218 2013-07-22 14:49:40Z Tomcraft $   

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(database.php,v 1.2 2002/03/02); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_db_install.inc.php,v 1.3 2003/08/13); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
function xtc_db_install($database, $type, $sql_file) {
    global $db_error;
    
    if (!$type) echo 'TYPE ERROR: xtc_db_install<br>';
    
    $db_error = false;

    if (!@xtc_db_select_db($database, $type)) {
      if (@xtc_db_query_installer("CREATE DATABASE '" . xtc_db_input_installer($database) . "'", $type)) {
        xtc_db_select_db($database, $type);
      } else {
        $db_error = xtc_db_error_installer($type);
      }
    }

    if (!$db_error) {
      if (file_exists($sql_file)) {
        $fd = fopen($sql_file, 'rb');
        $restore_query = fread($fd, filesize($sql_file));
        fclose($fd);
      } else {
        $db_error = 'SQL file does not exist: ' . $sql_file;
        return false;
      }

      $sql_array = array();
      $sql_length = strlen($restore_query);
      $pos = strpos($restore_query, ';');
      for ($i=$pos; $i<$sql_length; $i++) {
        if ($restore_query[0] == '#') {
          $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
          $sql_length = strlen($restore_query);
          $i = strpos($restore_query, ';')-1;
          continue;
        }
        if ($restore_query[($i+1)] == "\n") {
          for ($j=($i+2); $j<$sql_length; $j++) {
            if (trim($restore_query[$j]) != '') {
              $next = substr($restore_query, $j, 6);
              if ($next[0] == '#') {
                // find out where the break position is so we can remove this line (#comment line)
                for ($k=$j; $k<$sql_length; $k++) {
                  if ($restore_query[$k] == "\n") break;
                }
                $query = substr($restore_query, 0, $i+1);
                $restore_query = substr($restore_query, $k);
                // join the query before the comment appeared, with the rest of the dump
                $restore_query = $query . $restore_query;
                $sql_length = strlen($restore_query);
                $i = strpos($restore_query, ';')-1;
                continue 2;
              }
              break;
            }
          }
          if ($next == '') { // get the last insert query
            $next = 'insert';
          }
          if ( (preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop t/i', $next)) ) { // Hetfield - 2009-08-19 - replaced deprecated function eregi with preg_match to be ready for PHP >= 5.3
            $next = '';
            $sql_array[] = substr($restore_query, 0, $i);
            $restore_query = ltrim(substr($restore_query, $i+1));
            $sql_length = strlen($restore_query);
            $i = strpos($restore_query, ';')-1;
          }
        }
      }

      xtc_db_query_installer("DROP TABLE IF EXISTS address_book, 
                                                   address_format, 
                                                   admin_access,
                                                   banktransfer, 
                                                   banktransfer_blz,
                                                   banners, 
                                                   banners_history, 
                                                   campaigns, 
                                                   campaigns_ip, 
                                                   card_blacklist, 
                                                   categories, 
                                                   categories_description, 
                                                   cm_file_flags,
                                                   configuration, 
                                                   configuration_group, 
                                                   content_manager,
                                                   counter, 
                                                   counter_history, 
                                                   countries, 
                                                   coupon_email_track,
                                                   coupon_gv_customer,
                                                   coupon_gv_queue,
                                                   coupon_redeem_track,
                                                   coupons, 
                                                   coupons_description, 
                                                   currencies, 
                                                   customers, 
                                                   customers_basket, 
                                                   customers_basket_attributes, 
                                                   customers_info, 
                                                   customers_ip, 
                                                   customers_memo,
                                                   customers_status,
                                                   customers_status_history,
                                                   database_version, 
                                                   geo_zones, 
                                                   languages, 
                                                   manufacturers, 
                                                   manufacturers_info, 
                                                   module_backup,
                                                   module_newsletter, 
                                                   newsletters,
                                                   newsletters_history, 
                                                   newsletter_recipients, 
                                                   orders, 
                                                   orders_products, 
                                                   orders_status, 
                                                   orders_status_history, 
                                                   orders_products_attributes, 
                                                   orders_products_download, 
                                                   orders_recalculate,
                                                   orders_total, 
                                                   payment_moneybookers,
                                                   payment_moneybookers_countries,
                                                   personal_offers_by_customers_status_0, 
                                                   personal_offers_by_customers_status_1, 
                                                   personal_offers_by_customers_status_2, 
                                                   personal_offers_by_customers_status_3, 
                                                   personal_offers_by_customers_status_4, 
                                                   products, 
                                                   products_attributes, 
                                                   products_attributes_download, 
                                                   products_content, 
                                                   products_description, 
                                                   products_graduated_prices,
                                                   products_images, 
                                                   products_notifications,
                                                   products_options, 
                                                   products_options_values, 
                                                   products_options_values_to_products_options, 
                                                   products_to_categories, 
                                                   products_vpe, 
                                                   products_xsell, 
                                                   products_xsell_grp_name, 
                                                   reviews, 
                                                   reviews_description, 
                                                   sessions, 
                                                   shipping_status,
                                                   shop_configuration,
                                                   specials, 
                                                   tax_class, 
                                                   tax_rates, 
                                                   geo_zones, 
                                                   whos_online, 
                                                   zones, 
                                                   zones_to_geo_zones", $type);

      for ($i=0; $i<sizeof($sql_array); $i++) {
        if (INSTALL_CHARSET == 'utf8') {
          $sql_array[$i] = mb_convert_encoding($sql_array[$i], 'utf-8', 'ISO-8859-15');
        }
        xtc_db_query_installer($sql_array[$i], $type);
      }
    } else {
      return false;
    }
  }
 ?>