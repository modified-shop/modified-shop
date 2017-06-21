<?php
/**
 * 888888ba                 dP  .88888.                    dP
 * 88    `8b                88 d8'   `88                   88
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b.
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P'
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
<<<<<<< Upstream, based on Amazon_Shipment_Module
 * $Id: 075.sql.php 2332 2013-04-04 16:12:19Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();

/* Tabellenstruktur fuer Tabelle `magnalister_config` */
$queries[] = 'CREATE TABLE IF NOT EXISTS `'.TABLE_MAGNA_GLOBAL_SELECTION.'` (
  `mpID` int(8) unsigned NOT NULL,
  `selectionname` varchar(50) NOT NULL,
  `data` text NOT NULL,
  `session_id` varchar(64) NOT NULL,
  `element_id` varchar(100) NOT NULL,
  UNIQUE KEY `UC_mp_order_id` (`mpID`,`element_id`,`session_id`,`selectionname`)
)
';