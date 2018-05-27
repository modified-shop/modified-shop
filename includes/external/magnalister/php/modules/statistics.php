
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
 * (c) 2010 - 2014 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_top.php');

if (($hp = magnaContribVerify('StatisticsExtension', 2)) !== false) {
    require($hp);
}

if (!empty($globalStats)) {
	$statisticsHTML = '
		<p>&nbsp;</p><div id="stats">';
	if (!function_exists('imagecreatetruecolor')) {
		$statisticsHTML .= '<b class="noticeBox">'.ML_ERROR_GD_LIB_MISSING.'</b>';
	} else {
		foreach ($globalStats as $stat) {
			$statisticsHTML .= '
				<div class="stat" title="'.$stat['title'].'">
					<img width="'.$globalStatSize['w'].'" height="'.$globalStatSize['h'].'" alt="'.$stat['title'].'" src="'.toURL($stat['url']).'"/>
				</div>';
		}
	}
	$statisticsHTML .= '
		<div class="visualClear"></div>
		</div>';
}

echo $statisticsHTML;

include_once(DIR_MAGNALISTER_INCLUDES.'admin_view_bottom.php');
include_once(DIR_WS_INCLUDES . 'application_bottom.php');
exit();
