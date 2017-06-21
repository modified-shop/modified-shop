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
/*
header('Content-Type: text/html; charset=UTF-8');

if (isset($_SESSION['MagnaRAW'])) {
	$oldState = $_SESSION['MagnaRAW'];
} else {
	$oldState = false;
}

$_SESSION['MagnaRAW'] = 'true';
/* Test API-Functions here * /
try {
	$result = MagnaConnector::gi()->submitRequest(array(
		'SUBSYSTEM' => 'Maranon',
		'ACTION' => 'ItemSearch',
		'NAME' => 'How I met your mother: Season 1 (3 DVDs)'
	));
	echo print_m($result, '$result');
} catch (MagnaException $e) {
	echo print_m($e->getErrorArray(), $e->getMessage());
}

/* End Test * /
$_SESSION['MagnaRAW'] = $oldState;
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
*/

require_once(DIR_MAGNALISTER_INCLUDES.'lib/classes/GenerateProductsDetailInput.php');

$data = array (
	'prod' => array (
		'head' => 'Details',
		'key'  => 'n',
		'fields' => array (
			array (
				'label' => 'Produktname',
				'inputs' => array (
					array (
						'cols' => array (
							array (
								'type' => 'text',
								'key' => 'ItemName',
								'verify' => 'notEmpty',
							)
						)
					)
				),
				'required' => true,
			),
			array (
				'label' => 'Hersteller',
				'desc'  => 'Hersteller des Produktes',
				'inputs' => array (
					array (
						'cols' => array (
							array (
								'type' => 'text',
								'key' => 'Manufacturer',
								'verify' => 'notEmpty',
							)
						)
					)
				),
				'required' => true,
			),
			array (
				'label' => 'Marke',
				'desc'  => 'Marke oder Hersteller des Produktes',
				'inputs' => array (
					array (
						'cols' => array (
							array (
								'type' => 'text',
								'key' => 'Brand',
							)
						)
					)
				),
			),
			array (
				'label' => 'Modellnummer',
				'desc'  => 'Geben Sie die Modellnummer des Herstellers f&uuml;r das Produkt an.',
				'inputs' => array (
					array (
						'cols' => array (
							array (
								'type' => 'text',
								'key' => 'ManufacturerPartNumber',
							)
						)
					)
				),
			),
			array (
				'label' => 'EAN',
				'inputs' => array (
					array (
						'cols' => array (
							array (
								'type' => 'text',
								'key' => 'EAN',
								'verify' => 'notEmpty',
							)
						)
					)
				),
				'required' => true,
			),
		)
	),
	# Eine Unterkategorie
	'catBla' => array (
		'head' => 'Zus&auml;tzliche Eingabemasken f&uuml;r die von Ihnen gew&auml;hlte Kategorie',
		'desc' => 'Bitte geben Sie alle Werte an, die f&uuml;r relevant halten. Vergessen Sie nicht,
		           dass einige Werte angegeben werden m&uuml;ssen, wenn Sie andere ebenfalls angegeben haben.<br />
		           Bitte lesen Sie auch immer die Hinweistexte auf der rechten Seite.',
		'key'  => 'meh',
		'fields' => array (
			'attr1' => array (
				'label' => 'Durchmesser des Gegenstandes',
				'desc' => 'Wenn Sie den Durchmesser angeben, m&uuml;ssen Sie auch die Ma&szlig;einheit angeben!',
				'inputs' => array (
					'row1' => array (
						'cols' => array (
							'col1' => array (
								'label' => 'Durchmesser',
								'type' => 'text',
								'verify' => 'isINT',
								'key' => 'Diameter',
								'dependsOn' => array (
									'key' => 'DiameterUnitOfMeasure',
									'ifNotSet' => 'error',
									'blockIfNotSet' => false,
								),
							),
							'col2' => array (
								'label' => 'Ma&szlig;einheit',
								'type' => 'select',
								'values' => array (
									'' => '',
									'cm' => 'Centimeter',
									'mm' => 'Millimeter',
									'dm' => 'Decimeter',
								),
								'key' => 'DiameterUnitOfMeasure',
								'dependsOn' => array (
									'key' => 'Diameter',
									'ifNotSet' => 'ignore',
									'blockIfNotSet' => true,
								),
							),
						),
						'repeat' => 5,
					),
					'row2' => array (
						'cols' => array (
							'col1' => array (
								'type' => 'text',
								'key' => 'Bla',
							),
						),
					),
				),
			),
			'attr2' => array(
				'label' => 'A special date',
				'desc' => 'Let\'s fetz',
				'inputs' => array (
					'row1' => array (
						'cols' => array (
							'col1' => array (
								'type' => 'date',
								'key' => 'TestDate',
								#'default' => '-2 days',
							),
						),
					),
				),
				'required' => true,
			),
			'attr3' => array(
				'label' => 'Ein Textarea mit WYSIWYG',
				'desc' => 'Ein Feld f&uuml;r viel Text.',
				'inputs' => array (
					'row1' => array (
						'cols' => array (
							'col1' => array (
								'type' => 'textarea',
								'key' => 'TextWYSIWYG',
								'wysiwyg' => true,
								'validTags' => array('p' => array('style' => array()), 'br' => array()),
							),
						),
					),
				),
			),
			'attr4' => array(
				'label' => 'Ein Textarea ohne WYSIWYG',
				'desc' => 'Ein Feld f&uuml;r viel Text.',
				'inputs' => array (
					'row1' => array (
						'cols' => array (
							'col1' => array (
								'type' => 'textarea',
								'key' => 'TextPlain',
							),
						),
					),
				),
			),
		),
	),
);


