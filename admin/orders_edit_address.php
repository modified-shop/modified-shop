<?php
/* --------------------------------------------------------------
   $Id: orders_edit_address.php 2748 2012-04-10 15:31:07Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(orders.php,v 1.27 2003/02/16); www.oscommerce.com
   (c) 2003	 nextcommerce (orders.php,v 1.7 2003/08/14); www.nextcommerce.org
   (c) 2003 XT-Commerce

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

  //BOC web28 - 2013-02-02 - add dropdown countries boxes
  function get_country_id($country_name) {
    $countries_query = xtc_db_query("SELECT countries_id
                                       FROM ".TABLE_COUNTRIES."
                                      WHERE countries_name = '".xtc_db_input($country_name)."'");
    $countries = xtc_db_fetch_array($countries_query);
    return $countries['countries_id'];
  }

  $customer_countries_id = get_country_id($order->customer['country']);
  $delivery_countries_id = get_country_id($order->delivery['country']);
  $billing_countries_id = get_country_id($order->billing['country']);
  //EOC web28 - 2013-02-02 - add dropdown countries boxes

  echo xtc_draw_form('adress_edit', FILENAME_ORDERS_EDIT, 'action=address_edit', 'post');
  echo xtc_draw_hidden_field('oID', $_GET['oID']);
  echo xtc_draw_hidden_field('cID', $order->customer['ID']);
?>
<!-- Begin Infotext //-->
<table border="0" cellspacing="0" cellpadding="2" style="border: 1px red solid; padding:5px; background: #FFD6D6; margin: 5px 0 5px 0">
  <tr>
    <td class="main">
      <?php echo TEXT_ORDERS_ADDRESS_EDIT_INFO;?>
    </td>
  </tr>
</table>
<!-- End Infotext //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
<tr class="dataTableHeadingRow">
<td class="dataTableHeadingContent" width="10%" align="left">&nbsp;</td>
<td class="dataTableHeadingContent" width="30%" align="left"><?php echo TEXT_INVOICE_ADDRESS;?></td>
<td class="dataTableHeadingContent" width="30%" align="left"><?php echo TEXT_SHIPPING_ADDRESS;?></td>
<td class="dataTableHeadingContent" width="30%" align="left"><?php echo TEXT_BILLING_ADDRESS;?></td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_COMPANY;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_company', $order->customer['company']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_company', $order->delivery['company']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_company', $order->billing['company']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_FIRSTNAME;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_firstname', $order->customer['firstname']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_firstname', $order->delivery['firstname']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_firstname', $order->billing['firstname']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_LASTNAME;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_lastname', $order->customer['lastname']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_lastname', $order->delivery['lastname']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_lastname', $order->billing['lastname']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_STREET;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_street_address', $order->customer['street_address']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_street_address', $order->delivery['street_address']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_street_address', $order->billing['street_address']);?>
</td>
</tr>

<?php
if (ACCOUNT_SUBURB == 'true') {
?>
<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo ENTRY_SUBURB;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_suburb', $order->customer['suburb']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_suburb', $order->delivery['suburb']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_suburb', $order->billing['suburb']);?>
</td>
</tr>
<?php
}
?>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_ZIP;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_postcode', $order->customer['postcode']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_postcode', $order->delivery['postcode']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_postcode', $order->billing['postcode']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CITY;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('customers_city', $order->customer['city']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('delivery_city', $order->delivery['city']);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_input_field('billing_city', $order->billing['city']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_COUNTRY;?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_pull_down_menu('customers_country_id', xtc_get_countries('',1), $customer_countries_id);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_pull_down_menu('delivery_country_id', xtc_get_countries('',1), $delivery_countries_id);?>
</td>
<td class="dataTableContent" align="left">
<?php echo xtc_draw_pull_down_menu('billing_country_id', xtc_get_countries('',1), $billing_countries_id);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left" colspan="4">
&nbsp;
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CUSTOMER_GROUP;?>
</td>
<td class="dataTableContent" align="left" colspan="3">
<?php echo xtc_draw_pull_down_menu('customers_status', xtc_get_customers_statuses(), $order->info['status']). TEXT_CUSTOMER_GROUP_INFO;?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CUSTOMER_CID;?>
</td>
<td class="dataTableContent" align="left" colspan="3">
<?php echo xtc_draw_input_field('customers_cid', $order->customer['csID']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CUSTOMER_EMAIL;?>
</td>
<td class="dataTableContent" align="left" colspan="3">
<?php echo xtc_draw_input_field('customers_email_address', $order->customer['email_address']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CUSTOMER_TELEPHONE;?>
</td>
<td class="dataTableContent" align="left" colspan="3">
<?php echo xtc_draw_input_field('customers_telephone', $order->customer['telephone']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left">
<?php echo TEXT_CUSTOMER_UST;?>
</td>
<td class="dataTableContent" align="left" colspan="3">
<?php echo xtc_draw_input_field('customers_vat_id', $order->customer['vat_id']);?>
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="left" colspan="4">
&nbsp;
</td>
</tr>

<tr class="dataTableRow">
<td class="dataTableContent" align="right" colspan="4">
<?php echo '<input type="submit" class="button" onclick="this.blur();" value="' . TEXT_SAVE_CUSTOMERS_DATA . '"/>'; ?>
</td>
</tr>

<tr>
<td class="dataTableHeadingContent" width="10%" align="left">&nbsp;</td>
<td class="dataTableHeadingContent" width="30%" align="left">&nbsp;</td>
<td class="dataTableHeadingContent" width="30%" align="left">&nbsp;</td>
<td class="dataTableHeadingContent" width="30%" align="left">&nbsp;</td>
</tr>
</table>
</form>
<br />
<br />
<?php
}
?>
<!-- Adressbearbeitung Ende //-->