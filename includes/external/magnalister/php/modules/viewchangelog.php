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

#$warings = null;
#echo fileGetContents(MAGNA_SERVICE_URL.MAGNA_APIRELATED.'Changelog/?shop='.SHOPSYSTEM.'&pass='.getDBConfigValue('general.passphrase', '0').'&lang='.$_langISO.'&build='.CLIENT_BUILD_VERSION, $warings, 10);

/*
$chlog = file_get_contents(DIR_MAGNALISTER_FS.'ChangeLog');

$chlog = str_replace(array("\r\n", "\r"), "\n", $chlog);
$chlog = fixHTMLUTF8Entities($chlog);
$header = substr($chlog, 0, strpos($chlog, '*'.'/')+2);
//echo print_m($header);
$chlog = substr($chlog, strpos($chlog, '*'.'/')+2);
$chlog = preg_replace('/(=+)\s(.*)\s(=+)/e', "'<h'.strlen('\\1').'>'.'$2'.'</h'.strlen('\\1').'>'", $chlog);
$chlog = preg_replace('/\*\s(.*)/', '<ul><li>$1</li></ul>', $chlog);
$chlog = preg_replace("/<\/li><\/ul>(\s*)<ul><li>/s", "</li>$1<li>", $chlog);

echo ($chlog);
*/

class ChangeLogViewer {
	protected $headlines = array (
		array (
			'title' => ML_LABEL_TOPIC,
			'render' => 'TitleDesc',
			'sorting' => false,
		),
		array (
			'title' => ML_LABEL_PROJECT,
			'render' => 'Project',
			'sorting' => 'Project',
		),
		array (
			'title' => ML_LABEL_REVISION,
			'render' => 'Revision',
			'sorting' => 'Revision',
		),
		array (
			'title' => ML_LABEL_DATE,
			'render' => 'DateAdded',
			'sorting' => 'DateAdded',
		),
	);
	
	protected $itemsPerPage = 50;
	protected $pageCount = 1;
	
	protected $sort = array();
	
	protected $requestParams = array();
	
	protected $data = array();
	
	protected function init() {
		global $_MagnaSession;
		
		$_MagnaSession['currentPlatform'] = '';
		
		$this->requestParams = array (
			'module' => 'viewchangelog',
			'search' => trim(isset($_REQUEST['search']) ? (string)$_REQUEST['search'] : ''),
			'page' => max(1, (isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1)),
			'sorting' => isset($_REQUEST['sorting']) ? $_REQUEST['sorting'] : '',
		);
		
		$sort = explode('-', $this->requestParams['sorting']);
		$sortOk = false;
		foreach ($this->headlines as $set) {
			if ($set['sorting'] == $sort[0]) {
				$sortOk = true;
				break;
			}
		}
		if ($sortOk && isset($sort[1]) && in_array($sort[1], array('asc', 'desc'))) {
			$this->sort = array (
				'ORDERBY' => $sort[0],
				'SORTORDER' => strtoupper($sort[1]),
			);
		} else {
			unset($this->requestParams['sorting']);
		}
	}
	
	protected function fetchLog() {
		try {
			$data = MagnaConnector::gi()->submitRequest(array_merge(array (
				'ACTION' => 'GetChangelog',
				'SUBSYSTEM' => 'core',
				'SEARCH' => $this->requestParams['search'],
				'LIMIT' => $this->itemsPerPage,
				'OFFSET' => ($this->requestParams['page'] - 1) * $this->itemsPerPage,
			), $this->sort));
			
			$this->pageCount = max(1, ceil($data['NUMBEROFITEMS'] / $this->itemsPerPage));
			
			$this->data = $data['DATA'];
			arrayEntitiesFixHTMLUTF8($this->data);
			
			$this->requestParams['page'] = min(max(1, $this->requestParams['page']), $this->pageCount);
			
		} catch (MagnaException $me) {
			
		}
	}
	
	protected function getUrlParameters($params = array('module')) {
		$r = array('module' => $this->requestParams['module']);
		foreach ($params as $p) {
			if (isset($this->requestParams[$p]) && !empty($this->requestParams[$p])) {
				$r[$p] = $this->requestParams[$p];
			}
		}
		return array_unique($r);
	}
	
	protected function renderFilters() {
?>
<table class="fullWidth nospacing nopadding valigntop topControls">
	<tbody>
		<tr>
			<td>
				<form action="<?php echo toURL($this->getUrlParameters()); ?>" method="post" onchange="this.submit();">
					<table class="nospacing nopadding right">
						<tbody>
							<tr>
								<td class="filterRight">
									<div class="filterWrapper">
										<div class="newSearch">
											<input type="text" value="<?php echo htmlspecialchars($this->requestParams['search']); ?>" class="n" name="search">
											<button type="submit" class="mlbtn"><span></span></button>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</td>
		</tr>
	</tbody>
</table>
<?php
	}
	
