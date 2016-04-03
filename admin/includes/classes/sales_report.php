<?php
  /* --------------------------------------------------------------
   $Id: sales_report.php 4824 2013-05-24 09:41:50Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   stats_sales_report (c) Charly Wilhelm charly@yoshi.ch

   possible views (srView):
  1 yearly
  2 monthly
  3 weekly
  4 daily

  possible options (srDetail):
  0 no detail
  1 show details (products)
  2 show details only (products)

  export
  0 normal view
  1 html view without left and right
  2 csv

  sort
  0 no sorting
  1 product description asc
  2 product description desc
  3 #product asc, product descr asc
  4 #product desc, product descr desc
  5 revenue asc, product descr asc
  6 revenue desc, product descr des

   Released under the GNU General Public License
   --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' ); 

  class sales_report {

    var $mode, 
        $globalStartDate, 
        $startDate, 
        $endDate, 
        $actDate, 
        $showDate, 
        $showDateEnd, 
        $sortString, 
        $status, 
        $outlet;

    function __construct($mode, $startDate = 0, $endDate = 0, $sort = 0, $statusFilter = 0, $filter = 0,$payment = 0,$cgroup = '') {
      // startDate and endDate have to be a unix timestamp. Use mktime !
      // if set then both have to be valid startDate and endDate
      $this->mode = $mode;
      $this->tax_include = defined('DISPLAY_PRICE_WITH_TAX')?DISPLAY_PRICE_WITH_TAX:'';

      $this->statusFilter = $statusFilter;
      $this->paymentFilter = $payment;     
      $this->cgroupFilter = $cgroup;

      // get date of first sale
      /*
      $firstQuery = xtc_db_query("SELECT UNIX_TIMESTAMP(min(date_purchased)) as first FROM " . TABLE_ORDERS);
      $first = xtc_db_fetch_array($firstQuery);
      $this->globalStartDate = mktime(0, 0, 0, date("m", $first['first']), date("d", $first['first']), date("Y", $first['first']));
      */
      $this->globalStartDate = $startDate;
      
      $statusQuery = xtc_db_query("SELECT * FROM ".TABLE_ORDERS_STATUS." WHERE language_id='".(int)$_SESSION['languages_id']." ORDER BY sort_order'");
      $i = 0;
      while ($outResp = xtc_db_fetch_array($statusQuery)) {
        $status[$i] = $outResp;
        $i++;
      }
      $this->status = $status;


      if ($startDate == 0  or $startDate < $this->globalStartDate) {
        // set startDate to globalStartDate
        $this->startDate = $this->globalStartDate;
      } else {
        $this->startDate = $startDate;
      }
      if ($this->startDate > mktime(0, 0, 0, date("m"), date("d"), date("Y"))) {
        $this->startDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
      }
      /*
      if ($endDate > mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))) {
        // set endDate to tomorrow
        $this->endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
      } else {
        $this->endDate = $endDate;
      }
      */
      $this->endDate = $endDate;
      if ($this->endDate < $this->startDate + 24 * 60 * 60) {
        $this->endDate = $this->startDate + 24 * 60 * 60;
      }

      $this->actDate = $this->startDate;

      // query for order count
      $this->queryOrderCnt = "SELECT count(o.orders_id) as order_cnt FROM " . TABLE_ORDERS . " o";

      // queries for item details count
      $this->queryItemCnt = "SELECT o.orders_id, 
                                    op.products_id as pid, 
                                    op.orders_products_id, 
                                    op.products_name as pname,
                                    op.products_model as pmodel, 
                                    sum(op.products_quantity) as pquant, 
                                    sum(op.final_price/o.currency_value) as psum, 
                                    op.products_tax as ptax 
                               FROM " . TABLE_ORDERS . " o
                               JOIN " . TABLE_ORDERS_PRODUCTS . " op 
                                    ON o.orders_id = op.orders_id";

      // query for shipping
      $this->queryShipping = "SELECT sum(ot.value/o.currency_value) as shipping 
                                FROM " . TABLE_ORDERS . " o
                                JOIN " . TABLE_ORDERS_TOTAL . " ot 
                                     ON (ot.orders_id = o.orders_id AND  ot.class = 'ot_shipping')";

      switch ($sort) {
        case '0':
          $this->sortString = "";
          break;
        case '1':
          $this->sortString = " ORDER BY pname ASC ";
          break;
        case '2':
          $this->sortString = " ORDER BY pname DESC";
          break;
        case '3':
          $this->sortString = " ORDER BY pquant DESC, pname ASC";
          break;
        case '4':
          $this->sortString = " ORDER BY pquant DESC, pname ASC";
          break;
        case '5':
          $this->sortString = " ORDER BY psum ASC, pname ASC";
          break;
        case '6':
          $this->sortString = " ORDER BY psum DESC, pname ASC";
          break;
      }
    }

    function getNext($details=false) {
      switch ($this->mode) {
        // yearly
        case '1':
          $sd = $this->actDate;
          $ed = mktime(0, 0, 0, date("m", $sd), date("d", $sd), date("Y", $sd) + 1);
          break;
        // monthly
        case '2':
          $sd = $this->actDate;
          $ed = mktime(0, 0, 0, date("m", $sd) + 1, 1, date("Y", $sd));
          break;
        // weekly
        case '3':
          $sd = $this->actDate;
          $ed = mktime(0, 0, 0, date("m", $sd), date("d", $sd) + 7, date("Y", $sd));
          break;
        // daily
        case '4':
          $sd = $this->actDate;
          $ed = mktime(0, 0, 0, date("m", $sd), date("d", $sd) + 1, date("Y", $sd));
          break;
      }
      if ($ed > $this->endDate) {
        $ed = $this->endDate;
      }

      $filterString = "";
      if (strpos($this->statusFilter, ',') !== false) {
        $status_array = explode(',', $this->statusFilter);
        $filterString .= " AND o.orders_status IN ('". implode("', '", $status_array) . "') ";
      } elseif ($this->statusFilter > 0) {
        $filterString .= " AND o.orders_status = " . $this->statusFilter . " ";
      }
      
      if (!is_numeric($this->paymentFilter)) {
      	$filterString .= " AND o.payment_method ='" . xtc_db_prepare_input($this->paymentFilter) . "' ";
      }
       
      if ($this->cgroupFilter != '') {
         $filterString .= " AND o.customers_status ='" . (int)$this->cgroupFilter . "' ";
      }
       
      $rqOrders = xtc_db_query($this->queryOrderCnt . " WHERE o.date_purchased >= '" . xtc_db_input(date("Y-m-d H:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d H:i:s", $ed)) . "'" . $filterString);
      $order = xtc_db_fetch_array($rqOrders);

      $rqShipping = xtc_db_query($this->queryShipping . " WHERE o.date_purchased >= '" . xtc_db_input(date("Y-m-d H:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d H:i:s", $ed)) . "'" . $filterString);
      $shipping = xtc_db_fetch_array($rqShipping);

      $rqItems = xtc_db_query($this->queryItemCnt . " WHERE o.date_purchased >= '" . xtc_db_input(date("Y-m-d H:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d H:i:s", $ed)) . "'" . $filterString . " GROUP BY pid " . $this->sortString);
      $rqItems_count = xtc_db_num_rows($rqItems);

      // set the return values
      $this->actDate = $ed;
      $this->showDate = $sd;
      $this->showDateEnd = $ed - 60 * 60 * 24;

      // execute the query
      $itemTot = 0;
      $sumTot = 0;
      $result = array();
      while ($resp = xtc_db_fetch_array($rqItems)) {
        
        // to avoid rounding differences round for every quantum
        // multiply with the number of items afterwords.
        $price = 0;
        if ($resp['psum']>0) {
          $price = $resp['psum'] / $resp['pquant'];
        }
        
        // products_attributes
        // are there any attributes for this order_id ?
        if ($details) {
          $attr = array();
          $attributes_query = xtc_db_query("SELECT opa.*
                                              FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
                                              JOIN " . TABLE_ORDERS_PRODUCTS . " op
                                                   ON (op.orders_id=opa.orders_id 
                                                       AND op.products_id='".$resp['pid']."'
                                                       AND op.orders_products_id=opa.orders_products_id)
                                          GROUP BY opa.orders_products_options_values_id
                                          ORDER BY opa.orders_products_attributes_id");
          $attrib_cnt = xtc_db_num_rows($attributes_query);
          if ($attrib_cnt > 0) {
            while ($attr[] = xtc_db_fetch_array($attributes_query));
          }
            
          // values per date
          if ($attrib_cnt > 0) {
            $price2 = 0;
            $price3 = 0;
            $option = array();
            $k = -1;
            $ord_pro_id_old = 0;
            for ($j = 0; $j < $attrib_cnt; $j++) {
              if ($attr[$j]['price_prefix'] == "-") {
                $price2 += (-1) *  $attr[$j]['options_values_price'];
                $price3 = (-1) * $attr[$j]['options_values_price'];
                $prefix = "-";
              } else {
                $price2 += $attr[$j]['options_values_price'];
                $price3 = $attr[$j]['options_values_price'];
                $prefix = "+";
              }
              $ord_pro_id = $attr[$j]['orders_products_id'];
              if ( $ord_pro_id != $ord_pro_id_old) {
                $k++;
                $l = 0;
                // set values
                $option[$k]['price_prefix'] = $attr[$j]['price_prefix'];
                $option[$k]['options'][0] = $attr[$j]['products_options'];
                $option[$k]['options_values'][0] = $attr[$j]['products_options_values'];
                if ($price3 != 0) {
                  $option[$k]['price'][0] = $price3;
                } else {
                  $option[$k]['price'][0] = 0;
                }
              } else {
                $l++;
                // update values
                $option[$k]['options'][$l] = $attr[$j]['products_options'];
                $option[$k]['options_values'][$l] = $attr[$j]['products_options_values'];
                if ($price3 != 0) {
                  $option[$k]['price'][$l] = $price3;
                } else {
                  $option[$k]['price'][$l] = 0;
                }
              }
              $ord_pro_id_old = $ord_pro_id;
            }
            // set attr value
            $resp['attr'] = $option;
          } else {
            $resp['attr'] = "";
          }
        }
        
        $resp['price'] = $price;
        $resp['psum'] = $resp['pquant'] * $price;
        $resp['order'] = $order['order_cnt'];
        $resp['shipping'] = $shipping['shipping'];

        // values per date and item
        $sumTot += $resp['psum'];
        $itemTot += $resp['pquant'];
        // add totsum and totitem until current row
        $resp['totsum'] = $sumTot;
        $resp['totitem'] = $itemTot;
        
        // write results to return array      
        $result[] = $resp;
      
      }

      return $result;
    }
}
?>