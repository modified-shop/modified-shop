<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billpay: Error</title>
    <link rel="stylesheet" href="<?php echo DIR_WS_CATALOG; ?>includes/external/billpay/templates/css/billpay.css" type="text/css" />
</head>
<body>
<div class="bpyContent">
    <div class="bpyHeader">
        <img title="BillPay Logo" alt="BillPay Logo" src="https://www.billpay.de/wp-content/uploads/2011/04/LogoSmall_0.png">
    </div>
    <div class="clear-fix"></div>
    <div class="bpyError">
        <h3>Fehlermeldung:</h3>
        <p>
            <?php
            if (!empty($errorString))
            {
                echo $errorString;
            }
            ?>
        </p>
        <a href="#" onclick="history.back();"> &lt;&lt; zurück</a>
        <h3>Dokumentation:</h3>
        <ul>
            <li><a href="https://www.billpay.de/zahlungsinformationen/">Zahlungsinformationen</a></li>
            <li><a href="https://www.billpay.de/integration/download/api/">Programmierbibliotheken</a></li>
        </ul>
    </div>
</div>
</body>
</html>
<?php
exit();