	protected function renderPagination() {
?>
	<table class="ml-pagination">
		<tr>
			<td class="ml-pagination">
				<span class="bold"><?php echo ML_LABEL_CURRENT_PAGE.' '.$this->requestParams['page']; ?></span>
			</td>
			<td class="textright">
				<?php 
					echo renderPagination(
						$this->requestParams['page'],
						$this->pageCount,
						$this->getUrlParameters(array('search', 'sorting'))
					);
				?>
			</td>
		</tr>
	</table>
<?php
	}
	
	protected function renderFieldTitleDesc($row) {
		echo (empty($row['PublicText'])
				? ''
				: '<div class="longdesc"><strong>'.$row['Title'].'</strong><div class="light">'.nl2br($row['PublicText']).'</div></div>'
			).'
			'.$row['Title'];
	}
	
	protected function renderFieldProject($row) {
		echo $row['Project'];
	}
	
	protected function renderFieldRevision($row) {
		echo $row['Revision'];
		
	}
	
	protected function renderFieldDateAdded($row) {
		echo date(ML_FORMAT_DATE, strtotime($row['DateAdded']));
	}
	
	
	protected function renderLog() {
		
?>
<style>
div.changelog table.list thead tr td {
	padding-bottom: 13px;
	padding-top: 13px;
}
div.changelog table.list tbody tr td {
	padding-top: 8px;
	padding-bottom: 8px;
}
div.changelog table.list td > div {
	width: 100%;
}
div.changelog td.titledesc {
	position: relative;
}
div.changelog td.titledesc div.longdesc {
	position: absolute;
	left: 0;
	right: 15px;
	display: none;
	background: #fff;
	color: #555;
	border: 1px solid #999;
	line-height: 1.5em;
	z-index: 10;
	margin: -9px 0 0 0px;
	padding: 7px 5px 8px 5px;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}
div.changelog td.titledesc:hover div.longdesc {
	display: block;
}
div.changelog td.titledesc div.longdesc strong {
	display: block;
	margin-bottom: 0.5em;
	color: #333;
}
div.changelog table.list td.dateadded {
	text-align: right;
}
div.changelog div.ml-legend {
	background: #f6f6f6;
	color: #000;
	padding: 1.5em;
	font-size: 10px;
	margin-top: 1.5em;
}
div.changelog div.ml-legend p:first-child {
	margin-top: 0;
}
div.changelog div.ml-legend p:last-child {
	margin-bottom: 0;
}
</style>
<form class="categoryView">
<table class="list">
	<thead>
		<tr>
		<?php foreach ($this->headlines as $aElement) { ?>
			<td class="<?php echo strtolower($aElement['render']);?>">
				<?php echo defined($aElement['title']) ? constant($aElement['title']) : $aElement['title']; ?>
				<?php if ($aElement['sorting'] !== false) { ?>
					<span class="nowrap">
						<a href="<?php echo toURL($this->getUrlParameters(array('search')), array('sorting' => $aElement['sorting'].'-asc')); ?>" title="<?php echo ML_LABEL_SORT_ASCENDING; ?>" class="sorting">
							<img alt="<?php echo ML_LABEL_SORT_ASCENDING; ?>" src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>sort_up.png">
						</a>
						<a href="<?php echo toURL($this->getUrlParameters(array('search')), array('sorting' => $aElement['sorting'].'-desc')); ?>" title="<?php echo ML_LABEL_SORT_DESCENDING; ?>" class="sorting">
							<img alt="<?php echo ML_LABEL_SORT_DESCENDING; ?>" src="<?php echo DIR_MAGNALISTER_WS_IMAGES; ?>sort_down.png">
						</a>
					</span>
				<?php } ?>
			</td>
		<?php } ?>
		</tr>
	</thead>
	<tbody>
	<?php if (empty($this->data)) {?>
		<tr class="odd">
			<td colspan="<?php echo count($this->headlines); ?>"><?php echo ML_LABEL_NO_ENTIRES_FOUND; ?></td>
		</tr>
	<?php } else { ?>
	<?php foreach ($this->data as $iRow => $row) { ?>
		<tr class="<?php echo ($iRow % 2 == 0) ? 'odd' : 'even'; ?>">
		<?php foreach ($this->headlines as $aElement) { ?>
			<td class="<?php echo strtolower($aElement['render']);?>"><?php $this->{'renderField'.$aElement['render']}($row); ?></td>
		<?php } ?>
		</tr>
	<?php } ?>
	<?php } ?>
	</tbody>
</table>
</form>
<?php
	}
	
	protected function render() {
		
		require_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
		
		#echo print_m($this->requestParams, '$this->requestParams');
		echo '<div class="magnamain"><div class="changelog productList">';
		echo '<h1>Changelog</h1>';
		$this->renderFilters();
		$this->renderPagination();
		$this->renderLog();
		$this->renderPagination();
		echo '<div class="ml-legend">'.ML_TEXT_CHANGELOG_LEGEND.'</div>';
		echo '</div></div>';
		
		require_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
	}
	
	public function execute() {
		$this->init();
		$this->fetchLog();
		$this->render();
	}
	
}

$cl = new ChangeLogViewer();
$cl->execute();

include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();