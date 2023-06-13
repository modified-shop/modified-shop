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
 * $Id: main.php 4582 2014-09-12 03:39:06Z derpapst $
 *
 * (c) 2010 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
require_once(DIR_MAGNALISTER_INCLUDES.'lib/PHPLotMagna.php');

function renderTextImage($width, $height, $string, $fontSize = 12, $font = '') {
	$img = imagecreatetruecolor($width, $height);
	imagealphablending($img, false);
	imagesavealpha($img, true);
	imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
	
	if (!empty($font) && file_exists($font)
		&& ($bbox = @imagettfbbox($fontSize, 0, $font, $string)) !== false
	) {
		/* Array
		(
		    [0] => 0	untere linke Ecke, X-Position
		->  [1] => -1	untere linke Ecke, Y-Position array(170,  90,  50),
		    [2] => 86	untere rechte Ecke, X-Position
		    [3] => -1	untere rechte Ecke, Y-Position
		    [4] => 86	obere rechte Ecke, X-Position
		    [5] => -13	obere rechte Ecke, Y-Position
		    [6] => 0	obere linke Ecke, X-Position
		->  [7] => -13	obere linke Ecke, Y-Position
		) */
		$strWidth = abs($bbox[2] - $bbox[0]);
		$strHeight = abs($bbox[7] - $bbox[1]);
	
		imagettftext($img, $fontSize, 0, (($width / 2) - ($strWidth / 2)), (($height / 2) - ($strHeight / 2)), imagecolorallocate($img, 0, 0, 0), $font, $string);
	} else {
		$strWidth = imagefontwidth($fontSize) * strlen($string);
		$strHeight = imagefontheight($fontSize);
		imagestring($img, $fontSize, (($width / 2) - ($strWidth / 2)), (($height / 2) - ($strHeight / 2)), $string, imagecolorallocate($img, 0, 0, 0));
	}
	
	header("Content-type: image/png");
	imagepng($img);
	imagedestroy($img);
	die();
}

$phPlotSettings = array(
	'width' =>  $globalStatSize['w'],
	'height' => $globalStatSize['h'],
	'filetype' => 'png',
	'fonts' => array (
		'title' => array (
			'for' => 'title',
			'font' => DIR_MAGNALISTER_RESOURCE.'fonts/DejaVuSansCondensed.ttf',
			'size' => 11,
			'spacing' => NULL
		),
		'y_title' => array (
			'for' => 'y_title',
			'font' => DIR_MAGNALISTER_RESOURCE.'fonts/DejaVuSansCondensed.ttf',
			'size' => 8,
			'spacing' => NULL
		),
		'legend' => array (
			'for' => 'legend',
			'font' => DIR_MAGNALISTER_RESOURCE.'fonts/DejaVuSansCondensed.ttf',
			'size' => 7,
			'spacing' => 2
		),
		'x_label' => array (
			'for' => 'x_label',
			'font' => DIR_MAGNALISTER_RESOURCE.'fonts/DejaVuSansCondensed.ttf',
			'size' => 7,
			'spacing' => 2
		),
		'y_label' => array (
			'for' => 'y_label',
			'font' => DIR_MAGNALISTER_RESOURCE.'fonts/DejaVuSansCondensed.ttf',
			'size' => 7,
			'spacing' => 2
		),
	),
	'dashedLineStyle' => '2-2',
	'colorMap' => array (
		'data' => array (
			'colors' => array (
				'shop_green'        => array(203, 227, 107),
				'trueblack'         => array(  0,   0,   0),
				'amazon_yellow'     => array(228, 163,  20),
				'yatego_blue'       => array(100, 140, 219),
				'guenstiger_blue'   => array( 21,  26, 123),
				'ps_grey'           => array(114, 114, 114),
				'ebay_red'          => array(223,  55,  56),
				'check24_yellow'    => array(233, 196,  12),
				'hitmeister_orange' => array(232, 148,  80),
				'etsy_green'        => array(101, 196, 150),
				'kelkoo_orange'     => array(219, 103,  82),
				'daparto_blue'      => array( 66, 149, 203),
				'hood_brown'       => array(220, 170, 240), //array(170,  90,  50),
				'black'             => array(232, 148,  80),
			),
			'saturation' => 100,   /* in Prozent. 100 entspricht der aktuellen Farbe */
			'lightness'  => 100,   /* in Prozent. 100 entspricht der aktuellen Farbe */
			'bordersDarker' => 0.75,
			'borders' => array(), /* Wird aus colors Feld anhand von bordersDarker berechnet. */
			'transparency' => 40  /* 0 == full opaque, 127 == full transparent */
		),
		'marketplace' => array (
			'shop' => 'shop_green',
			$_modules['amazon']['title'] => 'amazon_yellow',
			$_modules['yatego']['title'] => 'yatego_blue',
			$_modules['guenstiger']['title'] => 'guenstiger_blue',
			$_modules['preissuchmaschine']['title'] => 'ps_grey',
			$_modules['ebay']['title'] => 'ebay_red',
			$_modules['check24']['title'] => 'check24_yellow',
			$_modules['hitmeister']['title'] => 'hitmeister_orange',
			$_modules['etsy']['title'] => 'etsy_green',
			$_modules['kelkoo']['title'] => 'kelkoo_orange',
			$_modules['daparto']['title'] => 'daparto_blue',
			$_modules['hood']['title'] => 'hood_brown',
		),
	)
);

