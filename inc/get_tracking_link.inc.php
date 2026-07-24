<?php
  /* --------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2015 Timo Paul Dienstleistungen

   Released under the GNU General Public License
   --------------------------------------------------------------*/

  function get_tracking_link($orders_id, $lang_code, $tracking_id = array()) {
    if ($lang_code != 'de' && $lang_code != 'en') {
      $lang_code = DEFAULT_LANGUAGE;
    }
    $where = '';
    if (!is_array($tracking_id)) {
      $tracking_id = array($tracking_id);
    }
    if (count($tracking_id) > 0) {
      $tracking_ids = array();
      foreach ($tracking_id as $id) {
        if (is_int($id) || (is_string($id) && ctype_digit($id))) {
          $tracking_ids[] = (int)$id;
        }
      }
      if (count($tracking_ids) > 0) {
        $where = ' AND ortr.tracking_id IN ('.implode(', ', $tracking_ids).')';
      } else {
        $where = ' AND 1 = 0'; // invalid tracking_id provided, return no results
      }
    }
    $parcel_link = array();
    $tracking_links_query = xtc_db_query("SELECT * 
                                            FROM ".TABLE_ORDERS_TRACKING." ortr
                                            JOIN ".TABLE_CARRIERS." ca
                                                 ON ortr.carrier_id = ca.carrier_id
                                           WHERE ortr.orders_id = '".(int)$orders_id."'
                                                 ".$where."
                                           ORDER BY ortr.tracking_id ASC");
    if (xtc_db_num_rows($tracking_links_query) > 0) {
      $i = 0;
      while ($tracking_link = xtc_db_fetch_array($tracking_links_query)) {
        $parcel_link[$i] = $tracking_link;
        $parcel_link[$i]['tracking_link'] = str_replace(array('$1', '$2', '$3', '$4', '$5'), array($tracking_link['parcel_id'], $lang_code, date('d', strtotime($tracking_link['date_added'])), date('m', strtotime($tracking_link['date_added'])), date('Y', strtotime($tracking_link['date_added']))), $tracking_link['carrier_tracking_link']);        
        $i++;
      }
    }

    return $parcel_link;
  }
