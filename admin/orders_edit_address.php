<?php
  /* --------------------------------------------------------------
   $Id: orders_edit_address.php 5338 2013-08-06 13:00:51Z Tomcraft $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com 
   (c) 2003	nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   XTC-Bestellbearbeitung:
   http://www.xtc-webservice.de / Matthias Hinsche
   info@xtc-webservice.de

   Released under the GNU General Public License
  --------------------------------------------------------------*/
  defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );
?>
<!-- Adressbearbeitung Anfang //-->
<?php
if ($_GET['edit_action']=='address') {
  // dropdown countries boxes
  function get_country_id($country_name, $country_iso_2) {
    $where = " WHERE countries_name = '".xtc_db_input($country_name)."'";
    if ($country_iso_2 != '') {
      $where = " WHERE countries_iso_code_2 = '".xtc_db_input($country_iso_2)."'";
    }
    $countries_query = xtc_db_query("SELECT countries_id
                                       FROM ".TABLE_COUNTRIES."
                                            ".$where);
    $countries = xtc_db_fetch_array($countries_query);
    return $countries['countries_id'];
  }
  $customer_countries_id = get_country_id($order->customer['country'], $order->customer['country_iso_2']);
  $delivery_countries_id = get_country_id($order->delivery['country'], $order->delivery['country_iso_2']);
  $billing_countries_id = get_country_id($order->billing['country'], $order->billing['country_iso_2']);
  

  echo xtc_draw_form('adress_edit', FILENAME_ORDERS_EDIT, 'action=address_edit', 'post');
  echo xtc_draw_hidden_field('oID', $_GET['oID']);
  echo xtc_draw_hidden_field('cID', $order->customer['ID']);
?>
<!-- Begin Infotext //-->
<div class="main important_info"><?php echo TEXT_ORDERS_ADDRESS_EDIT_INFO;?></div>
<!-- End Infotext //-->
<table class="tableBoxCenter collapse">
  <tr class="dataTableHeadingRow">
    <td class="dataTableHeadingContent" style="width:10%">&nbsp;</td>
    <td class="dataTableHeadingContent" style="width:30%"><?php echo TEXT_INVOICE_ADDRESS;?></td>
    <td class="dataTableHeadingContent" style="width:30%"><?php echo TEXT_SHIPPING_ADDRESS;?></td>
    <td class="dataTableHeadingContent" style="width:30%"><?php echo TEXT_BILLING_ADDRESS;?></td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_COMPANY;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_company', $order->customer['company'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_company', $order->delivery['company'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_company', $order->billing['company'], 'style="width: 200px"');?>
    </td>
  </tr>
  <?php if (ACCOUNT_GENDER == 'true') { ?>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_GENDER;?>
    </td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_gender_pull_down('customers_gender', $order->customer['gender'], 'style="width:200px;"');?>
    </span></td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_gender_pull_down('delivery_gender', $order->delivery['gender'], 'style="width: 200px"');?>
    </span></td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_gender_pull_down('billing_gender', $order->billing['gender'], 'style="width: 200px"');?>
    </span></td>
  </tr>
  <?php } ?>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_FIRSTNAME;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_firstname', $order->customer['firstname'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_firstname', $order->delivery['firstname'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_firstname', $order->billing['firstname'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_LASTNAME;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_lastname', $order->customer['lastname'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_lastname', $order->delivery['lastname'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_lastname', $order->billing['lastname'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_STREET;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_street_address', $order->customer['street_address'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_street_address', $order->delivery['street_address'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_street_address', $order->billing['street_address'], 'style="width: 200px"');?>
    </td>
  </tr>
  <?php if (ACCOUNT_SUBURB == 'true') { ?>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo ENTRY_SUBURB;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_suburb', $order->customer['suburb'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_suburb', $order->delivery['suburb'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_suburb', $order->billing['suburb'], 'style="width: 200px"');?>
    </td>
  </tr>
  <?php } ?>
    <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_ZIP;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_postcode', $order->customer['postcode'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_postcode', $order->delivery['postcode'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_postcode', $order->billing['postcode'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CITY;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_city', $order->customer['city'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_city', $order->delivery['city'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_city', $order->billing['city'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_COUNTRY;?>
    </td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_pull_down_menu('customers_country_id', xtc_get_countries('',1), $customer_countries_id, 'style="width: 200px"');?>
    </span></td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_pull_down_menu('delivery_country_id', xtc_get_countries('',1), $delivery_countries_id, 'style="width: 200px"');?>
    </span></td>
    <td class="dataTableContent"><span class="select_f12">
    <?php echo xtc_draw_pull_down_menu('billing_country_id', xtc_get_countries('',1), $billing_countries_id, 'style="width: 200px"');?>
    </span></td>
  </tr>
  <?php if (ACCOUNT_STATE == 'true') { ?>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo ENTRY_STATE;?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('customers_state', $order->customer['state'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('delivery_state', $order->delivery['state'], 'style="width: 200px"');?>
    </td>
    <td class="dataTableContent">
    <?php echo xtc_draw_input_field('billing_state', $order->billing['state'], 'style="width: 200px"');?>
    </td>
  </tr>
  <?php } ?>
  <tr class="dataTableRow">
    <td class="dataTableContent" colspan="4" style="padding: 0px !important; border-bottom: 0px !important;"><br /><br /><div class="main important_info"><?php echo TEXT_CUSTOMER_GROUP_INFO;?></div></td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CUSTOMER_GROUP;?>
    </td>
    <td class="dataTableContent" colspan="3"><span class="select_f12">
    <?php echo xtc_draw_pull_down_menu('customers_status', xtc_get_customers_statuses(), $order->info['status'], 'style="width: 200px"');?>
    </span></td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CUSTOMER_CID;?>
    </td>
    <td class="dataTableContent" colspan="3">
    <?php echo xtc_draw_input_field('customers_cid', $order->customer['csID'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CUSTOMER_EMAIL;?>
    </td>
    <td class="dataTableContent" colspan="3">
    <?php echo xtc_draw_input_field('customers_email_address', $order->customer['email_address'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CUSTOMER_TELEPHONE;?>
    </td>
    <td class="dataTableContent" colspan="3">
    <?php echo xtc_draw_input_field('customers_telephone', $order->customer['telephone'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent">
    <?php echo TEXT_CUSTOMER_UST;?>
    </td>
    <td class="dataTableContent" colspan="3">
    <?php echo xtc_draw_input_field('customers_vat_id', $order->customer['vat_id'], 'style="width: 200px"');?>
    </td>
  </tr>
  <tr class="dataTableRow">
    <td class="dataTableContent txta-r" colspan="4">
    <?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . TEXT_SAVE_CUSTOMERS_DATA . '"/>'; ?>
    </td>
  </tr>
  </table>
</form>
<br />
<br />
<?php
}
?>
<!-- Adressbearbeitung Ende //-->