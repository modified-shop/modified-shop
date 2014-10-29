<?php
/* -----------------------------------------------------------------------------------------
   $Id: HttpResponse.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   Copyright (c) 2013 PayPal SE and Bernd Blazynski

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

/*
* id = HttpResponse.php
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

class Billsafe_HttpResponse {
  public $statusCode;
  public $statusText;
  public $contentType;
  public $contentLength;
  public $body;
}

?>