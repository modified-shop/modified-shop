<?php
/* -----------------------------------------------------------------------------------------
   $Id: invoice_numer_action.php

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   hendrik - 2011-05-14 - independent invoice number and date 
   
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
   
include('includes/modules/invoice_number/'.$_SESSION['language'].'/invoice_number.php');
   
function add_select_ibillnr($sSelect)
{
    if (defined('MODULE_INVOICE_NUMBER_STATUS') && MODULE_INVOICE_NUMBER_STATUS == 'True') {
      $sSelect .= ',o.ibn_billnr';
    }
    return $sSelect;
}

function add_table_infos_ibillnr($order)
{
    $html = '';
    if (defined('MODULE_INVOICE_NUMBER_STATUS') && MODULE_INVOICE_NUMBER_STATUS == 'True') {
        $html .= '<tr>'.PHP_EOL;
        $html .= '  <td class="main"><b>'.ENTRY_INVOICE_NUMBER.'</b></td>'.PHP_EOL;
        $html .= '  <td class="main">'.($order->info['ibn_billnr'] == '' ? '<span class="not_assigned">'.NOT_ASSIGNED.'<span>' : $order->info['ibn_billnr']).'</td>'.PHP_EOL;
        $html .= '</tr>'.PHP_EOL;
        $html .= '<tr>'.PHP_EOL;
        $html .= '  <td class="main"><b>'.ENTRY_INVOICE_DATE.'</b></td>'.PHP_EOL;
        $html .= '  <td class="main">'. ($order->info['ibn_billdate'] == '0000-00-00'? '<span class="not_assigned">'.NOT_ASSIGNED.'<span>' : xtc_date_short($order->info['ibn_billdate'])).'</td>'.PHP_EOL;
        $html .= '</tr>'.PHP_EOL;
    }
    return $html;
}

function add_btn_ibillnr($order,$oID)
{
    $html = '';
    if (defined('MODULE_INVOICE_NUMBER_STATUS') && MODULE_INVOICE_NUMBER_STATUS == 'True') {
      if ($order->info['ibn_billnr'] == '') {
        $html = '              <a class="button ibillnr-btn" href="'.xtc_href_link(FILENAME_ORDERS, 'page='.$_GET['page'].'&oID='.$oID.'&action=edit&action2=set_ibillnr').'">'. BUTTON_BILL .'</a>'.PHP_EOL;
      }
    }
    return $html;
}
   
function action_next_ibillnr($order,$oID)
{
    if( (isset($_GET['action2']) && $_GET['action2']=='set_ibillnr') && ($order->info['ibn_billnr'] == '') ) {
      $ibillnr = get_next_ibillnr();
      set_order_ibillnr($oID, $ibillnr);
      set_next_ibillnr();
      xtc_redirect(xtc_href_link(FILENAME_ORDERS, 'page=1&oID='.(int)$oID.'&action=edit'));
    }
}

function set_next_ibillnr()
{
        /*
    $query = "select 
                configuration_value 
              from " . 
                TABLE_CONFIGURATION . "
              where 
                configuration_key = 'MODULE_INVOICE_NUMBER_IBN_BILLNR'";
    $result = xtc_db_query($query);
    $data=xtc_db_fetch_array($result);
    */
    
    $data = (int)MODULE_INVOICE_NUMBER_IBN_BILLNR;
    if ($data == 0) {
      return 0;
    }
    $data++;
    
    $sql_data_array = array(
        'configuration_value' => $data 
      );
    
    xtc_db_perform(TABLE_CONFIGURATION,$sql_data_array,'update',"configuration_key = 'MODULE_INVOICE_NUMBER_IBN_BILLNR'");
}

function get_next_ibillnr()
{
    /*
    $query = "select 
                configuration_value 
              from " . 
                TABLE_CONFIGURATION . "
              where 
                configuration_key = 'MODULE_INVOICE_NUMBER_IBN_BILLNR'";
    $result = xtc_db_query($query);
    $data=xtc_db_fetch_array($result);
    */
    $n = (int)MODULE_INVOICE_NUMBER_IBN_BILLNR;
    $year = date('Y');
    $month = date('m');
    $day = date('d');

    $d = MODULE_INVOICE_NUMBER_IBN_BILLNR_FORMAT;
    $d = str_replace('{n}', $n, $d);
    $d = str_replace('{d}', $day, $d);
    $d = str_replace('{m}', $month, $d);
    $d = str_replace('{y}', $year, $d);
    
    return $d;
}

function set_order_ibillnr($orders_id, $ibn_billnr)
{
    $sql_data_array = array(
        'ibn_billnr' => xtc_db_prepare_input($ibn_billnr), 
        'ibn_billdate' => 'now()'
      );
    xtc_db_perform(TABLE_ORDERS,$sql_data_array,'update',"orders_id = '" . (int)$orders_id . "'"); 
}