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
 * $Id: orders.php 4582 2014-09-12 03:39:06Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

$result = MagnaDB::gi()->fetchArray('
	SELECT platform
	  FROM magnalister_orders, orders
	 WHERE orders.orders_id=magnalister_orders.orders_id
	       AND orders.date_purchased BETWEEN \''.$dateBack.'\' AND NOW()
  GROUP BY platform
', true);
//echo base64_encode(serialize($result))."\n";

$platforms = array('label' => '', 'total' => 0, 'shop' => 0);
if (!empty($result)) {
	foreach ($result as $item) {
		if (array_key_exists($item, $_modules)){
			$platforms[$_modules[$item]['title']] = 0;
		} else {
			$platforms[$item] = 0;
		}
	}
}

$query = MagnaDB::gi()->query('
	SELECT orders.orders_id, orders.date_purchased, magnalister_orders.platform
	  FROM orders
 LEFT JOIN magnalister_orders ON orders.orders_id=magnalister_orders.orders_id
     WHERE orders.date_purchased BETWEEN \''.$dateBack.'\' AND NOW()
');
//echo base64_encode(serialize(MagnaDB::gi()->fetchArray($query)))."\n";
#echo print_m(MagnaDB::gi()->fetchArray($query));
#die();

$shopOrderExists = false;
$semiFinal = array();
while ($item = MagnaDB::gi()->fetchNext($query)) {
//foreach ($query as $item) {
	$date = strtotime($item['date_purchased']);
	$key = date('Ym', $date);
	if (!isset($semiFinal[$key])) {
		$semiFinal[$key] = $platforms;
		$semiFinal[$key]['label'] = date('M y', $date);
	}
	if (empty($item['platform'])) {
		$item['platform'] = 'shop';
		$shopOrderExists = true;
	} else if (array_key_exists($item['platform'], $_modules)) {
		$item['platform'] = $_modules[$item['platform']]['title'];
	}
	++$semiFinal[$key][$item['platform']];
	++$semiFinal[$key]['total'];
}
#echo print_m($semiFinal);
#die();

if (empty($semiFinal)) {
	renderTextImage(
		$phPlotSettings['width'], $phPlotSettings['height'], ML_LABEL_NO_DATA, 
		$phPlotSettings['fonts']['title']['size'], $phPlotSettings['fonts']['title']['font']
	);
}

ksort($semiFinal);
//$semiFinal = array_slice($semiFinal, (int)$_GET['s']);
//print_r($semiFinal);

$finalData = array();
if ($shopOrderExists) {
	$labels = array('shop');
} else {
	$labels = array();
}
$maxHeigth = 0;
foreach ($semiFinal as $item) {
	$fItem = array();
	$fItem[] = $item['label'];
	if ($shopOrderExists) {
		$fItem[] = (int)$item['shop'];
	}
	$maxHeigth = ($maxHeigth < (int)$item['total']) ? (int)$item['total'] : $maxHeigth;
	
	unset($item['label']);
	unset($item['total']);
	unset($item['shop']);
	
	ksort($item);

	foreach ($item as $key => $val) {
		$fItem[] = (int)$val;
		if (!in_array($key, $labels)) {
			$labels[] = $key;
		}
	}
	$finalData[] = $fItem;
}

/*
header('Content-type: text/plain');
print_r($finalData);
die();
//*/

$maretplaceColors = array(
	'colors' => array(),
	'borders' => array()
);

foreach ($labels as $label) {
	$col = isset($phPlotSettings['colorMap']['marketplace'][$label]) ? $phPlotSettings['colorMap']['marketplace'][$label] : 'trueblack';
	$maretplaceColors['colors'][] = $col;
	$maretplaceColors['borders'][$label] = $phPlotSettings['colorMap']['data']['borders'][$col];
}

$plot = new PHPlotMagna($phPlotSettings['width'], $phPlotSettings['height']);
foreach ($phPlotSettings['fonts'] as $fontItem) {
	$plot->SetFontTTF($fontItem['for'], $fontItem['font'], $fontItem['size'], $fontItem['spacing']);
}
$plot->SetFileFormat($phPlotSettings['filetype']);

$plot->retinarize(ML_RETINA_DISPLY ? 2 : 1);

$plot->SetImageBorderType('none');
$plot->SetDefaultDashedStyle($phPlotSettings['dashedLineStyle']);

$transparentColor = 'white';
$plot->SetBackgroundColor($transparentColor);
$plot->SetTransparentColor($transparentColor);

$plot->SetPlotType('stackedbars');
$plot->SetRGBArray($phPlotSettings['colorMap']['data']['colors']);
$plot->SetDataColors($maretplaceColors['colors'], null, $phPlotSettings['colorMap']['data']['transparency']);
$plot->SetDataBorderColors($maretplaceColors['borders']);
$plot->SetDataType('text-data');
$plot->SetDataValues($finalData);

$yNumTicks = (int)($maxHeigth / 2) + ((int)($maxHeigth / 2) % 2);

$plot->SetTitle(ML_LABEL_STATS_ORDERS_PER_MARKETPLACE);
$plot->SetYTitle(ML_LABEL_STATS_ORDERS);
$plot->SetYScaleType('linear');
$plot->SetPrecisionY(0);
//$plot->SetNumYTicks($yNumTicks);

# No shading:
$plot->SetShading(0);

$plot->SetLegend($labels);
$plot->SetLegendStyle('left', 'left');

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetNumXTicks(count($finalData));
$plot->SetDrawXGrid(true);
//$plot->SetXDataLabelAngle(30);
$plot->FitXDataLabel();

$plot->SetYDataLabelPos('plotstack');
$plot->SetYDataLabelType('data', 0);

$plot->SetPlotBorderType(array('left', 'bottom'));

$yTick = $plot->GetCalcTicks('y');
$plot->SetLegendWorld(count($finalData), $yTick[1]);

$dim = $plot->getLegendDimensions();
$plot->SetMarginsPixels(NULL, $dim[0]+1, NULL, NULL);

$plot->DrawGraph();
