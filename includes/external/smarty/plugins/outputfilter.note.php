<?php
/* -----------------------------------------------------------------------------------------
   $Id: outputfilter.note.php 1554 2010-12-05 15:23:03Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 xt:Commerce (campaigns.php 1117 2005-07-25)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

# SIE SIND IM BEGRIFF ETWAS ZU ÄNDERN, WAS NICHT FAIR IST. SIE MÖCHTEN MIT
# DIESER SOFTWARE GELD VERDIENEN ODER KUNDEN GEWINNEN. SIE HABEN NICHT STUNDEN 
# UND MONATE VERBRACHT DIESE SOFTWARE ZU ENTWICKELN UND ZU VERBESSEREN. ALS
# DANKESCHÖN AN DIE ENTWICKLER UND CODER LASSEN SIE DIESE DATEI, WIE SIE IST 
# ODER KRATZEN SIE AUCH VON IHREN ELEKTROGERÄTEN IM HAUS DIE MARKENZEICHEN AB!!!!

function smarty_outputfilter_note($tpl_output, &$smarty) {
  global $PHP_SELF;
  
  $cop = PHP_EOL.'<div class="copyright">'.((MODULE_SMALL_BUSINESS == 'true') ? '<span class="small_bussiness">'.TAX_INFO_SMALL_BUSINESS_FOOTER.'</span><br/>' : '').((basename($PHP_SELF)=='index.php' && $_SERVER['QUERY_STRING']=='')?'<a rel="nofollow" href="http://www.modified-shop.org" target="_blank">':'').'<span class="cop_magenta">mod</span><span class="cop_grey">ified eCommerce Shopsoftware &copy; 2009-' . date('Y') . '</span>'.((basename($PHP_SELF)=='index.php' && $_SERVER['QUERY_STRING']=='')?'</a>':'').'</div>';

  //web28 - making output W3C-Conform: replace ampersands, rest is covered by the modified shopstat_functions.php - preg_replace by cYbercOsmOnauT: don't replace &&
  $tpl_output = preg_replace("/((?<!&))&(?!(&|amp;|#[0-9]+;|[a-z0-9]+;))/i", "&amp;", $tpl_output);

  // compress HTML
  if (COMPRESS_HTML_OUTPUT == 'true') {
    require_once(DIR_FS_EXTERNAL.'compactor/compactor.php');
    $compactor = new Compactor();
    $tpl_output = $compactor->squeeze($tpl_output);
  }
  
  return $tpl_output.$cop;
}

# SIE SIND IM BEGRIFF ETWAS ZU ÄNDERN, WAS NICHT FAIR IST. SIE MÖCHTEN MIT
# DIESER SOFTWARE GELD VERDIENEN ODER KUNDEN GEWINNEN. SIE HABEN NICHT STUNDEN 
# UND MONATE VERBRACHT DIESE SOFTWARE ZU ENTWICKELN UND ZU VERBESSEREN. ALS
# DANKESCHÖN AN DIE ENTWICKLER UND CODER LASSEN SIE DIESE DATEI, WIE SIE IST 
# ODER KRATZEN SIE AUCH VON IHREN ELEKTROGERÄTEN IM HAUS DIE MARKENZEICHEN AB!!!!
?>