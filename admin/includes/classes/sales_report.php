<?php
/* --------------------------------------------------------------
   $Id: sales_report.php 1311 2005-10-18 12:30:40Z mz $

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   stats_sales_report (c) Charly Wilhelm  charly@yoshi.ch

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
    var $mode, $globalStartDate, $startDate, $endDate, $actDate, $showDate, $showDateEnd, $sortString, $status, $outlet;

    function sales_report($mode, $startDate = 0, $endDate = 0, $sort = 0, $statusFilter = 0, $filter = 0,$payment = 0) {
      // startDate and endDate have to be a unix timestamp. Use mktime !
      // if set then both have to be valid startDate and endDate
      $this->mode = $mode;
      $this->tax_include = DISPLAY_PRICE_WITH_TAX;

      $this->statusFilter = $statusFilter;
      $this->paymentFilter = $payment;     
      // get date of first sale
      $firstQuery = xtc_db_query("select UNIX_TIMESTAMP(min(date_purchased)) as first FROM " . TABLE_ORDERS);
      $first = xtc_db_fetch_array($firstQuery);
      $this->globalStartDate = mktime(0, 0, 0, date("m", $first['first']), date("d", $first['first']), date("Y", $first['first']));
            
      $statusQuery = xtc_db_query("select * from ".TABLE_ORDERS_STATUS." where language_id='".$_SESSION['languages_id']."'");
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

      if ($endDate > mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"))) {
        // set endDate to tomorrow
        $this->endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
      } else {
        $this->endDate = $endDate;
      }
      if ($this->endDate < $this->startDate + 24 * 60 * 60) {
        $this->endDate = $this->startDate + 24 * 60 * 60;
      }

      $this->actDate = $this->startDate;

      // query for order count
      $this->queryOrderCnt = "SELECT count(o.orders_id) as order_cnt FROM " . TABLE_ORDERS . " o";

      // queries for item details count
      $this->queryItemCnt = "SELECT o.orders_id, op.products_id as pid, op.orders_products_id, op.products_name as pname,op.products_model as pmodel, sum(op.products_quantity) as pquant, sum(op.final_price/o.currency_value) as psum, op.products_tax as ptax FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op WHERE o.orders_id = op.orders_id";

      // query for attributes
      $this->queryAttr = "SELECT count(op.products_id) as attr_cnt, o.orders_id, opa.orders_products_id, opa.products_options, opa.products_options_values, opa.options_values_price, opa.price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa, " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op WHERE o.orders_id = opa.orders_id AND op.orders_products_id = opa.orders_products_id";

      // query for shipping
      $this->queryShipping = "SELECT sum(ot.value/o.currency_value) as shipping FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot WHERE ot.orders_id = o.orders_id AND  ot.class = 'ot_shipping'";

      switch ($sort) {
        case '0':
          $this->sortString = "";
          break;
        case '1':
          $this->sortString = " order by pname asc ";
          break;
        case '2':
          $this->sortString = " order by pname desc";
          break;
        case '3':
          $this->sortString = " order by pquant asc, pname asc";
          break;
        case '4':
          $this->sortString = " order by pquant desc, pname asc";
          break;
        case '5':
          $this->sortString = " order by psum asc, pname asc";
          break;
        case '6':
          $this->sortString = " order by psum desc, pname asc";
          break;
      }

    }

    function getNext() {
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
      if ($this->statusFilter > 0) {
        $filterString .= " AND o.orders_status = " . $this->statusFilter . " ";
      }
      
      if (!is_numeric($this->paymentFilter)) {
      	$filterString .= " AND o.payment_method ='" . xtc_db_prepare_input($this->paymentFilter) . "' ";
      }
      
      $rqOrders = xtc_db_query($this->queryOrderCnt . " WHERE o.date_purchased >= '" . xtc_db_input(date("Y-m-d\TH:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d\TH:i:s", $ed)) . "'" . $filterString);
      $order = xtc_db_fetch_array($rqOrders);

      $rqShipping = xtc_db_query($this->queryShipping . " AND o.date_purchased >= '" . xtc_db_input(date("Y-m-d\TH:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d\TH:i:s", $ed)) . "'" . $filterString);
      $shipping = xtc_db_fetch_array($rqShipping);

      $rqItems = xtc_db_query($this->queryItemCnt . " AND o.date_purchased >= '" . xtc_db_input(date("Y-m-d\TH:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d\TH:i:s", $ed)) . "'" . $filterString . " group by pid " . $this->sortString);

      // set the return values
      $this->actDate = $ed;
      $this->showDate = $sd;
      $this->showDateEnd = $ed - 60 * 60 * 24;

      // execute the query
      $cnt = 0;
      $itemTot = 0;
      $sumTot = 0;
      while ($resp[$cnt] = xtc_db_fetch_array($rqItems)) {
        // to avoid rounding differences round for every quantum
        // multiply with the number of items afterwords.
        $price = $resp[$cnt]['psum'] / $resp[$cnt]['pquant'];

        // products_attributes
        // are there any attributes for this order_id ?
        $rqAttr = xtc_db_query($this->queryAttr . " AND o.date_purchased >= '" . xtc_db_input(date("Y-m-d\TH:i:s", $sd)) . "' AND o.date_purchased < '" . xtc_db_input(date("Y-m-d\TH:i:s", $ed)) . "' AND op.products_id = " . $resp[$cnt]['pid'] . $filterString . " group by products_options_values order by orders_products_id");
        $i = 0;
        while ($attr[$i] = xtc_db_fetch_array($rqAttr)) {
          $i++;
        }

        // values per date
        if ($i > 0) {
          $price2 = 0;
          $price3 = 0;
          $option = array();
          $k = -1;
          $ord_pro_id_old = 0;
          for ($j = 0; $j < $i; $j++) {
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
              $option[$k]['quant'] = $attr[$j]['attr_cnt'];
              $option[$k]['options'][0] = $attr[$j]['products_options'];
              $option[$k]['options_values'][0] = $attr[$j]['products_options_values'];
              if ($price3 != 0) {
                //$option[$k]['price'][0] = xtc_add_tax($price3, $resp[$cnt]['ptax']);
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
                //$option[$k]['price'][$l] = xtc_add_tax($price3, $resp[$cnt]['ptax']);
                $option[$k]['price'][$l] = $price3;
              } else {
                $option[$k]['price'][$l] = 0;
              }
            }
            $ord_pro_id_old = $ord_pro_id;
          }
          // set attr value
          $resp[$cnt]['attr'] = $option;
        } else {
          $resp[$cnt]['attr'] = "";
        }
        //$resp[$cnt]['price'] = xtc_add_tax($price, $resp[$cnt]['ptax']);
        //$resp[$cnt]['psum'] = $resp[$cnt]['pquant'] * xtc_add_tax($price, $resp[$cnt]['ptax']);
        $resp[$cnt]['price'] = $price;
        $resp[$cnt]['psum'] = $resp[$cnt]['pquant'] * $price;
        $resp[$cnt]['order'] = $order['order_cnt'];
        $resp[$cnt]['shipping'] = $shipping['shipping'];

        // values per date and item
        $sumTot += $resp[$cnt]['psum'];
        $itemTot += $resp[$cnt]['pquant'];
        // add totsum and totitem until current row
        $resp[$cnt]['totsum'] = $sumTot;
        $resp[$cnt]['totitem'] = $itemTot;
        $cnt++;
      }

      return $resp;
    }
}
?>