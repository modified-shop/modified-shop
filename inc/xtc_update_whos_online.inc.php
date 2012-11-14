<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(whos_online.php,v 1.8 2003/02/21); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_update_whos_online.inc.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_update_whos_online.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  function xtc_update_whos_online() {

    $crawler = 0; //DokuMan - 2011-05-13 - Added search engine crawler determination functionality

    if (isset($_SESSION['customer_id'])) {
      $wo_customer_id = (int)$_SESSION['customer_id'];

      $customer_query = xtc_db_query("select
                                      customers_firstname,
                                      customers_lastname
                                      from " . TABLE_CUSTOMERS . "
                                      where customers_id = '" . $wo_customer_id . "'");
      $customer = xtc_db_fetch_array($customer_query);

      $wo_full_name = xtc_db_input($customer['customers_firstname'] . ' ' . $customer['customers_lastname']);
    } else {
      $wo_customer_id = '';
      //BOF - DokuMan - 2011-05-13 - Added search engine crawler determination functionality
      //$wo_full_name = TEXT_GUEST;
      $crawler = xtc_check_agent();
      if ($crawler !== 0) {
        $wo_full_name = '['.TEXT_SEARCH_ENGINE_AGENT.']';
      } else {
        $wo_full_name = TEXT_GUEST; //DokuMan - 2011-03-18 - use language dependent constant here
      }
      //EOF - DokuMan - 2011-05-13 - Added search engine crawler determination functionality
    }
    //BOF - DokuMan - 2011-05-13 - Added search engine crawler determination functionality
    //$wo_session_id = xtc_session_id();
    if ($crawler !== 0) {
      $wo_session_id = '';
    } else {
      $wo_session_id = xtc_session_id();
    }
    //EOF - DokuMan - 2011-05-13 - Added search engine crawler determination functionality

    //BOF - Dokuman - 2009-10-28 - Who is online doesn't show any IP addresses and URLs (added http_referer)
    $wo_ip_address = xtc_db_input($_SERVER['REMOTE_ADDR']);
    $wo_last_page_url = xtc_db_input($_SERVER['REQUEST_URI']);
    $wo_referer = xtc_db_input(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '---');
    //EOF - Dokuman - 2009-10-28 - Who is online doesn't show any IP addresses and URLs (added http_referer)

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

    // remove entries that have expired
    xtc_db_query("delete from " . TABLE_WHOS_ONLINE . " where time_last_click < '" . $xx_mins_ago . "'");

    $stored_customer_query = xtc_db_query("select count(*) as count from " . TABLE_WHOS_ONLINE . " where session_id = '" . $wo_session_id . "'");
    $stored_customer = xtc_db_fetch_array($stored_customer_query);

    if ($stored_customer['count'] > 0) {
        xtc_db_query("
        update " . TABLE_WHOS_ONLINE . "
        set customer_id = '" . $wo_customer_id . "',
        full_name = '" . $wo_full_name . "',
        ip_address = '" . $wo_ip_address . "',
        time_last_click = '" . time() . "',
        last_page_url = '" . $wo_last_page_url . "',
        http_referer = '" . $wo_referer . "'
        where session_id = '" . $wo_session_id . "'");
    } else {
    //BOF - Dokuman - 2009-10-28 - Who is online: added http_referer
    //xtc_db_query("insert into " . TABLE_WHOS_ONLINE . " (customer_id, full_name, session_id, ip_address, time_entry, time_last_click, last_page_url) values ('" . $wo_customer_id . "', '" . $wo_full_name . "', '" . $wo_session_id . "', '" . $wo_ip_address . "', '" . $current_time . "', '" . $current_time . "', '" . $wo_last_page_url . "')");
      xtc_db_query("
      insert into " . TABLE_WHOS_ONLINE . "
        (customer_id,
        full_name,
        session_id,
        ip_address,
        time_entry,
        time_last_click,
        last_page_url,
        http_referer)
      values (
      '" . $wo_customer_id . "',
      '" . $wo_full_name . "',
      '" . $wo_session_id . "',
      '" . $wo_ip_address . "',
      '" . $current_time . "',
      '" . $current_time . "',
      '" . $wo_last_page_url . "',
      '" . $wo_referer . "')"
      );
    //BOF - Dokuman - 2009-10-28 - Who is online: added http_referer
    }
  }
?>