<?php

/**
 * Class BillpayHooks
 * Class used to store code hooks required to use this plugin.
 */
class BillpayHooks
{
    var $billpay;
    var $modification = '';
    var $version = '';
    var $hookData = array();
    /**
     * Returns hooks for current shop.
     *
     * @return array
     */
    function getHookData()
    {
        $this->billpay = new BillPay();
        $shop = $this->billpay->getShopModification();
        $this->modification = $shop['modification'];
        $this->version = $shop['version'];
        $this->hookData = array();
        if ($this->modification == 'gambio') {
            return $this->prepareGambio();
        }
        if ($this->modification == 'mastershop') {
            return $this->prepareMastershop();
        }
        if ($this->modification == 'xtcmod') {
            return $this->prepareXtcmod();
        }
        if ($this->modification == 'xtc3') {
            return $this->prepareXtc304r2();
        }
        if ($this->modification == 'commerceseo') {
            return $this->prepareCommerceSeo();
        }
        return $this->hookData;
    }

    function getShopIdentifier()
    {
        return $this->modification.' '.$this->version;
    }

    function prepareXtc304r2()
    {
        $this->addHook('admin/print_order.php', 'invoiceRender',
            '$payment_method=constant(strtoupper(\'MODULE_PAYMENT_\'.$order->info[\'payment_method\'].\'_TEXT_TITLE\'));',
                'require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . \'/external/billpay/utils/billpay_display_bankdata.php\');'
                .'$payment_method .= display_billpay_bankdata();',
            '$smarty->assign(\'PAYMENT_METHOD\',$payment_method);'
        );
        $this->addHook('admin/orders.php', 'statusChange',
            'if ($check_status[\'orders_status\'] != $status || $comments != \'\') {',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_status_requests.php\');',
            'xtc_db_query("update ".TABLE_ORDERS." set orders_status = \'".xtc_db_input($status)."\''
        );
        $this->addHook('admin/orders.php', 'orderDelete',
            '\'deleteconfirm\' :',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_order_delete.php\');',
            '$oID ='
        );
        $this->addHook('admin/orders_edit.php', 'orderEditValidation',
            'Funktionen und Klassen Ende',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/base/BillpayOrderEdit.php\');'
                .'$billpayOrderEdit = new BillpayOrderEdit();'
                .'$billpayOrderEdit->onBeforeUpdate();',
            '// Adressbearbeitung Anfang'
        );
        $this->addHook('admin/orders_edit.php', 'orderEditSync',
            'des Zwischenspeichers Ende',
                '$billpayOrderEdit->onAfterUpdate();',
            'xtc_redirect(xtc_href_link(FILENAME_ORDERS, \'action=edit&oID=\'.$_POST[\'oID\']));'
        );
        // send_order.php
        $this->addHook('send_order.php', 'emailOrderCreated',
            '$smarty->assign(\'PHONE\',$order->customer[\'telephone\']);',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail.php\');',
            '// PAYMENT MODUL TEXTS'
        );
        return $this->hookData;
    }

