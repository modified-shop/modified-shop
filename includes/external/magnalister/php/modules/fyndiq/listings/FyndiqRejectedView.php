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
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class FyndiqRejectedView extends MagnaCompatibleInventoryView {

    public function prepareInventoryData() {
        global $magnaConfig;

        $result = $this->getInventory();
        if (($result !== false) && !empty($result['DATA'])) {
            $this->renderableData = $result['DATA'];
            foreach ($this->renderableData as &$item) {
                if (isset($item['ItemTitle'])) {
                    $item['Title'] = $item['ItemTitle'];
                    unset($item['ItemTitle']);
                }
                $this->prepareInventoryItemData($item);
                if (is_array($this->settings['language'])) {
                    $iLanguageId = current($this->settings['language']);
                } else {
                    $iLanguageId = $this->settings['language'];
                }
                $pID = magnaSKU2pID($item['SKU']);
                $sTitle = (string)MagnaDB::gi()->fetchOne("
					SELECT products_name
					  FROM ".TABLE_PRODUCTS_DESCRIPTION."
					 WHERE     products_id = '".$pID."'
					       AND language_id = '".$iLanguageId."'
				");

                if (!empty($sTitle)) {
                    $item['Title'] = $sTitle;
                }

                $item['Category'] = renderCategoryPath($pID, 'product');
            }
            unset($result);
        }
    }

    protected function getInventory() {
        try {
            $request = array(
                'ACTION' => 'GetInventory',
                'LIMIT' => $this->settings['itemLimit'],
                'OFFSET' => $this->offset,
                'ORDERBY' => $this->sort['order'],
                'SORTORDER' => $this->sort['type'],
                'MODE' => 'Rejected'
            );
            if (!empty($this->search)) {
                #$request['SEARCH'] = (!magnalisterIsUTF8($this->search)) ? utf8_encode($this->search) : $this->search;
                $request['SEARCH'] = $this->search;
            }
            $result = MagnaConnector::gi()->submitRequest($request);
            $this->numberofitems = (int)$result['NUMBEROFLISTINGS'];
            return $result;

        } catch (MagnaException $e) {
            return false;
        }
    }

    protected function sortByType($type) {
        $this->url['view'] = 'rejected';
        $tmpURL = $this->url;
        if (!empty($this->search)) {
            $tmpURL['search'] = urlencode($this->search);
        }
        return '
			<span class="nowrap">
				<a href="'.toURL($tmpURL, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($tmpURL, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_IMAGES.'sort_down.png" />
				</a>
			</span>';
    }

    protected function getSortOpt() {
        if (isset($_GET['sorting'])) {
            $sorting = $_GET['sorting'];
        } else {
            $sorting = 'blabla'; // fallback for default
        }
        $sortFlags = array (
            'sku' => 'SKU',
            'title' => 'Title',
            'timestamp' => 'timestamp',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'dateadded' => 'DateAdded'
        );
        $order = 'ASC';
        if (strpos($sorting, '-desc') !== false) {
            $order = 'DESC';
            $sorting = str_replace('-desc', '', $sorting);
        }
        if (array_key_exists($sorting, $sortFlags)) {
            $this->sort['order'] = $sortFlags[$sorting];
            $this->sort['type']  = $order;
        } else {
            $this->sort['order'] = 'DateAdded';
            $this->sort['type']  = 'DESC';
        }
    }

    protected function getFields()
    {
        return array(
            'SKU' => array(
                'Label' => ML_LABEL_SKU,
                'Sorter' => null,
                'Getter' => null,
                'Field' => 'ArticleSKU'
            ),
            'Title' => array(
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => 'title',
                'Field' => 'Title',
            ),
            'Category' => array(
                'Label' => ML_LABEL_CATEGORY_PATH,
                'Sorter' => null,
                'Field' => 'Category'
            ),
            'Price' => array(
                'Label' => ML_GENERIC_OLD_PRICE,
                'Sorter' => null,
                'Getter' => 'getItemMarketplacePrice',
                'Field' => null
            ),
            'Reason' => array(
                'Label' => ML_GENERIC_REASON,
                'Sorter' => null,
                'Getter' => 'getRejectReason',
                'Field' => null
            ),
            'timestamp' => array(
                'Label' => ML_GENERIC_DELETEDDATE,
                'Sorter' => 'timestamp',
                'Getter' => 'getItemLastSyncTime',
                'Field' => null
            ),
        );
    }

    protected function getItemMarketplacePrice($item) {
        $item['Currency'] = isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency;
        $price = $item['Price'] + $item['ShippingCost'];
        return '<td>'.$this->simplePrice->setPriceAndCurrency($price, $item['Currency'])->format().'</td>';
    }

    protected function getRejectReason($item)
    {
        if (!isset($item['RejectReason']) || empty($item['RejectReason'])) {
            $item['RejectReason'] = ML_FYNDIQ_INVENTORY_REJECT_MESSAGE;
        }

        return '<td>'.fixHTMLUTF8Entities($item['RejectReason'], ENT_COMPAT).'</td>';
    }

    protected function getItemLastSyncTime($item)
    {
        if ($item['LastSync'] == null) {
            return '<td>-</td>';
        }

        $item['LastSync'] = strtotime($item['LastSync']);
        return '<td>' . date("d.m.Y", $item['LastSync']) . ' &nbsp;&nbsp;<span class="small">' . date("H:i", $item['LastSync']) . '</span>' . '</td>';
    }

    protected function postDelete()
    {
        MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'UploadItems'
        ));
    }
}
