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
        require_once('../../Klarna.php'); //relative from test/

        $config = new KlarnaConfig(KCONFIG);

        $klarna = new Klarna();
        $klarna->setConfig($config);

        include_once('../ko.inc.php');

        if(isset($_GET['invno'])) {
            try {
                $addr = $klarna->invoiceAddress($_GET['invno']);
                echo "Invoice #$_GET[invno] has invoice address:<br/>\n";
                $tmpArr = false;
                if($addr->isCompany) {
                    $tmpArr = array(
                            'Company name' => $addr->getCompanyName(),
                            'Street' => $addr->getStreet(),
                            'Zip code' => $addr->getZipCode(),
                            'City' => $addr->getCity(),
                            'Country code' => $addr->getCountryCode()
                    );
                }
                else {
                    $tmpArr = array(
                            'First name' => $addr->getFirstName(),
                            'Last name' => $addr->getLastName(),
                            'Street' => $addr->getStreet(),
                            'Zip code' => $addr->getZipCode(),
                            'City' => $addr->getCity(),
                            'Country code' => $addr->getCountryCode()
                    );
                }
                echo "<table border='1px'>";
                foreach($tmpArr as $key => $val) {
                    echo "<tr>\n
                                <td>$key</td><td>$val</td>
                              </tr>";
                }
                echo "</table>";
            }
            catch(Exception $e) {
                echo $e->getMessage() . " (" . $e->getCode() . ")";
            }
        }
        else {
            echo "First create a invoice <a href='../standard/add_transaction.php'>here</a><br />";
        }

        ?></body>
</html>