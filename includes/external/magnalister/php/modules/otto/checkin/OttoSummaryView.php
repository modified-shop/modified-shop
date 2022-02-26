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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/checkin/MagnaCompatibleSummaryView.php');
require_once(DIR_MAGNALISTER_MODULES.'otto/OttoHelper.php');

class OttoSummaryView extends MagnaCompatibleSummaryView {

    protected function getAdditionalHeadlines() {
        return str_replace(ML_MAGNACOMPAT_LABEL_MP_PRICE_SHORT, ML_OTTO_PRICE_FOR_OTTO,
            parent::getAdditionalHeadlines());
    }

    protected function getAdditionalItemCells($key, $dbRow) {
        $this->extendProductAttributes($dbRow['products_id'], $this->selection[$dbRow['products_id']]);

        return '
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>
						'.$this->simplePrice->setPrice($this->selection[$dbRow['products_id']]['price'])->format()
            .'
							<input type="hidden" id="backup_price_'.$dbRow['products_id'].'"
						           value="'.$this->simplePrice->getPrice().'"/>
						</td></tr>
				    	<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>&nbsp;'.(
            array_key_exists($dbRow['products_id'], $this->inventoryData) ?
                /* Waehrung von Preis nicht umrechnen, da bereits in Zielwaehrung. */
                $this->simplePrice->setPrice($this->inventoryData[$dbRow['products_id']]['Price'])->formatWOCurrency() :
                '&mdash;'
            ).'</td></tr>
				    </tbody></table>
				</td>
				<td>'.(int)$dbRow['products_quantity'].'</td>
				
				<td><table class="nostyle"><tbody>
						<tr><td>'.ML_LABEL_NEW.':&nbsp;</td><td>
							<input type="hidden" id="old_quantity_'.$dbRow['products_id'].'"
						           value="'.$this->selection[$dbRow['products_id']]['quantity'].'"/>
						    '.$this->selection[$dbRow['products_id']]['quantity'].'
						</td></tr>
				    	<tr><td>'.ML_LABEL_OLD.':&nbsp;</td><td>&nbsp;'.(
            array_key_exists($dbRow['products_id'], $this->inventoryData) ?
                $this->inventoryData[$dbRow['products_id']]['Quantity'] :
                '&mdash;'
            ).'</td></tr>
				    </tbody></table>
				</td>';
    }

    protected function extendProductAttributes($pID, &$data) {
        global $_MagnaSession;
        parent::extendProductAttributes($pID, $data);
        $iMaxQuantity = getDBConfigValue('otto.maxquantity', $_MagnaSession['mpID'], 0);
        if (($iMaxQuantity > 0)
            && ($iMaxQuantity < $data['quantity'])) {
            $data['quantity'] = $iMaxQuantity;
        }
    }
}
