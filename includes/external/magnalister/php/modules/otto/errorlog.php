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
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog.php');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog/MagnaCompatibleErrorView.php');

class OttoErrorLog extends MagnaCompatibleErrorLog {
	public function process() {
		$el = new OttoErrorView();
		echo $el->renderView();
	}
}
class OttoErrorView extends MagnaCompatibleErrorView {
    protected $blRecommendationColumn = true;
	public function __construct($settings = array()) {
		$settings = array_merge(array(
			'hasImport' => true,
			'hasOrigin' => true,
		), $settings);
		
		parent::__construct($settings);
	}

    public function renderView() {
        $html = '';

        if (empty($this->errorLog)) {
            return '<table class="magnaframe"><tbody><tr><td>'.ML_GENERIC_NO_ERRORS_YET.'</td></tr></tbody></table>';
        }

        $tmpURL = $this->url;
        if (isset($_GET['sorting'])) {
            $tmpURL['sorting'] = $_GET['sorting'];
        }

        $html .= '
            <form action="'.toURL($this->url).'" method="POST">
                <table class="listingInfo"><tbody><tr>
                    <td class="ml-pagination">
                        <span class="bold">'.ML_LABEL_CURRENT_PAGE.' &nbsp;&nbsp; '.$this->currentPage.'</span>
                    </td>
                    <td class="textright">
                        '.renderPagination($this->currentPage, $this->pages, $tmpURL).'
                    </td>
                </tr></tbody></table>
                <table class="datagrid" id="errorlog">
                    <thead><tr>
                        <td class="nowrap" style="width: 5px;"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
                        '.($this->settings['hasBatchId'] ? '<td>'.ML_AMAZON_LABEL_BATCHID.'</td>' : '').'
                        <td>'.ML_AMAZON_LABEL_ADDITIONAL_DATA.'</td>
                        <td>'.ML_OTTO_ERROR_CODE.'</td>
                        <td>'.ML_GENERIC_ERROR_MESSAGES.'&nbsp;'.$this->sortByType('errormessage').'</td>
                        <td>'.ML_GENERIC_LABEL_ADDITIONAL_HELP.'</td>
                        '.($this->settings['hasOrigin'] ? '<td>'.ML_GENERIC_LABEL_ORIGIN.'</td>' : '').'
                        <td>'.ML_GENERIC_COMMISSIONDATE.'&nbsp;'.$this->sortByType('commissiondate').'</td>
                    </tr></thead>
                    <tbody>';

        $oddEven = false;
        foreach ($this->errorLog as $item) {
            $dateadded = strtotime($item['dateadded']);
            $hdate = date("d.m.Y", $dateadded).' &nbsp;&nbsp;<span class="small">'.date("H:i", $dateadded).'</span>';
            $message = $this->processErrorMessage($item);
            $recommendation = $this->processErrorRecommendation($item);

            // Calculate your custom column value here
            $customColumnValue = $this->getErrorCodeValue($item);

            $html .= '
                <tr class="' . (($oddEven = !$oddEven) ? 'odd' : 'even') . '">
                    <td><input type="checkbox" name="errIDs[]" value="' . $item['id'] . '"></td>';

            if ($this->settings['hasBatchId']) {
                if (!empty($item['BatchId'])) {
                    $html .= '<td>' . $item['BatchId'] . '</td>';
                } else {
                    $html .= '<td>&nbsp;&nbsp;&mdash;</td>';
                }
            }

            $html .= '
                    <td class="nopadding" style="width: 1px">' . $this->additionalDataHandler($item['additionaldata']) . '</td>
                    <td>' . $customColumnValue . '</td>
                    <td class="errormessage">' . $message['short'] . '<span>' . $message['long'] . '</span></td>
                    <td class="errorrecommendation">' . $recommendation['short'] . '<span style="display:none;">' . $recommendation['long'] . '</span></td>
                    ' . ($this->settings['hasOrigin'] ? '<td>' . $item['origin'] . '</td>' : '') . '
                    <td>' . $hdate . '</td>
                </tr>';
        }

        $html .= '
                    </tbody>
                </table>
                <div id="errordetails" class="dialog2" title="'.ML_GENERIC_ERROR_DETAILS.'"></div>
                <div id="recommendationdetails" class="dialog2" title="'.ML_GENERIC_ERROR_RECOMMENDATIONS.'"></div>';

        ob_start(); ?>
        <script type="text/javascript">/*<![CDATA[*/
            $(document).ready(function() {
                $('table#errorlog tbody td.errormessage').click(function() {
                    $('#errordetails').html($('span', this).html()).jDialog();
                });

                $('table#errorlog tbody td.errorrecommendation').click(function() {
                    $('#recommendationdetails').html($('span', this).html()).jDialog();
                });

                $('#selectAll').click(function() {
                    state = $(this).attr('checked');
                    $('#errorlog input[type="checkbox"]:not([disabled])').each(function() {
                        $(this).attr('checked', state);
                    });
                });
            });
            /*]]>*/</script>
        <?php
        $html .= ob_get_contents();
        ob_end_clean();

        $html .= $this->renderActionBox().'
            </form>';

        return $html;
    }

    /**
     * Get error code column value for the new column
     * @param array $item Error log item
     * @return string
     */
    protected function getErrorCodeValue($item) {
        // Add your custom logic here
        // For example, if you want to show error level:
        if (!empty($item['ErrorCode']) && !$this->isUnixTimestamp($item['ErrorCode'])) {
            return htmlspecialchars($item['ErrorCode']);
        }
        return '&nbsp;&nbsp;&mdash;';
    }

    protected function isUnixTimestamp($str) {
        return ctype_digit($str) && ($str <= PHP_INT_MAX) && ($str >= 0);
    }
}
