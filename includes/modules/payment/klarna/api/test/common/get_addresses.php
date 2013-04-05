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
        
        include_once('../ko.inc.php'); //test person and goods list
        
        $klarna->setCountry('se');

        try {
            if(!isset($_GET['addrno'])) {
                $addrs = $klarna->getAddresses(@$_GET['pno'], null, KlarnaFlags::GA_GIVEN);
                echo "Get address reply: <br /><pre>";
                print_r($addrs);
                echo "</pre>";

                //This example only works for GA_GIVEN.
                $implode = false;
                //Print out the result
                echo "<table border='1px'>\n";
                foreach($addrs as $index => $addr) {
                    echo "<tr><td>$index</td><td>\n";
                    echo "<table border='1px'>\n";
                    if($addr->isCompany) {
                        $implode = array(
                                'Company name' => $addr->getCompanyName(),
                                'Street' => $addr->getStreet(),
                                'Zip code' => $addr->getZipCode(),
                                'City' => $addr->getCity(),
                                'Country code' => $addr->getCountryCode()
                        );
                    }
                    else {
                        $implode = array(
                                'First name' => $addr->getFirstName(),
                                'Last name' => $addr->getLastName(),
                                'Street' => $addr->getStreet(),
                                'Zip code' => $addr->getZipCode(),
                                'City' => $addr->getCity(),
                                'Country code' => $addr->getCountryCode()
                        );
                    }
                    foreach($implode as $key => $val) {
                        echo "<tr onclick='window.open(\"?addrno=$index\", \"_self\");'>\n
                                <td>$key</td><td>$val</td>
                              </tr>";
                    }
                    echo "</table></td></tr>";

                    $_SESSION['addr_'.$key] = implode(';', $implode);
                }
            }
            else {
                //The chosen address by the customer?
                echo $_SESSION['addr_'.$_GET['addrno']];

                //Here you can explode(';', $_SESSION['addr_'.$_GET['addrno']]);
                $tmpAddr = explode(';', $_SESSION['addr_'.$_GET['addrno']]);

                $addr = new KlarnaAddr();

                //The following only works for KlarnaFlags::GA_GIVEN
                $isCompany = (count($tmpAddr) == 5) ? true : false;
                if($isCompany) {
                    //$fname and $lname is the reference person, now it's taken from the ko.inc.php file.
                    $addr->isCompany = true;
                    $addr->setCompanyName($tmpAddr[0]);
                    $addr->setStreet($tmpAddr[1]);
                    $addr->setZipCode($tmpAddr[2]);
                    $addr->setCity($tmpAddr[3]);
                    $addr->setCountry($tmpAddr[4]);

                }
                else {
                    $addr->setFirstName($tmpAddr[0]);
                    $addr->setLastName($tmpAddr[1]);
                    $addr->setStreet($tmpAddr[2]);
                    $addr->setZipCode($tmpAddr[3]);
                    $addr->setCity($tmpAddr[4]);
                    $addr->setCountry($tmpAddr[5]);
                }

                //Get_addresses only allowed for Sweden atm.
                $klarna->setCountry( (($addr->getCountry() == KlarnaCountry::SE) ? $addr->getCountryCode() : null) );
                
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
                echo "Selected address:<br />\n";
                echo "<table border='1px'>";
                foreach($tmpArr as $key => $val) {
                    echo "<tr>
                            <td>$key</td><td>$val</td>
                          </tr>";
                }
                echo "</table>";

                $klarna->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
            }
        }
        catch(Exception $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }

        ?></body>
</html>
