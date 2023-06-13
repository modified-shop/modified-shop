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
 *
 * (c) 2010 - 2020 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

$queries = array();
$functions = array();
function etsy_add_shipping_profile() {
    $oDB = MagnaDB::gi();
    if ($oDB->tableExists(TABLE_MAGNA_ETSY_PREPARE) && !$oDB->columnExistsInTable('ShippingProfile', TABLE_MAGNA_ETSY_PREPARE)) {
        $oDB->query("ALTER TABLE `".TABLE_MAGNA_ETSY_PREPARE."` ADD `ShippingProfile` varchar(127) NOT NULL;");
        $oDB->query("ALTER TABLE `".TABLE_MAGNA_ETSY_PREPARE."` CHANGE COLUMN `ShippingTemplate` `ShippingTemplate` varchar(127) NULL;");
    }

    if ($oDB->tableExists(TABLE_MAGNA_ETSY_PREPARE)) {
        $oDB->query("UPDATE `".TABLE_MAGNA_ETSY_PREPARE."` SET `ShippingProfile` = `ShippingTemplate`, `ShippingTemplate` = null
                                  WHERE `ShippingProfile` = ''");
    }
    if ($oDB->tableExists(TABLE_MAGNA_CONFIG)) {
        $oldConfigName = 'etsy.ShippingTemplate';
        $newConfigName = 'etsy.ShippingProfile';
        $aEtsyMpIds = $oDB->fetchArray("
		SELECT DISTINCT mpID
		  FROM ".TABLE_MAGNA_CONFIG."
		 WHERE `mkey` = 'etsy.lang'
		       AND `value` <> ''
	", true);
        foreach ($aEtsyMpIds as $iMarketPlace) {
            if ($oDB->tableExists(TABLE_MAGNA_CONFIG)) {
                $aConfigValue = $oDB->fetchRow("
		SELECT value
		  FROM ".TABLE_MAGNA_CONFIG."
		 WHERE `mkey` = '".$oldConfigName."'
		       AND `mpid`= '".$iMarketPlace."'");
                if (isset($aConfigValue['value']) && !empty($aConfigValue['value'])) {
                    $aNewConfigValue = $oDB->fetchRow("
		SELECT value
		  FROM ".TABLE_MAGNA_CONFIG."
		 WHERE `mkey` = '".$newConfigName."'
		       AND `mpid`= '".$iMarketPlace."'");
                    if (!isset($aNewConfigValue['value'])) {
                        $oDB->insert(TABLE_MAGNA_CONFIG, array('mpid' => $iMarketPlace, 'mkey' => $newConfigName, 'value' => $aConfigValue['value']));
                    }
                    $oDB->delete(TABLE_MAGNA_CONFIG, array('mpid' => $iMarketPlace, 'mkey' => $oldConfigName));
                }

            }
        }
    }

}

$functions[] = 'etsy_add_shipping_profile';
