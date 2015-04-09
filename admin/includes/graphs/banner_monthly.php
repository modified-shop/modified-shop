<?php
/* --------------------------------------------------------------
   $Id: banner_monthly.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner_monthly.php,v 1.3 2002/05/09); www.oscommerce.com 
   (c) 2003	 nextcommerce (banner_monthly.php,v 1.6 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

  require(DIR_WS_CLASSES . 'phplot.php');

  $year = (($_GET['year']) ? $_GET['year'] : date('Y'));

  $stats = array();
  for ($i=1; $i<13; $i++) {
    $stats[] = array(decode_utf8(strftime('%b', mktime(0,0,0,$i))), '0', '0');
  }

  $banner_stats_query = xtc_db_query("select month(banners_history_date) as banner_month, sum(banners_shown) as value, sum(banners_clicked) as dvalue from " . TABLE_BANNERS_HISTORY . " where banners_id = '" . $banner_id . "' and year(banners_history_date) = '" . $year . "' group by banner_month");
  while ($banner_stats = xtc_db_fetch_array($banner_stats_query)) {
    $stats[($banner_stats['banner_month']-1)] = array(decode_utf8(strftime('%b', mktime(0,0,0,$banner_stats['banner_month']))), (($banner_stats['value']) ? $banner_stats['value'] : '0'), (($banner_stats['dvalue']) ? $banner_stats['dvalue'] : '0'));
  }

  $graph = new PHPlot(600, 350, 'images/graphs/banner_monthly-' . $banner_id . '.' . $banner_extension);

  $graph->SetFileFormat($banner_extension);
  $graph->SetIsInline(1);
  $graph->SetPrintImage(0);

  $graph->SetSkipBottomTick(1);
  $graph->SetDrawYGrid(1);
  $graph->SetPrecisionY(0);
  $graph->SetPlotType('lines');

  $graph->SetPlotBorderType('left');
  $graph->SetTitleFontSize('4');
  $graph->SetTitle(html_entity_decode(sprintf(TEXT_BANNERS_MONTHLY_STATISTICS, $banner['banners_title'], $year)));

  $graph->SetBackgroundColor('white');

  $graph->SetVertTickPosition('plotleft');
  $graph->SetDataValues($stats);
  $graph->SetDataColors(array('blue','red'),array('blue', 'red'));

  $graph->DrawGraph();

  $graph->PrintImage();
?>