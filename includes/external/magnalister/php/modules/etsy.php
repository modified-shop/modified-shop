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
 * (c) 2010 - 2025 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

require_once('magnacompatible.php');

class EtsyMarketplace extends MagnaCompatMarketplace {

    protected function extraChecks() {
        parent::extraChecks();

        // Handle AJAX request to dismiss notice
        if (isset($_GET['kind']) && $_GET['kind'] == 'ajax' && isset($_GET['request']) && $_GET['request'] == 'dismissEtsyNotice') {
            $this->dismissEtsyNotice();
        }

        // Check if Etsy Processing Profile notice has been dismissed
        $noticeKey = 'etsy.processing_profile_notice_dismissed';
        $noticeDismissed = getDBConfigValue($noticeKey, $this->mpID, false);

        if (!$noticeDismissed) {
            global $_url;

            $noticeHtml = '
                <div id="etsyProcessingProfileNotice" class="noticeBox" style="position: relative; padding-right: 50px;">
                    <button type="button" id="dismissEtsyNotice" style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; cursor: pointer; background: #fff; border: 1px solid #ccc; border-radius: 3px;">
                        '.ML_BUTTON_LABEL_CLOSE.'
                    </button>
                    '.ML_ETSY_PROCESSING_PROFILE_NOTICE_HTML.'
                </div>
                <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#dismissEtsyNotice").on("click", function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        // Send dismissal request
                        $.ajax({
                            url: "'.toURL($_url, array('kind' => 'ajax', 'request' => 'dismissEtsyNotice'), true).'",
                            type: "POST",
                            data: { mpID: '.$this->mpID.' },
                            dataType: "json",
                            global: false,
                            success: function(response) {
                                // Fade out and remove notice after successful save
                                $("#etsyProcessingProfileNotice").fadeOut(300, function() {
                                    $(this).remove();
                                });
                            },
                            error: function(xhr, status, error) {
                                // Show error message
                                alert("'.ML_ERROR_SAVE.': " + (error || status));
                            }
                        });

                        return false;
                    });
                });
                </script>
            ';

            $this->resources['query']['messages'][] = $noticeHtml;
        }
    }

    protected function dismissEtsyNotice() {
        if (isset($_POST['mpID'])) {
            $mpID = (int)$_POST['mpID'];
            $noticeKey = 'etsy.processing_profile_notice_dismissed';
            setDBConfigValue($noticeKey, $mpID, true, true);
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'mpID missing'));
        }
        exit();
    }
}

new EtsyMarketplace($_MagnaSession['currentPlatform']);
