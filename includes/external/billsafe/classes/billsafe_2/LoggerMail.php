<?php
/* -----------------------------------------------------------------------------------------
   $Id: LoggerMail.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = LoggerMail.php
* location = /includes/external/billsafe/classes/billsafe_2 // DokuMan - 2012-06-19 - move billsafe to external directory
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* @package BillSAFE_2
* @copyright (C) 2013 PayPal SE and Bernd Blazynski
* @license GPLv2
*/

class Billsafe_LoggerMail implements Billsafe_Logger {
  private $_email;
  private $_log = '';

  public function __construct($email) {
    $this->_email = $email;
  }

  public function __destruct() {
    if (!empty($this->_log)) @mail($this->_email, 'BillSAFE SDK Verbose Log', $this->_log);
  }

  public function log($message) {
    $this->_log .= '['.date('Y-m-d H:i:s')."]\r\n".$message."\r\n\r\n";
  }
}

?>