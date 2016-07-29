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

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class FyndiqInventoryView extends MagnaCompatibleInventoryView
{
    protected function prepareInventoryItemData(&$item)
    {
        $item['MarketplaceTitle'] = $item['ArticleName'];
        $item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
            ? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')) . '&hellip;')
            : fixHTMLUTF8Entities($item['MarketplaceTitle']);
    }

    protected function getFields()
    {
        return array(
            'SKU' => array(
                'Label' => ML_LABEL_SKU,
                'Sorter' => 'sku',
                'Getter' => 'getSKU',
                'Field' => null
            ),
            'Title' => array(
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => 'title',
                'Getter' => 'getTitle',
                'Field' => null,
            ),
            'MarketplaceTitle' => array(
                'Label' => ML_LABEL_TITLE,
                'Sorter' => null,
                'Getter' => 'getMarketplaceTitle',
                'Field' => null,
            ),
            'Price' => array(
                'Label' => ML_GENERIC_PRICE,
                'Sorter' => 'price',
                'Getter' => 'getItemPrice',
                'Field' => null
            ),
            'MarketplacePrice' => array(
                'Label' => ML_FYNDIQ_MARKETPLACE_PREIS,
                'Sorter' => 'price',
                'Getter' => 'getItemMarketplacePrice',
                'Field' => null
            ),
            'Quantity' => array(
                'Label' => ML_LABEL_QUANTITY,
                'Sorter' => 'quantity',
                'Getter' => 'getQuantities',
                'Field' => null,
            ),
            'LastModified' => array(
                'Label' => 'Letzte Synchronisation',
                'Sorter' => 'lastmodified',
                'Getter' => 'getLastModified',
                'Field' => null
            ),
            'Status' => array(
                'Label' => ML_HITMEISTER_INVENTORY_STATUS,
                'Sorter' => 'status',
                'Getter' => 'getStatus',
                'Field' => null
            )
        );
    }

    protected function getMarketplaceTitle($item)
    {
        return '<td title="' . fixHTMLUTF8Entities($item['MarketplaceTitle'], ENT_COMPAT) . '">' . $item['MarketplaceTitleShort'] . '</td>';
    }

    protected function getSKU($item)
    {
        return '<td>' . $item['ArticleSKU'] . '</td>';
    }

    protected function getLastModified($item)
    {
        $item['LastSync'] = strtotime($item['LastSync']);
        if ($item['LastSync'] < 0) {
            return '<td>-</td>';
        }
        return '<td>' . date("d.m.Y", $item['LastSync']) . ' &nbsp;&nbsp;<span class="small">' . date("H:i", $item['LastSync']) . '</span>' . '</td>';
    }

    protected function getQuantities($item)
    {
        if (getDBConfigValue('general.keytype', '0') == 'artNr') {
            $where = 'sku';
        } else {
            $where = 'id';
        }

        $shopQuantity = (int)MagnaDB::gi()->fetchOne("
			SELECT variation_quantity
			FROM " . TABLE_PRODUCTS_VARIATIONS . "
			WHERE marketplace_" . $where . " = '" . $item['ArticleSKU'] . "'
		");

        if (!$shopQuantity) {
            $shopQuantity = (int)MagnaDB::gi()->fetchOne("
				SELECT products_quantity
				  FROM " . TABLE_PRODUCTS . "
				 WHERE products_id = '" . magnaSKU2pID($item['ArticleSKU']) . "'
			");
        }

        if (!$shopQuantity) {
            $shopQuantity = '-';
        }

        return '<td>' . $shopQuantity . ' / ' . $item['Quantity'] . '</td>';
    }

    protected function getStatus($item)
    {
        if (isset($item['Status']) === false) {
            $status = '-';
        } else {
            $status = $item['Status'];
        }

        return '<td>' . $status . '</td>';
    }

    protected function getItemMarketplacePrice($item) {
        $item['Currency'] = isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency;
        $price = $item['Price'] + $item['ShippingCost'];
        return '<td>'.$this->simplePrice->setPriceAndCurrency($price, $item['Currency'])->format().'</td>';
    }

    protected function postDelete()
    {
        MagnaConnector::gi()->submitRequest(array(
            'ACTION' => 'UploadItems'
        ));
    }

    protected function renderDataGrid($id = '')
    {
        global $magnaConfig;

        $html = '
			<table' . (($id != '') ? ' id="' . $id . '"' : '') . ' class="datagrid">
				<thead class="small"><tr>
					<td class="nowrap" style="width: 5px;">
						<input type="checkbox" id="selectAll"/><label for="selectAll">' . ML_LABEL_CHOICE . '</label>
					</td>';
        $fieldsDesc = $this->getFields();
        foreach ($fieldsDesc as $fdesc) {
            $html .= '
					<td>' . $fdesc['Label'] . ((isset($fdesc['Sorter']) && ($fdesc['Sorter'] != null)) ? ' ' . $this->sortByType($fdesc['Sorter']) : '') . '</td>';
        }
        $html .= '
				</tr></thead>
				<tbody>
		';
        $oddEven = false;
        foreach ($this->renderableData as $item) {
            $details = htmlspecialchars(str_replace('"', '\\"', serialize(array(
                'SKU' => $item['SKU'],
                'Price' => $item['Price'],
                'Currency' => isset($item['Currency']) ? $item['Currency'] : $this->mpCurrency,
            ))));
            $html .= '
				<tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
					<td><input type="checkbox" name="SKUs[]" value="' . $item['ArticleSKU'] . '">
						<input type="hidden" name="details[' . $item['ArticleSKU'] . ']" value="' . $details . '"></td>';
            foreach ($fieldsDesc as $fdesc) {
                if ($fdesc['Field'] != null) {
                    $html .= '
					<td>' . $item[$fdesc['Field']] . '</td>';

                } else {
                    $html .= '
					' . call_user_func(array($this, $fdesc['Getter']), $item);
                }
            }
            $html .= '
				</tr>';
        }
        $html .= '
				</tbody>
			</table>';

        return $html;
    }
}
