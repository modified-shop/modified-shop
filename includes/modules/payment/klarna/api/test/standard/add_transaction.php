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

        $klarna->setComment("the comment");
        //Reference can also be grabbed from first name, last name of the address.
        $klarna->setReference("$fname $lname", ""); 

        $klarna->setIncomeInfo('yearly_salary', is_numeric($ysalary) ? intval(round($ysalary*100, 0)) : "");

        if(isset($_GET['orderno']) && is_numeric($_GET['orderno']) && isset($_SESSION['dev_id_1'])) {
            try {
                $klarna->setEstoreInfo($orderid1 = $_GET['orderno']);
                $result = $klarna->addTransaction($pno, $gender);

                echo "\nTransaction complete.<br />\n";
                echo "Invoice #$result[0] is ";
                switch($result[1]) {
                    case KlarnaFlags::PENDING:
                        echo "pending review.<br />\n";
                        echo "Check for updated status <a href='../common/check_order_status.asp?invno=$result[0]'>here</a><br/><br/>\n";
                        break;
                    case KlarnaFlags::DENIED:
                        echo "rejected!<br />\n";
                        break;
                    case KlarnaFlags::ACCEPTED:
                        echo "accepted.<br/>\n";
                        break;
                }
                echo "Click <a href='activate_invoice.php?invno=$result[0]'>here</a> to activate your invoice.<br />";
                echo "OR <br />";
                echo "Click <a href='activate_part.php?invno=$result[0]'>here</a> to partially activate your invoice.<br />";
                echo "OR <br />";
                echo "Click <a href='delete_invoice.php?invno=$result[0]'>here</a> to remove your invoice.<br />";
                echo "<br />\n<h3>Passive invoice functions</h3>";
                echo "<ul>
                        <li><a href='../other/update_orderno.php?invno=$result[0]&orderno=666' target='_blank'>Update order number to 666</a></li>
                        <li><a href='../other/update_goods_qty.php?invno=$result[0]' target='_blank'>Update goods quantity to 5</a></li>
                        <li><a href='../other/update_charge_amount.php?invno=$result[0]' target='_blank'>Update charge amount (handling fee) to 39</a></li>
                      </ul>";
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
