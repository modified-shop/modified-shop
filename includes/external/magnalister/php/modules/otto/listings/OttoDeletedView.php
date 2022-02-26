<?php
/*
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
 * (c) 2010 - 2021 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/listings/MagnaCompatibleInventoryView.php');

class OttoDeletedView extends MagnaCompatibleInventoryView {

    public function __construct() {
        parent::__construct();
        $this->url['view'] = 'deleted';
        $this->additionalParameters['ONLY_DELETED'] = true;
    }

    protected function getFields() {
        return array(
            'SKU' => array (
                'Label' => ML_LABEL_SKU,
                'Sorter' => 'sku',
                'Getter' => null,
                'Field' => 'SKU'
            ),
            'Title' => array (
                'Label' => ML_LABEL_SHOP_TITLE,
                'Sorter' => null,
                'Getter' => 'getTitle',
                'Field' => null,
            ),
            'Category' => array (
                'Label' => ML_LABEL_CATEGORY_PATH,
                'Sorter' => null,
                'Getter' => 'getCategory',
                'Field' => null,
            ),
            'Price' => array (
                'Label' => ML_GENERIC_PRICE,
                'Sorter' => null,
                'Getter' => 'getItemPrice',
                'Field' => null
            ),
            'DateAdded' => array (
                'Label' => ML_GENERIC_DELETEDDATE,
                'Sorter' => 'dateupdated',
                'Getter' => 'getItemDateUpdated',
                'Field' => null
            ),
        );
    }

    protected function getCategory($item) {
        return '<td><ul><li>'.str_replace('<br>', '</li><li>', renderCategoryPath(unserialize($item['ProductData'])['CategoryID'], 'category')).'</li></ul></td>';
    }

    protected function getTitle($item) {
        return '<td>'.(empty($item['TitleShort']) ? unserialize($item['ProductData'])['Title'] : $item['TitleShort']).'</td>';
    }

    protected function getItemDateUpdated($item) {
        return '<td>'.date("d.m.Y", strtotime($item['DateUpdated'])).' &nbsp;&nbsp;<span class="small">'.date('H:i:s', strtotime($item['DateUpdated'])).'</span>'.'</td>';
    }

    protected function renderDataGrid($id = '') {
        global $magnaConfig;

        $html = '<table class="magnaframe">
				<thead><tr><th>'.ML_LABEL_NOTE.'</th></tr></thead>
				<tbody><tr><td class="fullWidth">
					<table><tbody>
						<tr><td>'.ML_OTTO_DELETED_OFFER_PURGE_INFO.'
							</td>
						</tbody></table>
				</td></tr></tbody>
			</table>';
        $html .= '
			<table'.(($id != '') ? ' id="'.$id.'"' : '').' class="datagrid">
				<thead class="small"><tr>';
        $fieldsDesc = $this->getFields();
        foreach ($fieldsDesc as $fdesc) {
            $html .= '
					<td>'.$fdesc['Label'].((isset($fdesc['Sorter']) && ($fdesc['Sorter'] != null)) ? ' '.$this->sortByType($fdesc['Sorter']) : '').'</td>';
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

            $addStyle = ($item['Title'] === '&mdash;' && $item['SKU'] !== '&mdash;') ? 'style="color:#900;"' : '';

            $html .= '
				<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'" ' . $addStyle . '>
					';
            foreach ($fieldsDesc as $fdesc) {
                if ($fdesc['Field'] != null) {
                    $html .= '
					<td>'.$item[$fdesc['Field']].'</td>';

                } else {
                    $html .= '
					'.call_user_func(array($this, $fdesc['Getter']), $item);
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

    public function renderActionBox() {

        return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild"></td>
						<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
							<input id="tfSearch" name="tfSearch" type="text" value="'.fixHTMLUTF8Entities($this->search, ENT_COMPAT).'"/>
							<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" name="search_go" /></td>
						<td class="lastChild"></td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			';
    }

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
                $pID = magnaSKU2pID($item['SKU']);
                if (is_array($this->settings['language'])) {
                    $iLanguageId = current($this->settings['language']);
                } else {
                    $iLanguageId = $this->settings['language'];
                }

                $item['MarketplaceTitle'] = unserialize($item['ProductData'])['Title'];
                $item['MarketplaceTitleShort'] = (mb_strlen($item['MarketplaceTitle'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
                    ? (fixHTMLUTF8Entities(mb_substr($item['MarketplaceTitle'], 0, $this->settings['maxTitleChars'], 'UTF-8')) . '&hellip;')
                    : fixHTMLUTF8Entities($item['MarketplaceTitle']);

                $sTitle = (string)MagnaDB::gi()->fetchOne("
					SELECT products_name 
					  FROM ".TABLE_PRODUCTS_DESCRIPTION."
					 WHERE     products_id = '".$pID."'
					       AND language_id = '".$iLanguageId."'
				");
                $item['Title'] = '&mdash;';
                if (!empty($sTitle)) {
                    $item['Title'] = $sTitle;
                }
                $item['TitleShort'] = (mb_strlen($item['Title'], 'UTF-8') > $this->settings['maxTitleChars'] + 2)
                    ? (fixHTMLUTF8Entities(mb_substr($item['Title'], 0, $this->settings['maxTitleChars'], 'UTF-8')).'&hellip;')
                    : fixHTMLUTF8Entities($item['Title']);
            }
            unset($result);
        }

    }
}
