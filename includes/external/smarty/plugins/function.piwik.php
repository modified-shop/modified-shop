<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2011 WEB-Shop Software (function.piwik.php 1871) http://www.webs.de/

   Add the Piwik tracking code (and the possibility to track the order details as well)

   Usage: Put one of the following tags into the templates\yourtemplate\index.html at the bottom
   {piwik url=piwik.example.com id=1} or
   {piwik url=piwik.example.com id=1 goal=1}
   where "id=1" is the domain-ID you want to track (see your Piwik configuration for details)

   Asynchronous Piwik tracking is possible from Piwik version 1.1 and higher
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   extended version to track
   - viewed products
   - categories
   - abandoned shopping carts
   - placed orders
   noRiddle 05-2013

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

function smarty_function_piwik($params, $smarty) {
  return;
}
?>