<?php
/* --------------------------------------------------------------
$Id: customers_group.php André Estel $

Estelco - Ebusiness & more
http://www.estelco.de

Copyright (c) 2008 Estelco
--------------------------------------------------------------
Released under the GNU General Public License
--------------------------------------------------------------*/

require('includes/application_top.php');

switch ($_GET['action']) {
    case 'send':
        //var_dump($_POST);
        if (isset($_POST['cg']) && is_array($_POST['cg'])) {
            if (isset($_POST['categories']) || isset($_POST['products']) || isset($_POST['content'])) {
                if (isset($_POST['categories'])) {
                    foreach ($_POST['cg'] as $cgID=>$value) {
                        xtc_db_query('UPDATE ' . TABLE_CATEGORIES . ' SET group_permission_' . (int)$cgID . '=' . ($_POST['permission'] == 'true'?'1':'0'));
                    }
                    $messageStack->add(constant('TEXT_CATEGORIES_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
                if (isset($_POST['products'])) {
                    foreach ($_POST['cg'] as $cgID=>$value) {
                        xtc_db_query('UPDATE ' . TABLE_PRODUCTS . ' SET group_permission_' . (int)$cgID . '=' . ($_POST['permission'] == 'true'?'1':'0'));
                    }
                    $messageStack->add(constant('TEXT_PRODUCTS_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
                if (isset($_POST['content'])) {
                    $content_query = xtc_db_query('SELECT content_id, group_ids FROM ' . TABLE_CONTENT_MANAGER . ' ORDER BY content_id');
                    while ($result = xtc_db_fetch_array($content_query)) {
                        $values = explode(',', $result['group_ids']);
                        if (in_array('', $values)) {
                            unset($values[array_search('', $values)]);
                        }
                        if ($_POST['permission'] == 'true') {
                            foreach ($_POST['cg'] as $cgID=>$value) {
                                if (!in_array('c_' . $cgID . '_group', $values)) {
                                    $values[] = 'c_' . $cgID . '_group';
                                }
                            }
                            $group_ids = implode(',', $values);
                            xtc_db_query('UPDATE ' . TABLE_CONTENT_MANAGER . ' SET group_ids=\'' . $group_ids . '\' WHERE content_id=' . $result['content_id']);
                        } else {
                            foreach ($_POST['cg'] as $cgID=>$value) {
                                if (in_array('c_' . $cgID . '_group', $values)) {
                                    unset($values[array_search('c_' . $cgID . '_group', $values)]);
                                }
                            }
                            $group_ids = implode(',', $values);
                            xtc_db_query('UPDATE ' . TABLE_CONTENT_MANAGER . ' SET group_ids=\'' . $group_ids . '\' WHERE content_id=' . $result['content_id']);
                        }
                    }
                    $messageStack->add(constant('TEXT_CONTENT_SUCCESSFULLY_' . ($_POST['permission'] == 'true'?'SET':'UNSET')), 'success');
                }
            } else {
                $messageStack->add(ERROR_PLEASE_SELECT_SHOP_AREA);
            }
        } else {
            $messageStack->add(ERROR_PLEASE_SELECT_CUSTOMER_GROUP);
        }
        break;
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script type="text/javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
        <td class="columnLeft2" width="<?php echo BOX_WIDTH; ?>" valign="top">
            <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        </td>
        <td  class="boxCenter" width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
    <tr>
        <td>
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="80" rowspan="2"><?php echo xtc_image(DIR_WS_ICONS.'heading_customers.gif'); ?></td>
                    <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="main" valign="top">Customers</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td valign="top">
            <table border="0" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                    <td class="main">
                    <form action="customers_group.php?action=send" method="post">
                    <?php
                    $group_query = xtc_db_query('SELECT customers_status_id,
                                                        customers_status_name
                                                 FROM ' . TABLE_CUSTOMERS_STATUS . '
                                                 WHERE language_id=' . (int)$_SESSION['languages_id'] . '
                                                 ORDER BY customers_status_id ASC');
                    while ($result = xtc_db_fetch_array($group_query)) {
                        echo xtc_draw_checkbox_field('cg[' . $result['customers_status_id'].']', '1') . ' ' . $result['customers_status_name'] . '<br />';
                    }
                    echo '<br /><br />';
                    echo xtc_draw_checkbox_field('categories', '1') . ' ' . TEXT_CATEGORIES . '<br />';
                    echo xtc_draw_checkbox_field('products', '1') . ' ' . TEXT_PRODUCTS . '<br />';
                    echo xtc_draw_checkbox_field('content', '1') . ' ' . TEXT_CONTENT . '<br />';
                    echo '<br /><br />';
                    echo '<strong>' . TEXT_PERMISSION . ':</strong> ' . TEXT_SET . ' ' . xtc_draw_radio_field('permission', 'true', true) . ' ' . TEXT_UNSET . ' ' . xtc_draw_radio_field('permission', 'false', false) . '<br />';
                    echo '<br /><br />' . xtc_draw_input_field('senden', TEXT_SEND, '', false, 'submit');
                    ?>
                    </form>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br />
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>