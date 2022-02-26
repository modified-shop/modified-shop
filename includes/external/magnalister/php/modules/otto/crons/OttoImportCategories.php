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

require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/crons/MagnaCompatibleCronBase.php');

class OttoImportCategories extends MagnaCompatibleCronBase {

    const iExpiresLiveTime = 86400;

    /**
     * @var int[]
     */
    protected $aRequestTimeouts = array(
        'iGetCategoriesTimeout' => 0,
    );

    /**
     * @var int
     */
    protected $iImportCategoriesLimit = 3000;


    protected function getConfigKeys() {
        $keys = array();
        return $keys;
    }

    protected function getGetCategoriesRequest() {
        $aRequest = array(
            'ACTION' => 'GetCategories',
            'MODE' => 'GetCategories',
            'SUBSYSTEM' => $this->marketplace,
            'MARKETPLACEID' => $this->mpID,
            'OFFSET' => 0,
            'LIMIT' => $this->iImportCategoriesLimit,
        );
        if (isset($_REQUEST['steps']) && (int)$_REQUEST['steps'] > 0) {
            $aRequest['steps'] = (int)$_REQUEST['steps'];
        }

        return $aRequest;
    }


    public function process() {
        $timestamp = date('Y-m-d H:i:s', time() + self::iExpiresLiveTime);

        $aRequest = $this->getGetCategoriesRequest();
        $this->out("\nFetchCategories {\n");
        try {
            do {
                $dCategory = array();

                $aResponse = MagnaConnector::gi()->submitRequest($aRequest);
                $this->log(
                    'Received '.count($aResponse['DATA']).' categories '.
                    '('.($aRequest['OFFSET'] + count($aResponse['DATA'])).' of '.$aResponse['NUMBEROFLISTINGS'].') '.
                    'in  '.microtime2human($aResponse['Client']['Time'])."\n"
                );
                $aResponse['DATA'] = empty($aResponse['DATA']) ? array() : $aResponse['DATA'];

                //prepare data for insert in database
                foreach ($aResponse['DATA'] as $rCategory) {
                    $dCategory[] = array(
                        'CategoryID' => $rCategory['CategoryID'],
                        'CategoryName' => $rCategory['CategoryName'],
                        'ParentID' => $rCategory['ParentID'],
                        'LeafCategory' => $rCategory['LeafCategory'],
                        'Expires' => $timestamp,
                    );
                }
                //Insert categories in the database
                $this->log('InsertCategories'."\n\n".'Inserted  '.count($dCategory).' categories ');
                $chunks = array_chunk($dCategory, 100);
                foreach ($chunks as $chunk) {
                   MagnaDB::gi()->batchinsert(TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE, $chunk);
                }

                $aRequest['OFFSET'] += $aRequest['LIMIT'];
                if (isset($aRequest['steps'])) {
                    $aRequest['steps']--;
                }

                if ($aRequest['OFFSET'] < $aResponse['NUMBEROFLISTINGS']) {
                    $this->out(array(
                        'Finished' => false,
                        'Done' => (int)$aRequest['OFFSET'],
                        'Step' => isset($aRequest['steps']) ? $aRequest['steps'] : false,
                        'Total' => $aResponse['NUMBEROFLISTINGS'],
                    ));
                    $blNext = true;
                } else {
                    $this->out(array(
                        'Finished' => true,
                        'Done' => (int)$aRequest['OFFSET'],
                        'Step' => isset($aRequest['steps']) ? $aRequest['steps'] : false,
                        'Total' => $aResponse['NUMBEROFLISTINGS'],
                    ));
                    $blNext = false;
                }
                if (isset($aRequest['steps']) && $aRequest['steps'] <= 1) {
                    $this->out(array(
                        'Finished' => true,
                        'Done' => (int)$aRequest['OFFSET'],
                        'Step' => isset($aRequest['steps']) ? $aRequest['steps'] : false,
                        'Total' => $aResponse['NUMBEROFLISTINGS'],
                    ));
                    $blNext = false;
                }
            } while ($blNext);

            // delete old categories from the database
            $this->log("\n".'RemoveOldCategories'."\n\n");
            MagnaDB::gi()->query("DELETE FROM `".TABLE_MAGNA_OTTO_CATEGORIES_MARKETPLACE."`  WHERE `Expires` <> '".$timestamp ."'");

        } catch (\Exception $oEx) {
            $this->log($oEx->getMessage());
        }
    }

}