    function prepareXtcmod()
    {
        $this->addHook((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'print_order.php', 'invoiceRender',
            '$payment_method=constant(strtoupper(\'MODULE_PAYMENT_\'.$order->info[\'payment_method\'].\'_TEXT_TITLE\'));',
                'require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . \'/external/billpay/utils/billpay_display_bankdata.php\');'
               .'$payment_method .= display_billpay_bankdata();',
            '$smarty->assign(\'PAYMENT_METHOD\',$payment_method);'
        );
        $this->addHook((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'orders.php', 'statusChange',
            'if ($check_status[\'orders_status\'] != $status || $comments != \'\') {',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_status_requests.php\');',
            'xtc_db_query("'
        );
        $this->addHook((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'orders.php', 'orderDelete',
            '\'deleteconfirm\' :',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_order_delete.php\');',
            'xtc_remove_order'
        );
        $this->addHook((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'orders_edit.php', 'orderEditValidation',
            'Funktionen und Klassen Ende',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/base/BillpayOrderEdit.php\');'
               .'$billpayOrderEdit = new BillpayOrderEdit();'
               .'$billpayOrderEdit->onBeforeUpdate();',
            '$action = (isset($_GET[\'action\']) ? $_GET[\'action\'] : \'\');'
        );
        $this->addHook((defined('DIR_ADMIN') ? DIR_ADMIN : 'admin/').'orders_edit.php', 'orderEditSync',
            'des Zwischenspeichers Ende',
                '$billpayOrderEdit->onAfterUpdate();',
            'xtc_redirect(xtc_href_link(FILENAME_ORDERS, \'action=edit&oID=\'.(int)$_POST[\'oID\']));'
        );
        $this->addHook('send_order.php', 'emailOrderCreated',
            '$smarty->assign(\'PHONE\',$order->customer[\'telephone\']);',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail.php\');',
            '//BOF  - web28 - 2010-03-27 PayPal Bezahl-Link'
        );
        return $this->hookData;
    }

    function prepareMastershop()
    {
        // admin/includes/classes/pdfbill.php
        $this->addHook('admin/includes/classes/pdfbill.php', 'mastershopInvoice',
            '$cluster_end->outbuffer($this, $this->lMargin, $this->GetY() );',
                'require_once(DIR_FS_CATALOG.\'includes/external/billpay/utils/billpay_display_pdf_data.php\');',
            '// --------------- Anlage ---------------------------------------'
        );
        $this->addHook('admin/orders.php', 'statusChange',
            'if ($check_status[\'orders_status\'] != $status || $comments != \'\' || $dpd_tracking_id != $check_status[\'dpd_tracking_id\'] || $spi_tracking_id != $check_status[\'spi_tracking_id\'] || $hermes_tracking_id != $check_status[\'hermes_tracking_id\'] || $ups_tracking_id != $check_status[\'ups_tracking_id\'] || $gls_tracking_id != $check_status[\'gls_tracking_id\'] || $dhl_tracking_id != $check_status[\'dhl_tracking_id\'] ) {',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_status_requests.php\');',
            'xtc_db_query("update ".TABLE_ORDERS." set orders_status'
        );
        $this->addHook('admin/orders.php', 'orderDelete',
            '\'deleteconfirm\' :',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_order_delete.php\');',
            '$oID ='
        );
        $this->addHook('admin/orders_edit.php', 'orderEditValidation',
            'Funktionen und Klassen Ende',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/base/BillpayOrderEdit.php\');'
                .'$billpayOrderEdit = new BillpayOrderEdit();'
                .'$billpayOrderEdit->onBeforeUpdate();',
            '// Adressbearbeitung Anfang'
        );
        $this->addHook('admin/orders_edit.php', 'orderEditSync',
            '// Neue totalSum schreiben Ende',
                '$billpayOrderEdit->onAfterUpdate();',
            'xtc_redirect(xtc_href_link(FILENAME_ORDERS, \'action=edit&oID=\'.$_POST[\'oID\']));'
        );
        $this->addHook('send_order.php', 'emailOrderCreated',
            '$smarty->assign(\'PHONE\',$order->customer[\'telephone\']);',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail.php\');',
            '// PAYMENT MODUL TEXTS'
        );
        return $this->hookData;
    }

    function prepareGambio()
    {
        // admin/print_order.php is no longer used in Gambio
        $this->addHook('admin/orders.php', 'statusChange',
            'if(xtc_db_input($status) == gm_get_conf(\'GM_ORDER_STATUS_CANCEL_ID\')) {'."\r\n".
            '					$gm_update = "gm_cancel_date = now(),";'."\r\n".
            '				}',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_status_requests.php\');',
            'xtc_db_query("'
        );
        $this->addHook('admin/orders.php', 'orderDelete',
            '\'deleteconfirm\':',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_order_delete.php\');',
            'if($_SESSION'
        );
        if (version_compare($this->version, '2.1', '<')) {
            $this->addHook('admin/gm/classes/gmOrderPDF.php', 'gambioInvoice < 2.1',
                'order info data'."\r\n".
                '			*/',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_gm_pdf.php\');',
                'if(!empty($this->order_info))'
            );
        }
        if (version_compare($this->version, '2.1', '>=')) {
            $this->addHook('admin/gm/classes/gmOrderPDF.php', 'gambioInvoice >= 2.1',
                'order info data'."\r\n".
                '		 */',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_gm_pdf.php\');',
                'if(!empty($this->order_info))'
            );
        }

        if (version_compare($this->version, '2.1', '<')) {
            $this->addHook('send_order.php', 'emailOrderCreated < 2.1',
                '$smarty->assign(\'PHONE\',$order->customer[\'telephone\']);',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail.php\');',
                'if(defined(\'EMAIL_SIGNATURE\')) {'
            );
        }
        if (version_compare($this->version, '2.1', '>=')) {
            $this->addHook('system/classes/orders/SendOrderProcess.inc.php', 'emailOrderCreated => 2.1',
                '$t_payment_info_text = \'\';',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail_gambio21.php\');',
                'switch($order->info[\'payment_method\'])'
            );
        }

        // order edit
        $this->addHook('admin/orders_edit.php', 'orderEditValidation',
            '$xtPrice = new xtcPrice($order->info[\'currency\'], $order->info[\'status\']);',
            'require_once(DIR_FS_CATALOG . \'includes/external/billpay/base/BillpayOrderEdit.php\');'
            .'$billpayOrderEdit = new BillpayOrderEdit();'
            .'$billpayOrderEdit->onBeforeUpdate();',
            '// Adressbearbeitung Anfang'
        );
        $this->addHook('admin/orders_edit.php', 'orderEditSync',
            'des Zwischenspeichers Ende',
            '$billpayOrderEdit->onAfterUpdate();',
            'xtc_redirect(xtc_href_link(FILENAME_ORDERS, \'action=edit&oID=\' . (int)$_POST[\'oID\']));'
        );

        return $this->hookData;
    }

    function prepareCommerceSeo()
    {
        $this->addHook('admin/print_order.php', 'invoiceRender',
            '$payment_method = constant(strtoupper(\'MODULE_PAYMENT_\' . $order->info[\'payment_method\'] . \'_TEXT_TITLE\'));',
                'require_once(DIR_FS_CATALOG . DIR_WS_INCLUDES . \'/external/billpay/utils/billpay_display_bankdata.php\');'
                .'$payment_method .= display_billpay_bankdata();',
            '$smarty->assign(\'PAYMENT_METHOD\', $payment_method);'
        );
        $this->addHook('admin/includes/modules/order_update_order.php', 'statusChange',
            'if ($check_status[\'orders_status\'] != $status || $comments != \'\') {',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_status_requests.php\');',
            'if ($status == MODULE_PAYMENT_RMAMAZON_ORDER_STATUS_STORNO || $status == MODULE_PAYMENT_RMAMAZON_ORDER_STATUS_SHIPPED) {'
        );
        $this->addHook('admin/orders.php', 'orderDelete',
            '\'deleteconfirm\' :',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_order_delete.php\');',
            'include(\'includes/modules/order_deleteconfirm.php\');'
        );
        $this->addHook('admin/orders_edit.php', 'orderEditValidation',
            'Funktionen und Klassen Ende',
                'require_once(DIR_FS_CATALOG . \'includes/external/billpay/base/BillpayOrderEdit.php\');'
                .'$billpayOrderEdit = new BillpayOrderEdit();'
                .'$billpayOrderEdit->onBeforeUpdate();',
            '$action = (isset($_GET[\'action\']) ? $_GET[\'action\'] : \'\');'
        );
        $this->addHook('admin/orders_edit.php', 'orderEditSync',
            'des Zwischenspeichers Ende',
                '$billpayOrderEdit->onAfterUpdate();',
            'xtc_redirect(xtc_href_link(FILENAME_ORDERS, \'action=edit&oID=\' . (int) $_POST[\'oID\']));'
        );
        // send_order.php
        $this->addHook('send_order.php', 'emailOrderCreated',
            '$smarty->assign(\'PHONE\', $order->customer[\'telephone\']);',
            'require_once(DIR_FS_CATALOG . \'includes/external/billpay/utils/billpay_mail.php\');',
            '$smarty->assign(\'WIDERRUF_HEAD\', $widerruf[\'content_heading\']);'
        );
        return $this->hookData;
    }

    function addHook($file, $label, $pre, $content, $post)
    {
        if (!isset($this->hookData[$file])) {
            $this->hookData[$file] = array();
        }
        $this->hookData[$file][$label] = array(
            'hash'      =>  $this->getHash($pre, $content, $post),
            'pre'       =>  $pre,
            'content'   =>  $content,
            'post'      =>  $post,
        );
    }

    function getHash($pre, $content, $post)
    {
        return md5($pre.$content.$post);
    }

    function isHookPresent($fileContent, $hookContent)
    {
        return strpos($fileContent, $hookContent) !== false;
    }
}
