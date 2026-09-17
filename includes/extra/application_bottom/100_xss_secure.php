<?php

/* --------------------------------------------------------------
 $Id$

 modified eCommerce Shopsoftware
 http://www.modified-shop.org

 Copyright (c) 2009 - 2014 [www.modified-shop.org]
 --------------------------------------------------------------
 Released under the GNU General Public License
 --------------------------------------------------------------*/

if (defined('XSS_SEND_LOG') && XSS_SEND_LOG === true) {
    $xss_files_array = glob(DIR_FS_LOG . 'xss_attacks_*.mail');
    if (is_array($xss_files_array) && count($xss_files_array) > 0) {
        foreach ($xss_files_array as $xss_file) {
            $mail_txt = @file_get_contents($xss_file);
            if ($mail_txt === false) {
                continue;
            }

            $sent = xtc_php_mail(
                EMAIL_SUPPORT_ADDRESS,
                EMAIL_SUPPORT_NAME,
                EMAIL_SUPPORT_ADDRESS,
                EMAIL_SUPPORT_NAME,
                EMAIL_SUPPORT_FORWARDING_STRING,
                EMAIL_SUPPORT_ADDRESS,
                EMAIL_SUPPORT_NAME,
                '',
                '',
                'Security Alert - ' . STORE_NAME,
                nl2br(htmlspecialchars($mail_txt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')),
                $mail_txt
            );

            if ($sent === true) {
                @unlink($xss_file);
            }
        }
    }
}
