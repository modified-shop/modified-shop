<?php
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

/**
 * Class BillpayOrder
 * Used to encapsulate "global $order" everywhere.
 */
class BillpayOrder
{

    public static function getTotal()
    {
        global $order;
        if ($order) {
            return $order->info['total'];
        }
        if (isset($_SESSION['cart']) && is_object($_SESSION['cart'])) {
            return $_SESSION['cart']->show_total();
        }
        return 0;
    }

    public static function getCustomerCompany()
    {
        global $order;
        if ($order) {
            return $order->customer['company'];
        }
        return '';
    }

    public static function getCustomerBilling()
    {
        global $order;
        if ($order) {
            return array(
                'firstName' =>  billpayBase::EnsureUTF8($order->billing['firstname']),
                'lastName'  =>  billpayBase::EnsureUTF8($order->billing['lastname']),
                'address'   =>  billpayBase::EnsureUTF8($order->billing['street_address']
                    . (isset($order->billing['suburb']) ? ' '.$order->billing['suburb'] : '')),
                'postCode'  =>  billpayBase::EnsureUTF8($order->billing['postcode']),
                'city'      =>  billpayBase::EnsureUTF8($order->billing['city']),
                'country2'  =>  billpayBase::EnsureUTF8($order->billing['country']['iso_code_2']),
                'country3'  =>  billpayBase::EnsureUTF8($order->billing['country']['iso_code_3']),
            );
        }
        $table = TABLE_COUNTRIES;
        $country = (int)$_SESSION['customer_country_id'];
        $countries = BillpayDB::DBFetchRow("SELECT countries_iso_code_2, countries_iso_code_3 FROM $table WHERE countries_id = $country");
        $ret = array(
            'firstName' =>  '',
            'lastName'  =>  '',
            'address'   =>  '',
            'postCode'  =>  '',
            'city'      =>  '',
            'country2'  =>  $countries['countries_iso_code_2'],
            'country3'  =>  $countries['countries_iso_code_3'],
        );
        return $ret;
    }

    public static function getCustomerShipping()
    {
        global $order;
        if(!isset($order->delivery) || empty($order->delivery)) {
            return BillpayOrder::getCustomerBilling();
        }

        if ($order) {
            return array(
                'firstName' =>  billpayBase::EnsureUTF8($order->delivery['firstname']),
                'lastName'  =>  billpayBase::EnsureUTF8($order->delivery['lastname']),
                'address'   =>  billpayBase::EnsureUTF8($order->delivery['street_address']
                    . (isset($order->billing['suburb']) ? ' '.$order->billing['suburb'] : '')),
                'postCode'  =>  billpayBase::EnsureUTF8($order->delivery['postcode']),
                'city'      =>  billpayBase::EnsureUTF8($order->delivery['city']),
                'country2'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_2']),
                'country3'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_3']),
            );
        }
        $table = TABLE_COUNTRIES;
        $country = (int)$_SESSION['customer_country_id'];
        $countries = BillpayDB::DBFetchRow("SELECT countries_iso_code_2, countries_iso_code_3 FROM $table WHERE countries_id = $country");
        $ret = array(
            'firstName' =>  '',
            'lastName'  =>  '',
            'address'   =>  '',
            'postCode'  =>  '',
            'city'      =>  '',
            'country2'  =>  $countries['countries_iso_code_2'],
            'country3'  =>  $countries['countries_iso_code_3'],
        );
        return $ret;
    }

    public static function getCustomerDelivery()
    {
        global $order;
        if ($order) {
            return array(
                'firstName' =>  billpayBase::EnsureUTF8($order->delivery['firstname']),
                'lastName'  =>  billpayBase::EnsureUTF8($order->delivery['lastname']),
                'address'   =>  billpayBase::EnsureUTF8($order->delivery['street_address']
                    . (isset($order->delivery['suburb']) ? ' '.$order->delivery['suburb'] : '')),
                'postCode'  =>  billpayBase::EnsureUTF8($order->delivery['postcode']),
                'city'      =>  billpayBase::EnsureUTF8($order->delivery['city']),
                'country2'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_2']),
                'country3'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_3']),
            );
        }
        return array();
    }

    public static function getProducts()
    {
        global $order;
        $ret = array();
        if ($order) {
            foreach ($order->products as $product) {
                $ret[] = array(
                    'id'            =>  $product['id'],
                    'qty'           =>  $product['qty'],
                    'name'          =>  billpayBase::EnsureUTF8($product['name']),
                    'price'         =>  $product['price'],
                    'tax'           =>  $product['tax'],
                );
            }
        }
        return $ret;
    }

    public static function getCustomerPhone()
    {
        global $order;
        if ($order) {
            return $order->customer['telephone'];
        }
        return '';
    }

    public static function getCustomerEmail()
    {
        global $order;
        if ($order) {
            return $order->customer['email_address'];
        }
        return '';
    }

    public static function GetCurrentCurrency()
    {
        global $order;

        // prefer order over session
        if (!empty($order->info['currency'])) {
            return (string)$order->info['currency'];
        }
        else if (!empty($_SESSION['currency'])) {
            return (string)$_SESSION['currency'];
        }
        return 'EUR';
    }

    public static function getCurrencyById($orderId)
    {
        $table = TABLE_ORDERS;
        $orders_id = (int)$orderId;
        return BillpayDB::DBFetchValue("SELECT currency FROM $table WHERE orders_id = $orders_id");
    }

    public static function getOTById($orderId, $ot)
    {
        $table = TABLE_ORDERS_TOTAL;
        $orders_id = (int)$orderId;
        $class = $ot;
        return BillpayDB::DBFetchValue("SELECT value FROM $table WHERE orders_id = '$orders_id' AND class='$class'");
    }

}