if (($hp = magnaContribVerify('StatisticsExtensionSettings', 1)) !== false) {
    require($hp);
}

/* Berechnet Farben anhand der Einstellungen neu. */
foreach ($phPlotSettings['colorMap']['data']['colors'] as $key => &$color) {
	$hsv = rgb2hsv($color);
	$hsv[1] = $hsv[1] * ((float)$phPlotSettings['colorMap']['data']['saturation'] / 100.0);
	$hsv[2] = $hsv[2] * ((float)$phPlotSettings['colorMap']['data']['lightness'] / 100.0);
	$color = hsv2rgb($hsv);
	$phPlotSettings['colorMap']['data']['borders'][$key] = array(
		(int)($color[0] * $phPlotSettings['colorMap']['data']['bordersDarker']),
		(int)($color[1] * $phPlotSettings['colorMap']['data']['bordersDarker']),
		(int)($color[2] * $phPlotSettings['colorMap']['data']['bordersDarker'])
	);
}
//die(print_m($phPlotSettings['colorMap']));

$dateBack = (int)getDBConfigValue('general.stats.backwards', '0', 6);
$dateBack = date('Y-m-01 00:00:00', mktime(0, 0, 0, date('n') - $dateBack, 1, date("Y")));

if (isset($_GET['view'])) {
	if (!function_exists('imagecreatetruecolor')) {
		echo 'GD Lib is missing';
		die();
	}
	switch ($_GET['view']) {
		case 'orders': {
			require_once(DIR_MAGNALISTER_MODULES.'stats/orders.php');
			die();
		}
		case 'ordersPercent': {
			require_once(DIR_MAGNALISTER_MODULES.'stats/ordersPercent.php');
			die();
		}
		case 'visualizeColors': {
			//echo print_m($phPlotSettings['colorMap']['data']['colors']);
			foreach ($phPlotSettings['colorMap']['data']['colors'] as $key => $col) {
				$borderC = $phPlotSettings['colorMap']['data']['borders'][$key];
				echo '
					<div style="width: 50px; height: 50px; margin: 0 5px 5px 0; float: left;
					            background: rgba('.$col[0].', '.$col[1].', '.$col[2].', '.((127 - $phPlotSettings['colorMap']['data']['transparency']) / 127).');
					            border: 1px solid rgb('.$borderC[0].', '.$borderC[1].', '.$borderC[2].');
					            overflow: hidden; font-family: sans-serif; font-size: 9px;
					            line-height: 50px; text-align: center; text-shadow: 0px 0px 2px rgba(255, 255, 255, 0.75);
					     "><span>'.$key.'</span></div>';
				
			}
			die();
		}
	}
}

if (($hp = magnaContribVerify('StatisticsExtension', 1)) !== false) {
    require($hp);
}

renderTextImage(
	$phPlotSettings['width'], $phPlotSettings['height'], ML_LABEL_NO_DATA, 
	$phPlotSettings['fonts']['title']['size'], $phPlotSettings['fonts']['title']['font']
);
