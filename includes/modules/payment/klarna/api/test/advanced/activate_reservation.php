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
        
        if(isset($_GET['rno'])) {
            try {
                $result = $klarna->activateReservation($pno, $_GET['rno'], $gender);
                echo "View your invoice here: <a href=https://online.klarna.com/invoices/$result[1].pdf>$result[1]</a> ($result[0])<br />\n";
                echo "<br/><h3>Invoice functions</h3><ul>
                        <li><a href='../other/update_orderno.php?invno=$result[1]&orderno=666' target='_blank'>Update order number to 666</a></li>
                        <li><a href='../invoice_functions/credit_invoice.php?invno=$result[1]' target='_blank'>Credit invoice</a></li>
                        <li><a href='../invoice_functions/credit_part.php?invno=$result[1]' target='_blank'>Credit part</a></li>
                        <li><a href='../invoice_functions/email_invoice.php?invno=$result[1]' target='_blank'>Email invoice</a></li>
                        <li><a href='../invoice_functions/send_invoice.php?invno=$result[1]' target='_blank'>Send invoice</a></li>
                        <li><a href='../invoice_functions/return_amount.php?invno=$result[1]&vat=25&amount=5' target='_blank'>Return amount 5 at 25% VAT</a></li>
                        <li><a href='../other/invoice_address.php?invno=$result[1]' target='_blank'>Invoice address</a></li>
                        <li><a href='../other/invoice_amount.php?invno=$result[1]' target='_blank'>Invoice amount</a></li>
                        <li><a href='../other/invoice_part_amount.php?invno=$result[1]&vat=25&amount=5' target='_blank'>Invoice part amount</a></li>
                      <ul>";
            }
            catch(Exception $e) {
                echo $e->getMessage() . " (" . $e->getCode() . ")";
            }
        }
        else {
            echo "First create a invoice <a href='reserve_amount.php'>here</a><br />";
        }

        ?></body>
</html>