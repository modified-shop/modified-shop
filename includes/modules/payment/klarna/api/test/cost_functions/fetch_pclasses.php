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

        echo "Only pclasses for the specified country is shown.<br/><br/>\n";
        echo "<a href='?c=se'>SE</a>,\n";
        echo "<a href='?c=no'>NO</a>,\n";
        echo "<a href='?c=fi'>FI</a>,\n";
        echo "<a href='?c=dk'>DK</a>,\n";
        echo "<a href='?c=de'>DE</a>,\n";
        echo "<a href='?c=nl'>NL</a>\n";

        try {
            $config = new KlarnaConfig(KCONFIG);

            $klarna = new Klarna();
            $klarna->setConfig($config);
            
            if(!isset($_GET['c'])) {
                $_GET['c'] = 'se';
            }
            switch(strtolower(@$_GET['c'])) {
                default:
                case 'se':
                    $klarna->setCountry(KlarnaCountry::SE);
                    $klarna->setCurrency(KlarnaCurrency::SEK);
                    $klarna->setLanguage(KlarnaLanguage::SV);
                    break;
                case 'no':
                    $klarna->setCountry(KlarnaCountry::NO);
                    $klarna->setCurrency(KlarnaCurrency::NOK);
                    $klarna->setLanguage(KlarnaLanguage::NB);
                    break;
                case 'dk':
                    $klarna->setCountry(KlarnaCountry::DK);
                    $klarna->setCurrency(KlarnaCurrency::DKK);
                    $klarna->setLanguage(KlarnaLanguage::DA);
                    break;
                case 'fi';
                    $klarna->setCountry(KlarnaCountry::FI);
                    $klarna->setCurrency(KlarnaCurrency::EUR);
                    $klarna->setLanguage(KlarnaLanguage::FI);
                    break;
                case 'de';
                    $klarna->setCountry(KlarnaCountry::DE);
                    $klarna->setCurrency(KlarnaCurrency::EUR);
                    $klarna->setLanguage(KlarnaLanguage::DE);
                    break;
                case 'nl';
                    $klarna->setCountry(KlarnaCountry::NL);
                    $klarna->setCurrency(KlarnaCurrency::EUR);
                    $klarna->setLanguage(KlarnaLanguage::NL);
                    break;
            }
            
            if(isset($_GET['clear'])) {
                $klarna->clearPClasses();
            }

            if(isset($_GET['update'])) {
                //If the XML file doesn't exist, fetch!
                $klarna->fetchPClasses();
            }

            if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                echo "<table>\n";
                echo "<tr><th>ID</th><th>Description</th><th>Type</th><th>Months</th><th>Start Fee</th><th>Invoice fee</th><th>Interest rate</th><th>Min amount</th><th>Country</th><th>Expire</th></tr>\n";
                foreach($klarna->getPClasses() as $pclass) {
                    echo "<tr onclick='window.open(\"use_pclass.php?id=".$pclass->getId()."&c=$_GET[c]\", \"_self\");'>";
                    echo "<td>".$pclass->getId()."</td>";
                    echo "<td>".$pclass->getDescription()."</td>";
                    echo "<td>".$pclass->getType()."</td>";
                    echo "<td>".$pclass->getMonths()."</td>";
                    echo "<td>".$pclass->getStartFee()."</td>";
                    echo "<td>".$pclass->getInvoiceFee()."</td>";
                    echo "<td>".$pclass->getInterestRate()."</td>";
                    echo "<td>".$pclass->getMinAmount()."</td>";
                    echo "<td>".$pclass->getCountry()."</td>";
                    if($pclass->getExpire() == '-') {
                        echo "<td>-</td>";
                    }
                    else {
                        echo "<td>".date('Y-m-d', $pclass->getExpire())."</td>";
                    }
                    echo "</tr>\n";
                }
                echo "</table>\n<br/>";
                echo "Clicking a pclass will show you the usages.<br />\n";
                echo "<a href='?c=".$_GET['c']."&clear'>Clear</a> or <a href='?c=".$_GET['c']."&update'>update</a> pclasses.";
            }
            else {
                $pclass = $klarna->getPClass(intval($_GET['id']));
                echo "\n<!--";
                var_dump($pclass);
                echo "-->\n";
                echo $pclass->getDescription();
            }
        }
        catch(KlarnaException $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }
        ?></body>
</html>