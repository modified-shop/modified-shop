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
 * $Id: dawanda.php 3830 2014-05-06 13:00:00Z tim.neumann $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require(DIR_MAGNALISTER_MODULES.'magnacompatible/config.php');

$mpconfig['auth']['authkeys'] = array('access.MPUSERNAME', 'access.MPPASSWORD', 'access.MPAPITOKEN');

$mpconfig['pages']['conf']['class'] = 'FyndiqConfigure';
$mpconfig['pages']['prepare']['class'] = 'FyndiqPrepare';
$mpconfig['pages']['checkin']['class'] = 'FyndiqCheckin';

getDBConfigValue(array('fyndiq.usevariations', 'val'), $this->mpID, true) ? $mpconfig['checkin']['Variations'] ='yes':
                                                                            $mpconfig['checkin']['Variations'] ='no';

$mpconfig['checkin']['Categories']['Marketplace'] = 'no';
