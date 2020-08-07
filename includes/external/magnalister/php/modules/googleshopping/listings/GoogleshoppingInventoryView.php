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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class GoogleshoppingInventoryView extends MagnaCompatibleInventoryView {
    protected function getFields() {
        return array(
            'SKU' => array(
                'Label' => ML_LABEL_SKU,
                'Sorter' => 'sku',
                'Getter' => null,
                'Field' => 'SKU'
            ),
            'Title' => array(
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => null,
                'Getter' => 'getTitle',
                'Field' => null,
            ),
            'Price' => array(
                'Label' => ML_GENERIC_PRICE,
                'Sorter' => 'price',
                'Getter' => 'getItemPrice',
                'Field' => null
            ),
            'Quantity' => array(
                'Label' => ML_LABEL_QUANTITY,
                'Sorter' => 'quantity',
                'Getter' => null,
                'Field' => 'Quantity',
            ),
            'StartTime' => array(
                'Label' => ML_GENERIC_CHECKINDATE,
                'Sorter' => 'starttime',
                'Getter' => 'getItemStartTime',
                'Field' => null
            ),
            'LastSync' => array(
                'Label' => 'Letzte Synchronisation',
                'Sorter' => null,
                'Getter' => 'getItemLastSyncTime',
                'Field' => null
            ),
            'Status' => array(
                'Label' => 'Status',
                'Sorter' => null,
                'Getter' => 'getStatus',
                'Field' => null
            ),
        );
    }

    protected function getItemStartTime($item) {
        $item['DateAdded'] = strtotime($item['DateAdded']);
        return '<td>'.date("d.m.Y", $item['DateAdded']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateAdded']).'</span>'.'</td>';
    }

    protected function getItemLastSyncTime($item) {
        $item['DateUpdated'] = strtotime($item['DateUpdated']);
        if ($item['DateUpdated'] < 0) {
            return '<td>-</td>';
        }
        return '<td>'.date("d.m.Y", $item['DateUpdated']).' &nbsp;&nbsp;<span class="small">'.date("H:i", $item['DateUpdated']).'</span>'.'</td>';
    }

    protected function getStatus($item) {
        if (!isset($item['Status'])) {
            return '<td>-</td>';
        }
        return '<td>'.$item['Status'].'</td>';
    }
}
