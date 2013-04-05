<?php session_start(); ?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        <title><?php echo basename(dirname(__FILE__)) . ' > ' . basename(__FILE__, '.php'); ?></title>
    </head>
    <body><?php
        $link = dirname(dirname($_SERVER['SCRIPT_NAME']));
        echo "<a href='".$link."'>".basename($link)."</a> &gt; ";
        $link = dirname($_SERVER['SCRIPT_NAME']);
        echo "<a href='".$link."'>".basename($link)."</a> &gt; ".basename($_SERVER['SCRIPT_NAME'])."<br/>\n";

        include_once('../config.inc.php'); //KCONFIG is defined in here.
        require_once('../../Klarna.php');

        if(!isset($_GET['sum'])) {
            $_GET['sum'] = 1001;
        }
        if(!isset($_GET['flags'])) {
            $_GET['flags'] = KlarnaFlags::PRODUCT_PAGE;
        }
        echo "<h3>Cost functions:</h3>";
        echo "<ul>\n
                <li><a href='apr_annuity.php?id=$_GET[id]&sum=$_GET[sum]&flags=$_GET[flags]&c=$_GET[c]'>APR annuity</a></li>
                <li><a href='total_credit_purchase_cost.php?id=$_GET[id]&sum=$_GET[sum]&flags=$_GET[flags]&c=$_GET[c]'>Total credit purchase cost</a></li>
                <li><a href='monthly_cost?id=$_GET[id]&sum=$_GET[sum]&flags=$_GET[flags]&c=$_GET[c]'>Monthly cost</a></li>\n
                </ul>";
        echo "<br/>\nUsing sum $_GET[sum], adding or altering sum in the URL will change this value.<br/>";
        echo "Flags set to ".KlarnaFlags::CHECKOUT_PAGE." for checkout and ".KlarnaFlags::PRODUCT_PAGE." for product page.<br/>\n";
        ?></body>
</html>
