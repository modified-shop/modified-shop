<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Billpay: Plugin Status Screen</title>
    <link rel="stylesheet" href="<?php echo DIR_WS_CATALOG; ?>includes/external/billpay/templates/css/billpay.css" type="text/css" />
</head>
<body>
<div class="bpyContent bpyContentWide">
    <div class="bpyHeader">
        <img title="BillPay Logo" alt="BillPay Logo" src="https://www.billpay.de/wp-content/uploads/2011/04/LogoSmall_0.png">
    </div>
    <div class="clear-fix"></div>
    <div class="bpyPluginStatusScreen">
        <?php
        if (isset($data['message'])) {
            echo '<pre>'.$data['message'].'</pre>';
        }
        ?>
        <h3>Options</h3>
        <ul>
            <li><a href="?auth=<?php echo $data['auth']; ?>&showLog=1">Show log</a></li>
            <li><a href="?auth=<?php echo $data['auth']; ?>&md5file=1">Check file integrity</a></li>
        </ul>
        <h3>Plugin config (<?php echo $data['paymentMethod']; ?>):</h3>
        <table>
            <tr>
                <td>
                    <h6>MODULE_PAYMENT_BILLPAY%</h6>
                    <table>
                        <tr><th>key</th><th>value</th></tr>
                        <?php foreach ($data['bpyConfigPayment'] as $row) { ?>
                            <tr><td><?php echo $row['configuration_key']; ?></td><td><?php echo $row['configuration_value']; ?></td></tr>
                        <?php } ?>
                        <?php if (isset($data['bpyConfigPaymentInstalled'])): ?>
                        <tr><td>MODULE_PAYMENT_INSTALLED</td><td><?= $data['bpyConfigPaymentInstalled'] ?></td></tr>
                        <?php endif; ?>
                    </table>
                </td>
                <td>
                    <h6>MODULE_ORDER_TOTAL_BILLPAY%</h6>
                    <table>
                        <tr><th>key</th><th>value</th></tr>
                        <?php foreach ($data['bpyConfigOT'] as $row) { ?>
                            <tr><td><?php echo $row['configuration_key']; ?></td><td><?php echo $row['configuration_value']; ?></td></tr>
                        <?php } ?>
                    </table>
                    <h6>MODULE_ORDER_TOTAL_Z_PAYLATER_%</h6>
                    <table>
                        <tr><th>key</th><th>value</th></tr>
                        <?php foreach ($data['bpyConfigOTPL'] as $row) { ?>
                            <tr><td><?php echo $row['configuration_key']; ?></td><td><?php echo $row['configuration_value']; ?></td></tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
        </table>
        <!-- <?php print_r($data['bpyConfigPayment']); ?> -->
        <!-- <?php print_r($data['bpyConfigOT']); ?> -->

        <h3>Plugin log (<?php echo $data['pluginLogPath']; ?>):</h3>
        <pre><?php echo $data['log']; ?></pre>

        <h3>File integrity (hooks) (<?= $data['shop_identifier']; ?>):</h3>
        <table>
            <?php foreach ($data['hookData'] as $file => $hooks) { ?>
            <tr>
                <th colspan="2" style="text-align: left;"><h6><?php echo $file; ?></h6></th>
            </tr>
                <?php foreach ($hooks as $label => $hook) { ?>
                    <tr>
                        <th style="text-align: right; vertical-align: top;"><?php echo $label; ?></th>
                        <td style="vertical-align: top;">
                            <?php
                            if ($hook['isHooked']) {
                                echo 'hooked.';
                            } elseif (!empty($hook['currentContent'])) {
                                $currentContent = trim($hook['currentContent']);
                                $linkText = empty($currentContent) ? 'write' : 'rewrite';
                                echo 'Existing hook code: <br />';
                                echo '<pre>' . $currentContent . '</pre>';
                                echo '<span style="color: red;">Warning: if existing code is not empty and you don\'t recognize it, don\'t rewrite it.</span><br />';
                                echo '<a href="' . $data['currentUrl'] . '&hookRewrite=' . preg_replace("@/@", ",", $file) . "@" . $label . '">' . $linkText . '</a>';
                            } elseif (!$hook['isHookPre']) {
                                echo 'Pre hook not found.';
                            } elseif (!$hook['isHookPost']) {
                                echo 'Post hook not found.';
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            <?php }; ?>
        </table>
        <pre>

        </pre>
        <!-- <?php print_r($data['hookData']); ?> -->

        <h3>File integrity (plugin):</h3>
        <?php if (!empty($_GET['md5file']) && ($_GET['md5file'] == "1")) { ?>
        <table>
            <thead>
            <tr>
                <th>Length</th>
                <th>Md5</th>
                <th>File</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($data['filesMd5'] as $name => $file) { ?>
            <tr>
                <td style="<?php if ($file['len'] != $file['len_current']) {echo 'color: red;';} ?>"><?php echo $file['len'].'<br>'.$file['len_current']; ?></td>
                <td style="<?php if ($file['md5'] != $file['md5_current']) {echo 'color: red;';} ?>"><?php echo $file['md5'].'<br>'.$file['md5_current']; ?></td>
                <td><a href="?auth=<?php echo $data['auth']; ?>&md5file=<?php echo preg_replace("@/@", "!", $name); ?>"><?php echo $name; ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
            <a href="?auth=<?php echo $data['auth']; ?>&md5file=1">Check file integrity</a>
        <?php } ?>
        <?php if (!empty($data['fileContent'])) { ?>
        <h6><?php echo $this->data['md5file']; ?></h6>
        <?php highlight_string($data['fileContent']); ?>
        <?php } ?>


        <h3>Development actions</h3>
        <div>
            <form method="GET" action="?">
                <input type="hidden" name="auth" value="<?php echo $data['auth']; ?>">
                <label for="newOrderId">Change orderId autoincrement</label>
                <input id="newOrderId" name="newOrderId">
                <input type="submit" value="Set orderId">
            </form>
            <form method="GET" action="?">
                <input type="hidden" name="auth" value="<?php echo $data['auth']; ?>">
                <label for="newOrderPrefix">Change order prefix</label>
                <input id="newOrderPrefix" name="newOrderPrefix">
                <input type="submit" value="Set order prefix">
            </form>
        </div>

    </div>
</div>
</body>
</html>
<?php
exit();



