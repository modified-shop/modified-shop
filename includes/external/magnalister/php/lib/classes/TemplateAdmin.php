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
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class TemplateAdmin {
	private $_magnasession;
	
	public function __construct() {
		global $_MagnaSession, $_url;
		$_url['view'] = 'administrate';

		$this->_magnasession = $_MagnaSession;
		
		if (array_key_exists('removeItems', $_POST) && is_array($_POST['removeItems'])) {
			$tplIDs = array_keys($_POST['removeItems']);
			foreach ($tplIDs as $tID) {
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES, array('tID' => $tID));
				MagnaDB::gi()->delete(TABLE_MAGNA_SELECTION_TEMPLATES, array('tID' => $tID));
			}
		}
	}

	public function renderTemplateList() {
		global $_url;

		$backURL = $_url;
		unset($backURL['view']);

		$templates = MagnaDB::gi()->fetchArray('
		    SELECT mlst.tID, title, data
		      FROM `'.TABLE_MAGNA_SELECTION_TEMPLATES.'` mlst
		     WHERE mpID=\''.$this->_magnasession['mpID'].'\'
		');
		if (empty($templates)) {
			return '
				<p class="noticeBox">'.ML_LABEL_NO_TEMPLATES_YET.'</p>
				<table class="actions"><thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead><tbody><tr><td>
					<table><tbody><tr><td>
						<a class="ml-button" href="'.toURL($backURL).'" title="'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'">'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'</a>
					</td><td>&nbsp;</td></tr></tbody></table>
				</td></tr></tbody></table>
			';
		}

		$html = '<form action="'.toURL($_url).'" method="POST" id="templateAdmin">
			<input type="hidden" name="delTmplID" value=""/>
			<table class="datagrid"><thead>
				<tr>
					<td class="smallCell"><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
					<td>'.ML_LABEL_LABEL.'</td>
					<td>'.ML_LABEL_AMOUNT_PRODUCTS.'</td>
					<td class="smallCell">'.ML_LABEL_EDIT.'</td>
				</tr>
			</thead>
			<tbody>';
		$isOdd = true;
		foreach ($templates as $template) {
			$template['items'] = MagnaDB::gi()->fetchOne('
			    SELECT COUNT(pID)
			      FROM `'.TABLE_MAGNA_SELECTION_TEMPLATE_ENTRIES.'`
			     WHERE tID='.$template['tID'].'
			  GROUP BY tID
			');

			$html .= '
				<tr class="'.(($isOdd = !$isOdd) ? 'odd' : 'even').'">
					<td><input type="checkbox" name="removeItems['.$template['tID'].']" value="true"/></td>
					<td>'.$template['title'].'</td>
					<td>'.$template['items'].'</td>
					<td>
						<input type="submit" class="gfxbutton edit" name="edit['.$template['tID'].']" value="'.ML_LABEL_EDIT.'" title="'.ML_LABEL_EDIT.'"/>
					</td>
				</tr>
			';
		}

		$html .= '
			</tbody></table>
			<table class="actions"><thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead><tbody><tr><td>
				<table><tbody><tr><td class="firstChild">
					<a class="ml-button" href="'.toURL($backURL).'" title="'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'">'.ML_BUTTON_LABEL_BACK_TO_CHECKIN.'</a>
				</td><td class="lastChild">
					<button class="ml-button" type="button" id="removeSelected" value="remove" name="removeSelected" title="'.ML_BUTTON_LABEL_DELETE.'">
						<img src="'.DIR_MAGNALISTER_WS_IMAGES.'cross.png" alt="'.ML_BUTTON_LABEL_DELETE.'"/> '.ML_BUTTON_LABEL_DELETE.'
					</button>				
				</td></tr></tbody></table>
			</td></tr></tbody></table>
		</form>
		';
		ob_start();
?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {
	$('button#removeSelected').click(function() {
		$('#templateAdmin input[type="checkbox"]').each(function () {
			if ($(this).attr('checked')) {
				$('#templateAdmin').submit();
			}
		});
	});
	$('#selectAll').click(function() {
		state = $(this).attr('checked');
		$('#templateAdmin input[type="checkbox"]').each(function () {
			$(this).attr('checked', state);
		});
	});
});
/*]]>*/</script>
<?php
		$html .= ob_get_contents();
		ob_end_clean();
		return $html;
	}
}