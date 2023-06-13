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
include_once(DIR_MAGNALISTER_INCLUDES.'lib/phplot/phplot.php');

class PHPlotMagna extends PHPlot_truecolor {
	public function GetCalcTicks($what) {
		$this->_precalc();
		return parent::CalcTicks($what);
	}
	
	public function getLegendDimensions() {
		$font = &$this->fonts['legend'];

		// Find maximum legend label line width.
		$max_width = 0;
		foreach ($this->legend as $line) {
			list($width, $unused) = $this->SizeText($font, 0, $line);
			if ($width > $max_width) $max_width = $width;
		}

		// Use the font parameters to size the color boxes:
		$char_w = $font['width'];
		$char_h = $font['height'];
		$line_spacing = $this->GetLineSpacing($font);

		// Normalize text alignment and colorbox alignment variables:
		$text_align = isset($this->legend_text_align) ? $this->legend_text_align : 'right';
		$colorbox_align = isset($this->legend_colorbox_align) ? $this->legend_colorbox_align : 'right';

		// Sizing parameters:
		$v_margin = $char_h/2;				   // Between vertical borders and labels
		$dot_height = $char_h + $line_spacing;   // Height of the small colored boxes
		// Overall legend box width e.g.: | space colorbox space text space |
		// where colorbox and each space are 1 char width.
		if ($colorbox_align != 'none') {
			$width = $max_width + 4 * $char_w;
			$draw_colorbox = TRUE;
		} else {
			$width = $max_width + 2 * $char_w;
			$draw_colorbox = FALSE;
		}

		$box_start_x = 0;
		$box_start_y = 0;

		// Dimensions
		$box_height = $dot_height*(count($this->legend)) + 2*$v_margin;
		$box_width = $width;

		return array(
			$box_width,
			$box_height
		);
	}
	
	public function FitXDataLabel() {
		$this->_precalc();
		
		#echo print_m($this->fonts, '$this->fonts');
		
		$longestStrDim = array(0, 0);
		foreach ($this->data as $row) {
			$len = $this->SizeText($this->fonts['x_label'], 0, $row[0]);
			if ($len[0] > $longestStrDim[0]) {
				$longestStrDim = $len;
			}
		}
		#echo print_m($longestStrDim, '$longestStrDim');
		$longestStrDim[0] *= 2;
		
		/*
NON RETINA
$longestStrDim :: Array
(
    [0] => 30
    [1] => 8
)
$group_width :: 20.923076923077
$deg :: 45.778354013124


RETINA
$longestStrDim :: Array
(
    [0] => 30
    [1] => 8
)
$group_width :: 51.692307692308
$deg :: 0
		*/
		
		if (in_array($this->plot_type, array('stackedbars', 'bars'))) {
			$padding = 5;
			$group_width = (($this->plot_area_width / $this->num_data_rows) - $padding);
		#	echo print_m($group_width, '$group_width');
			if ($group_width < $longestStrDim[0]) {
				$deg = rad2deg(acos($group_width / $longestStrDim[0]));
				// $deg = rad2deg(asin(($longestStrDim[1] + 5) / $longestStrDim[0]));
			} else {
				$deg = 0;
			}
		#	echo print_m($deg, '$deg');
			$this->SetXDataLabelAngle($deg);
		}
		#die();
		return;
	}
	
	private function _precalc() {
		if (!$this->CheckDataArray())
			return FALSE; // Error message already reported.

		// Allocate colors for the plot:
		$this->SetColorIndexes();

		// For pie charts: don't draw grid or border or axes, and maximize area usage.
		// These controls can be split up in the future if needed.
		$draw_axes = ($this->plot_type != 'pie');

		// Get maxima and minima for scaling:
		if (!$this->FindDataLimits())
			return FALSE;

		// Set plot area world values (plot_max_x, etc.):
		if (!$this->CalcPlotAreaWorld())
			return FALSE;

		// Calculate X and Y axis positions in World Coordinates:
		$this->CalcAxisPositions();

		// Process label-related parameters:
		$this->CheckLabels();

		// Apply grid defaults:
		$this->CalcGridSettings();

		// Calculate the plot margins, if needed.
		// For pie charts, set the $maximize argument to maximize space usage.
		$this->CalcMargins(!$draw_axes);

		// Calculate the actual plot area in device coordinates:
		$this->CalcPlotAreaPixels();

		// Calculate the mapping between world and device coordinates:
		$this->CalcTranslation();
	}
	
	public function retinarize($factor) {
		$this->image_width *= $factor;
		$this->image_height *= $factor;
		
		$this->img = ImageCreate($this->image_width, $this->image_height);
		if (    !is_resource($this->img)
		     && (!class_exists('GdImage') || !($this->img instanceof GdImage))//PHP 8

		) {
			return $this->PrintError('PHPlot(): Could not create image resource.');
		}
		
		foreach ($this->fonts as &$font) {
			$font['width'] *= $factor;
			$font['height'] *= $factor;
			if (isset($font['size'])) {
				$font['size'] *= $factor;
			}
			if (isset($font['spacing'])) {
				$font['spacing'] = is_null($font['spacing']) ? $font['spacing'] : $font['spacing'] * $factor;
			}
			$font['line_spacing'] = is_null($font['line_spacing']) ? $font['line_spacing'] : $font['line_spacing'] * $factor;
		}
		
		#echo print_m($this->fonts, '$this->fonts');
		#die();
	}
}
