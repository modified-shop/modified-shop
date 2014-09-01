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

        $klarna->setIncomeInfo('yearly_salary', is_numeric($ysalary) ? intval(round($ysalary*100, 0)) : "");

        if(isset($_GET['orderno']) && is_numeric($_GET['orderno']) && isset($_SESSION['dev_id_1'])) {
            try {
                $klarna->setEstoreInfo($orderid1 = $_GET['orderno']);
                //-1 calculates amount from goodsList
                $result = $klarna->reserveAmount($pno, $gender, -1);
                
                echo "\nTransaction complete.<br />\n";
                echo "Reservation #$result[0] is ";
                switch($result[1]) {
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
                echo "<br />\nClick <a href='activate_reservation.php?rno=$result[0]'>here</a> to activate your reservation.<br />";
                echo "OR <br />";
                echo "Click <a href='cancel_reservation.php?rno=$result[0]'>here</a> to cancel your reservation.<br />";
                echo "OR <br />";
                echo "Click <a href='change_reservation.php?rno=$result[0]&amount=100'>here</a> to change your reservation amount to 100.<br />";
                echo "OR <br />";
                echo "Click <a href='split_reservation.php?rno=$result[0]&amount=50'>here</a> to split your reservation by 50.<br />";
            }
            catch(Exception $e) {
                echo $e->getMessage() . " (#" . $e->getCode() . ")";
            }
        }
        else {
            echo "\nNo orderno set.<br />\n";
            $orderno = time();
            echo "Try <a href='?orderno=$orderno'>$orderno</a> as order number.<br/>\n";
            echo $klarna->checkoutHTML(); //dev_id_1 is set here.
        }

        ?></body>
</html>
