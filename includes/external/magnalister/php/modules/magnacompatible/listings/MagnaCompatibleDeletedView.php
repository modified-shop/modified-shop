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
 * (c) 2011 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once (DIR_MAGNALISTER_INCLUDES.'lib/classes/SimplePrice.php');

class MagnaCompatibleDeletedView {
	private $settings = array();
	private $sort = array();

	private $renderableData = array();
		
	private $simplePrice = null;
	private $url = array();
	private $magnaSession = array();
	private $magnaShopSession = array();
	
	private $numberofitems = 0;
	
	private $deletedLog = array();


	public function __construct($settings = array()) {
		global $_MagnaShopSession, $_MagnaSession, $_url, $_modules;
		
		$this->settings = array_merge(array(
			'maxTitleChars'	=> 40,
			'itemLimit' => 50
		), $settings);
		
		$this->simplePrice = new SimplePrice();
		$this->simplePrice->setCurrency(getCurrencyFromMarketplace($_MagnaSession['mpID']));
		$this->url = $_url;
		$this->url['view'] = 'deleted';
		$this->magnasession = $_MagnaSession;
		$this->magnaShopSession = $_MagnaShopSession;

		/* Delete Log */
		if (isset($_POST['action'])) {
            $arrayKeys = array_keys($_POST['action']);
			$action = array_pop($arrayKeys);
			switch ($action) {
				case 'delete': {
					if (!isset($_POST['delIDs']) || empty($_POST['delIDs'])) {
						break;
					}
					$ids = array();
					foreach ($_POST['delIDs'] as $id) {
						$ids[] = (int)$id;
					}
					$ids = array_unique($ids);
					MagnaDB::gi()->query('
						DELETE FROM '.TABLE_MAGNA_COMPAT_DELETEDLOG.'
						 WHERE id IN (\''.implode('\', \'', $ids).'\')
					');
				}
			}
		}
		
		if (isset($_GET['sorting'])) {
			$sorting = $_GET['sorting'];
		} else {
			$sorting = 'blabla'; // fallback for default
		}

		switch ($sorting) {
			case 'itemname':
				$this->sort['order'] = 'itemname';
				$this->sort['type']  = 'ASC';
				break;
			case 'itemname-desc':
				$this->sort['order'] = 'itemname';
				$this->sort['type']  = 'DESC';
				break;
			case 'date':
				$this->sort['order'] = 'date';
				$this->sort['type']  = 'ASC';
				break;
			case 'date-desc':
			default:
				$this->sort['order'] = 'date';
				$this->sort['type']  = 'DESC';
				break;
		}

		$this->numberofitems = (int)MagnaDB::gi()->fetchOne(
			'SELECT DISTINCT count(id) FROM '.TABLE_MAGNA_COMPAT_DELETEDLOG.' WHERE `mpID`=\''.$this->magnasession['mpID'].'\''
		);
		$this->pages = ceil($this->numberofitems / $this->settings['itemLimit']);
		$this->currentPage = 1;

		if (isset($_GET['page']) && ctype_digit($_GET['page']) && (1 <= (int)$_GET['page']) && ((int)$_GET['page'] <= $this->pages)) {
			$this->currentPage = (int)$_GET['page'];
		}

		$this->offset = ($this->currentPage - 1) * $this->settings['itemLimit'];
		$artNr = getDBConfigValue('general.keytype', '0') == 'artNr';
		$this->deletedLog = MagnaDB::gi()->fetchArray('
			   SELECT csd.id, '.($artNr ? 'p.products_id' : 'csd.products_id').', csd.products_model as sku, csd.old_price,
			          csd.timestamp AS date, pd.products_name AS itemname 
			     FROM '.TABLE_MAGNA_COMPAT_DELETEDLOG.' csd
			LEFT JOIN '.TABLE_PRODUCTS.' p ON '.($artNr
				? 'csd.products_model=p.products_model'
				: 'csd.products_id=p.products_id'
			).'
			LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON p.products_id=pd.products_id AND pd.language_id = \''.$_SESSION['languages_id'].'\'
			    WHERE mpID = \''.$this->magnasession['mpID'].'\'
			 ORDER BY `'.$this->sort['order'].'` '.$this->sort['type'].' 
			    LIMIT '.$this->offset.','.$this->settings['itemLimit'].'
		');
	}

	private function sortByType($type) {
		return '
			<span class="nowrap">
				<a href="'.toURL($this->url, array('sorting' => $type.'')).'" title="'.ML_LABEL_SORT_ASCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_ASCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_up.png" />
				</a>
				<a href="'.toURL($this->url, array('sorting' => $type.'-desc')).'" title="'.ML_LABEL_SORT_DESCENDING.'" class="sorting">
					<img alt="'.ML_LABEL_SORT_DESCENDING.'" src="'.DIR_MAGNALISTER_WS_IMAGES.'sort_down.png" />
				</a>
			</span>';
	}

	public function renderActionBox() {
		#return '';
		$left = '<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_DELETE.'" id="logDelete" name="action[delete]"/>';
		$right = '&nbsp;';

		ob_start();
		echo '<div id="infodiag" class="dialog2" title="'.ML_LABEL_NOTE.'">'.ML_HINT_NO_PRODUCTS_SELECTED.'</div>';
		?>
<script type="text/javascript">/*<![CDATA[*/
$(document).ready(function() {

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

	public function renderView() {
		$html = '';
		if (!empty($this->deletedLog)) {
		
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
					<table class="datagrid" id="deletedlog">
						<thead><tr>
							<td><input type="checkbox" id="selectAll"/><label for="selectAll">'.ML_LABEL_CHOICE.'</label></td>
							<td>'.ML_LABEL_SHOP_TITLE.'&nbsp;'.$this->sortByType('itemname').'</td>
							<td>'.ML_LABEL_CATEGORY_PATH.'</td>
							<td>'.ML_GENERIC_OLD_PRICE.'</td>
							<td>'.ML_GENERIC_DELETEDDATE.'&nbsp;'.$this->sortByType('date').'</td>
						</tr></thead>
						<tbody>';
			$oddEven = false;
			foreach ($this->deletedLog as $item) {
				$commissiondate = strtotime($item['date']);
				$hdate = date("d.m.Y", $commissiondate).' &nbsp;&nbsp;<span class="small">'.date("H:i", $commissiondate).'</span>';

				$html .= '
							<tr class="'.(($oddEven = !$oddEven) ? 'odd' : 'even').'">
								<td><input type="checkbox" name="delIDs[]" value="'.$item['id'].'"></td>
								<td>'.fixHTMLUTF8Entities(empty($item['itemname']) ? $item['sku'] : $item['itemname']).'</td>
								<td><ul><li>'.str_replace('<br>', '</li><li>', renderCategoryPath($item['products_id'], 'product')).'</li></ul></td>
								<td>'.$this->simplePrice->setPrice($item['old_price'])->format().'</td>
								<td>'.$hdate.'</td>
							</tr>';
			}
			$html .= '
						</tbody>
					</table>';
			ob_start(); ?>
<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
		$('#selectAll').click(function() {
			state = $(this).attr('checked');
			$('#deletedlog input[type="checkbox"]:not([disabled])').each(function() {
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
		} else {
			$html .= '<table class="magnaframe"><tbody><tr><td>'.ML_GENERIC_NO_DELETED_ITEMS_YET.'</td></tr></tbody></table>';
		}

		return $html;
	}
}
