<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  // The module status alone, not the customer group: a customer the shop owner moved into a b2b
  // group still sees the label of their older orders, and without these two files the dialogue
  // cannot open and the label stays unstyled. The constant answers that without loading anything.
  if (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true') {
    echo '<link rel="stylesheet" href="'.DIR_WS_CATALOG.'images/guarantee_labels/guarantee_labels.css">'.PHP_EOL;
    echo '<script src="'.DIR_WS_CATALOG.'images/guarantee_labels/guarantee_labels.js" defer></script>'.PHP_EOL;
  }