if (array_key_exists('kind', $_GET) && ($_GET['kind'] == 'ajax') && array_key_exists('load', $_POST)) {
	$fData = array();
	if (empty($_POST['load'])) {
		die();
	}
	foreach ($_POST['load'] as $block) {
		$fData[$block] = $data[$block];
	}
	$gPDI = new GenerateProductsDetailInput($fData);
	echo $gPDI->render();
	die();
}

$fData = array('prod' => $data['prod']);

if (array_key_exists('__loadedBlocks', $_POST) && !empty($_POST['__loadedBlocks'])) {
	foreach ($_POST['__loadedBlocks'] as $block) {
		$fData[$block] = $data[$block];
	}
}

$_js[] = DIR_MAGNALISTER_WS.'js/tinymce/tinymce.min.js';
$gPDI = new GenerateProductsDetailInput($fData);

if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');
}
?>
<form name="apply" method="post" action="#">
<?php
	# echo print_m($_POST, '$_POST');
	# echo print_m($data);
	if (!$gPDI->verifyItems()) {
		echo '
			<p class="noticeBox">
				Einige Daten wurden falsch oder unvollst&auml;ndig ausgef&uuml;llt. Bitte korrigieren Sie die hervorgegebenen Felder.
			</p>';
	}
?>
	<input type="hidden" name="saveApplyData" value="true"/>
	<p><b>Hinweis:</b> Die mit <span class="bull">&bull;</span> markierten Felder sind Pflichtfelder und m&uuml;ssen ausgef&uuml;llt werden.
	<table class="attributesTable">
		<tbody>
			<?php
			echo $gPDI->render();
?>
		</tbody>
	</table>
	<table class="actions">
		<thead><tr><th>Aktionen</th></tr></thead>
		<tbody>
			<tr class="firstChild"><td>
				<table><tbody><tr>
					<td class="firstChild"><input class="ml-button" type="button" id="ajaxLoadForm" value="Load"/></td>
					<td class="lastChild"><input class="ml-button" type="submit" value="Daten speichern"/></td>
				</tr></tbody></table>
			</td></tr>
		</tbody>
	</table>
</form>

<script type="text/javascript">/*<![CDATA[*/
	$(document).ready(function() {
		$('#ajaxLoadForm').click(function () {
			jQuery.ajax({
				type: 'POST',
				url: '<?php echo toURL(array('module' => 'apitest', 'kind' => 'ajax'), true); ?>',
				data: {
					'kind': 'ajax',
					'load': ['catBla']
				},
				success: function(data) {
					$('table.attributesTable tbody#attributes').detach();
					$('table.attributesTable').append('<tbody id="attributes">'+data+'</tbody>');
				},
				dataType: 'html'
			});
		});
	});
/*]]>*/</script>
<?php
if (!isset($_GET['kind']) || ($_GET['kind'] != 'ajax')) {
	echo print_m($_POST, '$_POST');
	require(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
}
exit();