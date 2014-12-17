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
 * (c) 2011 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_MODULES.'magnacompatible/errorlog/MagnaCompatibleErrorView.php');

class EbayErrorView extends MagnaCompatibleErrorView {
	public function __construct($settings = array()) {
		$settings = array_merge(array(
			'hasImport' => true,
			'hasOrigin' => true,
		), $settings);
		
		parent::__construct($settings);
	}

	public function renderActionBox() {
		$left = '<input type="button" class="ml-button ml-js-deleteBtn" value="'.ML_BUTTON_LABEL_DELETE.'" name="delete"/>';
		$right = '&nbsp;';

		ob_start();?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('.ml-js-deleteBtn').click(function() {
		var btnAction = $(this).attr('name');
		if (($('#errorlog input[type="checkbox"]:checked').length > 0)
			&& confirm(unescape(<?php echo "'".html2url(ML_GENERIC_DELETE_ERROR_MESSAGES)."'"; ?>))
		) {
			$('#action').val(btnAction);
			$(this).parents('form').submit();
		}
	});
});
/*]]>*/</script>
<?php // Durch aufrufen der Seite wird automatisch ein Aktualisierungsauftrag gestartet
		$js = ob_get_contents();	
		ob_end_clean();

		return '
			<input type="hidden" id="action" name="action" value="">
			<input type="hidden" name="timestamp" value="'.time().'">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody><tr><td>
					<table><tbody><tr>
						<td class="firstChild">'.$left.'</td>
						<td class="lastChild">'.$right.'</td>
					</tr></tbody></table>
				</td></tr></tbody>
			</table>
			'.$js;
	}
}

$_magnaQuery['mode'] = $_url['mode'] = $_GET['mode'];

$el = new EbayErrorView();
echo $el->renderView();
