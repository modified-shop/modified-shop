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
        try {
            $result = $klarna->getCustomerNo($pno);
            echo "$pno is associated with the following customer numbers: <br/>";
            echo "<ul>";
            foreach($result as $custno) {
                echo "<li><a href='remove_customer_no.php?custno=$custno'>$custno</a></li>\n";
            }
            echo "</ul><br/>\n";
            echo "Clicking a customer number will remove the association.";
        }
        catch(Exception $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }

        ?></body>
</html>