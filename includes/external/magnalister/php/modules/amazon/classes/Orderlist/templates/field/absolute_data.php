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
 * $Id$
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */
/* @var $this MLOrderlistAmazonAbstract */
/* @var $aRow array */
/* @var $aFieldConfig array */
class_exists('MLOrderlistAmazonAbstract') or die();
?>
<td>
    <?php
        if ($aField['head']['fieldname'] == 'AmazonOrderID') {
            global $_MagnaSession;
            $sCancelledStatus = getDBConfigValue('amazon.orderstatus.cancelled', $_MagnaSession['mpID']);
            $sShippedStatus = getDBConfigValue('amazon.orderstatus.shipped.shipped', $_MagnaSession['mpID']);
            
            $fulfillment = $aRow['FulfillmentChannel'];
            if ($fulfillment !== 'MFN-Prime' && $fulfillment !== 'MFN' && $fulfillment !== 'Business') {
                $sLogo = 'amazon_fba';

                if (isset($aRow['IsBusinessOrder']) && $aRow['IsBusinessOrder'] == 'true') {
                    $sLogo .= '_business';
                }

                $sLogo .= '_orderview';
            } else {
                // business, prime and regular orders could also be cancelled or shipped
                $suffix = '';
                if ($fulfillment === 'MFN-Prime') {
                    $suffix = '_prime';
                    if (isset($aRow['ShipServiceLevel'])) {
                        $sShipServiceLevel = $aRow['ShipServiceLevel'];
                        if ($sShipServiceLevel === 'NextDay') {
                            $suffix .= '_nextday';
                        } else if ($sShipServiceLevel === 'SameDay') {
                            $suffix .= '_sameday';
                        } else if ($sShipServiceLevel === 'SecondDay') {
                            $suffix .= '_secondday';
                        }
                    }
                } elseif ($fulfillment === 'Business') {
                    $suffix = '_business';
                }

                $sStatus = MagnaDB::gi()->fetchOne("
                    SELECT shipping_class
                      FROM ".TABLE_ORDERS."
                     WHERE orders_id='".MagnaDB::gi()->escape($aRow['ShopOrderID'])."'");

                if (false) {//todo
                    $sLogo = 'amazon_orderview_error';
                } elseif ($sCancelledStatus == $sStatus) {
                    $sLogo = 'amazon_orderview_cancelled'.$suffix;
                } elseif (in_array($sStatus, $sShippedStatus)) {
                    $sLogo = 'amazon_orderview_shipped'.$suffix;
                } else {
                    $sLogo = 'amazon_orderview'.$suffix;
                }
            }

            $sLogo = $sLogo . '.png';
            echo '<img src="'.DIR_MAGNALISTER_WS_IMAGES.'logos/'.$sLogo.'" /> ' . fixHTMLUTF8Entities($aRow[$aField['head']['fieldname']]);
        } else {
            echo fixHTMLUTF8Entities($aRow[$aField['head']['fieldname']]);
        }
     ?>
</td>
