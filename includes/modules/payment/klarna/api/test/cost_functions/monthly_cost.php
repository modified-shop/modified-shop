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

        try {
            $config = new KlarnaConfig(KCONFIG);

            $klarna = new Klarna();
            $klarna->setConfig($config);
            
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

            $pclass = $klarna->getPClass($_GET['id']);
            
            echo "Calculating monthly cost using PClass#$_GET[id] for $_GET[sum] with flags $_GET[flags]<br/>\n";
            echo "Result: ";
            echo KlarnaCalc::calc_monthly_cost($_GET['sum'], $pclass, $_GET['flags']);
        }
        catch(KlarnaException $e) {
            echo $e->getMessage() . " (#" . $e->getCode() . ")";
        }

        ?></body>
</html>