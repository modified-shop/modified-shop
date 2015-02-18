<?php
/** @noinspection PhpIncludeInspection */
require_once(DIR_FS_CATALOG . 'includes/external/billpay/base/billpayBase.php');

/**
 * Class BillpayOrder
 * Used to encapsulate "global $order" everywhere.
 */
class BillpayOrder
{

    function getTotal()
    {
        global $order;
        if ($order) {
            return $order->info['total'];
        }
        if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']->total)) {
            return $_SESSION['cart']->total;
        }
        return 0;
    }

    function getCustomerCompany()
    {
        global $order;
        if ($order) {
            return $order->customer['company'];
        }
        return '';
    }

    function getCustomerBilling()
    {
        global $order;
        if ($order) {
            return array(
                'firstName' =>  billpayBase::EnsureUTF8($order->billing['firstname']),
                'lastName'  =>  billpayBase::EnsureUTF8($order->billing['lastname']),
                'address'   =>  billpayBase::EnsureUTF8($order->billing['street_address'] . (isset($order->billing['suburb']) ? ' '.$order->billing['suburb'] : '')),
                'postCode'  =>  billpayBase::EnsureUTF8($order->billing['postcode']),
                'city'      =>  billpayBase::EnsureUTF8($order->billing['city']),
                'country2'  =>  billpayBase::EnsureUTF8($order->billing['country']['iso_code_2']),
                'country3'  =>  billpayBase::EnsureUTF8($order->billing['country']['iso_code_3']),
            );
        }
        $countries = BillpayDB::DBFetchRow("SELECT countries_iso_code_2, countries_iso_code_3 FROM ".TABLE_COUNTRIES." WHERE countries_id = "
            .(int)$_SESSION['customer_country_id']);
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

    function getCustomerDelivery()
    {
        global $order;
        if ($order) {
            return array(
                'firstName' =>  billpayBase::EnsureUTF8($order->delivery['firstname']),
                'lastName'  =>  billpayBase::EnsureUTF8($order->delivery['lastname']),
                'address'   =>  billpayBase::EnsureUTF8($order->delivery['street_address'] . (isset($order->delivery['suburb']) ? ' '.$order->delivery['suburb'] : '')),
                'postCode'  =>  billpayBase::EnsureUTF8($order->delivery['postcode']),
                'city'      =>  billpayBase::EnsureUTF8($order->delivery['city']),
                'country2'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_2']),
                'country3'  =>  billpayBase::EnsureUTF8($order->delivery['country']['iso_code_3']),
            );
        }
        return array();
    }

    function getProducts()
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

    function getCustomerPhone()
    {
        global $order;
        if ($order) {
            return $order->customer['telephone'];
        }
        return '';
    }

    function getCustomerEmail()
    {
        global $order;
        if ($order) {
            return $order->customer['email_address'];
        }
        return '';
    }

    function getCurrency()
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

}
