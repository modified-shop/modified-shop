<body>

<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table class="tableBody">
    <tr>
        <?php //left_navigation
        if (USE_ADMIN_TOP_MENU == 'false') {
            echo '<td class="columnLeft2">' . PHP_EOL;
            echo '<!-- left_navigation //-->' . PHP_EOL;
            require_once(DIR_WS_INCLUDES . 'column_left.php');
            echo '<!-- left_navigation eof //-->' . PHP_EOL;
            echo '</td>' . PHP_EOL;
        }
        ?>
        <!-- body_text //-->
        <td class="boxCenter">
            <div class="pageHeadingImage"><?php echo xtc_image(DIR_WS_ICONS . 'heading/icon_modules.png'); ?></div>
            <div class="pageHeading pdg2">Shopgate</div>
            <div class="main">Modules</div>
            <table class="tableCenter">
                <tr>
                    <td valign="middle" class="dataTableHeadingContent" style="width:250px;">
                        <?php echo SHOPGATE_CONFIG_TITLE; ?>
                    </td>
                    <td valign="middle" class="dataTableHeadingContent">
                        <a href="<?php echo xtc_href_link('modules.php', 'set=payment&module=shopgate'); ?>"><u>Einstellungen</u></a>
                    </td>
                </tr>
                <tr style="height: 100%;">
                    <td class="main" style="height: 100%; vertical-align: top;" colspan="2">


