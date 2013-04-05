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

        $pno = (isset($_GET['pno'])) ? $_GET['pno'] : $pno; //re-set pno to GET if set otherwise use value from ko.inc.php
        if(isset($_GET['custno'])) {
            try {
                $result = $klarna->setCustomerNo($pno, $_GET['custno']);

                if($result) {
                    echo "Click <a href='remove_customer_no.php?custno=$_GET[custno]'>here</a> to remove the customer number.<br/>";
                    echo "OR<br/>";
                    echo "Click <a href='get_customer_no.php?pno=$pno'>here</a> to grab the customer number from the pno.<br/>";
                }
                else {
                    echo "set customer no failed?"; //Probably won't end up here? throws exception instead?
                }
            }
            catch(Exception $e) {
                echo $e->getMessage() . " (#" . $e->getCode() . ")";
            }
        }
        else {
            echo "\nNo custno set.<br />\n";
            $custno = time();
            echo "Try <a href='?custno=$custno'>$custno</a> as customer number.<br/>\n";
        }

        ?></body>
</html>