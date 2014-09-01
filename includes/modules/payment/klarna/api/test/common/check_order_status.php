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

        $id = '';
        $type = -1;
        if(isset($_GET['invno'])) {
            $id = $_GET['invno'];
            $type = 0;
        }
        else if(isset($_GET['ono'])) {
            $id = $_GET['ono'];
            $type = 1;
        }
        else {
            echo "<br/>You need to specify a rno/invno or orderid.<br/>";
            echo "<table><tr><form><td><label for='invno'>reservation/invoice:</label></td><td><input id='invno' name='invno'/></form></td></tr>";
            echo "<tr><form><td><label for='ono'>orderid:</label></td><td><input id='ono' name='ono'/></form></td></tr></table>";
            exit;
        }

        try {
            $result = $klarna->checkOrderStatus($id, $type);
            echo "Invoice/reservation #$id is ";
            switch($result) {
                case KlarnaFlags::PENDING:
                    echo "pending review.<br />\n";
                    break;
                case KlarnaFlags::DENIED:
                    echo "rejected!<br />\n";
                    break;
                case KlarnaFlags::ACCEPTED:
                    echo "accepted.<br/>\n";
                    break;
            }
        }
        catch(Exception $e) {
            echo $e->getMessage() . " (" . $e->getCode() . ")";
        }
        ?></body>
</html>