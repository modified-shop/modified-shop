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
 * $Id: $
 *
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES . 'magnacompatible/checkin/MagnaCompatibleSummaryView.php');
require_once(DIR_MAGNALISTER_MODULES . 'crowdfox/CrowdfoxHelper.php');

class CrowdfoxSummaryView extends MagnaCompatibleSummaryView
{
    public function __construct($settings = array())
    {
        parent::__construct($settings);
    }

    protected function setupQuery($addFields = '', $addFrom = '', $addWhere = '')
    {
        $addFields .= (empty($addFields) ? '' : ',') . '';
        $addFrom = 'LEFT JOIN ' . TABLE_MAGNA_CROWDFOX_PREPARE . ' hp ON (
							hp.mpID=\'' . $this->mpID . '\' 
							AND hp.products_id=p.products_id
					  )
                      ' . $addFrom;
        parent::setupQuery($addFields, $addFrom, $addWhere);
    }

    protected function processAdditionalPost()
    {
        parent::processAdditionalPost();
        if ($this->isAjax){
            if (!isset($_POST['productID'])){
                return;
            }
            $pID = $this->ajaxReply['pID'] = substr($_POST['productID'], strpos($_POST['productID'], '_') + 1);
            if (!array_key_exists($pID, $this->selection)){
                $this->loadItemToSelection($pID);
            }
            $this->extendProductAttributes($pID, $this->selection[$pID]);

        }
    }

}
