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
 * $Id: viewchangelog.php 1271 2011-09-27 22:32:14Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

function imageHTML($fName, $alt = '') {
	$alt = ($alt != '') ? $alt : basename($fName);
	return '<img src="'.$fName.'" alt="'.$alt.'" />';
}

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
echo '<h2>SKUs anzeigen</h2>';

$for = array ('stamm', 'variant');
if (isset($_GET['for']) && in_array($_GET['for'], $for)) {
	$for = $_GET['for'];
	$_url['for'] = $_GET['for'];
}

$offset = array (
	'START' => 0,
	'COUNT' => 50
);
if (isset($_GET['page']) && ctype_digit($_GET['page'])) {
	$page = $_GET['page'];
} else {
	$page = 1;
}
--$page;
$offset['START'] = $offset['COUNT'] * $page;

if (isset($_POST['search'])) {
	$search = trim($_POST['search']);
	$_url['search'] = $search;
} else if (isset($_GET['search'])) {
	$search = trim($_GET['search']);
	$_url['search'] = $search;
} else {
	$search = false;
}


$rendered = false;
if ($for == 'stamm') {
	$searchable = array (
		'p.products_id', 'p.products_model', 'p.products_ean', 'pd.products_name'
	);
	$searchQuery = '';
	if ($search !== false) {
		$searchQuery = 'WHERE (';
		$eSearch = MagnaDB::gi()->escape($search);
		foreach ($searchable as $f) {
			$searchQuery .= '('.$f.' LIKE \'%'.$eSearch.'%\') OR ';
		}
		$searchQuery = substr($searchQuery, 0, -4).')';
	}
	
	$data = MagnaDB::gi()->fetchArray(eecho('
		SELECT SQL_CALC_FOUND_ROWS p.products_id as ProduktID, p.products_model as ArtikelNummer, 
		       \'\' as SKU, p.products_ean AS EAN, pd.products_name as Name
		  FROM '.TABLE_PRODUCTS.' p
	 LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd ON p.products_id=pd.products_id AND pd.language_id = \''.$_SESSION['languages_id'].'\'
	  '.$searchQuery.'
	  ORDER BY p.products_id ASC
	     LIMIT '.$offset['START'].','.$offset['COUNT'].'
	', false));
	$foundProducts = MagnaDB::gi()->foundRows();
	$pages = ceil($foundProducts / $offset['COUNT']);
	
	//echo print_m($data);
	foreach ($data as &$item) {
		$item['SKU'] = magnaPID2SKU($item['ProduktID']);
	}
	$rendered = true;
	echo renderPagination($page, $pages, $_url);
	renderDataGrid($data);
	echo renderPagination($page, $pages, $_url);
	
} else if ($for == 'variant') {
	$searchable = array (
		'products_attributes_id', 'attributes_model', 'attributes_ean', 'pd.products_name',
		'pov.products_options_values_name'
	);
	$searchQuery = '';
	if ($search !== false) {
		$searchQuery = 'AND (';
		$eSearch = MagnaDB::gi()->escape($search);
		foreach ($searchable as $f) {
			$searchQuery .= '('.$f.' LIKE \'%'.$eSearch.'%\') OR ';
		}
		$searchQuery = substr($searchQuery, 0, -4).')';
	}

	$data = MagnaDB::gi()->fetchArray(eecho('
		SELECT SQL_CALC_FOUND_ROWS products_attributes_id as AttributID, attributes_model as ArtikelNummer,
		       \'\' as SKU, attributes_ean AS EAN, 
		       pd.products_name as Name,
		       po.products_options_name AS VariationTitel,
			   pov.products_options_values_name AS VariationWert
		  FROM '.TABLE_PRODUCTS_ATTRIBUTES.' pa,
	           '.TABLE_PRODUCTS_OPTIONS.' po, 
	           '.TABLE_PRODUCTS_OPTIONS_VALUES.' pov, 
	           '.TABLE_PRODUCTS_DESCRIPTION.' pd,
	           '.TABLE_LANGUAGES.' l
         WHERE po.language_id = l.languages_id
               AND pa.products_id=pd.products_id 
               AND pd.language_id = l.languages_id
	           AND po.products_options_id = pa.options_id
	           AND po.products_options_name<>\'\'
	           AND pov.language_id = l.languages_id
	           AND pov.products_options_values_id = pa.options_values_id
	           AND pov.products_options_values_name<>\'\'
	           AND l.directory = \''.$_SESSION['language'].'\'
	           '.$searchQuery.'
	  ORDER BY products_attributes_id ASC
	     LIMIT '.$offset['START'].','.$offset['COUNT'].'
	', false));
	$foundProducts = MagnaDB::gi()->foundRows();
	$pages = ceil($foundProducts / $offset['COUNT']);
	
	//echo print_m($data);
	foreach ($data as &$item) {
		$item['SKU'] = magnaAID2SKU($item['AttributID']);
	}
	$rendered = true;
	echo renderPagination($page, $pages, $_url);
	renderDataGrid($data);
	echo renderPagination($page, $pages, $_url);	
}

if ($rendered) {
	$tURL = $_url;
	unset($tURL['for']);
	unset($tURL['search']);
	unset($_url['search']);
	$leftButtons = '
		<a class="ml-button" href="'.toURL($tURL).'">'.
			imageHTML(DIR_MAGNALISTER_WS_IMAGES.'folder_back.png', ML_BUTTON_LABEL_BACK).' '. ML_BUTTON_LABEL_BACK . 
		'</a>';
	echo '
		<form action="'.toURL($_url).'" method="post">
			<table class="actions">
				<thead><tr><th>'.ML_LABEL_ACTIONS.'</th></tr></thead>
				<tbody>
					<tr class="firstChild"><td>
						<table><tbody><tr>
							<td class="firstChild">'.$leftButtons.'</td>
							<td><label for="tfSearch">'.ML_LABEL_SEARCH.':</label>
								<input id="tfSearch" name="search" type="text" value="'.fixHTMLUTF8Entities($search, ENT_COMPAT).'"/>
								<input type="submit" class="ml-button" value="'.ML_BUTTON_LABEL_GO.'" /></td>
							<td class="lastChild"></td>
						</tr></tbody></table>
					</td></tr>
				</tbody>
			</table>
		</form>';
} else {
	echo '
		<ul><li><a href="'.toURL($_url, array('for' => 'stamm')).'">Stammartikel</a></li>
		    <li><a href="'.toURL($_url, array('for' => 'variant')).'">Varianten</a></li></ul>';
}