<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

use PHPMailer\PHPMailer\SMTP;

class xoauth_smtp extends SMTP
{
    public function client_send($data, $command = '')
    {
        if (!in_array($command, array('AUTH', 'OAuth TOKEN'), true)) {
            return parent::client_send($data, $command);
        }

        $debugLevel = $this->do_debug;
        $this->edebug('CLIENT -> SERVER: [credentials hidden]', self::DEBUG_CLIENT);
        $this->do_debug = self::DEBUG_OFF;

        try {
            return parent::client_send($data, $command);
        } finally {
            $this->do_debug = $debugLevel;
        }
    }
}
