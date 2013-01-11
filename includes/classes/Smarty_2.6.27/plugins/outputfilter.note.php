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
  
  $cop='<div class="copyright">'.((basename($PHP_SELF)=='index.php' && $_SERVER['QUERY_STRING']=='')?'<a href="http://www.modified-shop.org" target="_blank">':'').'<span class="cop_magenta">mod</span><span class="cop_grey">ified eCommerce Shopsoftware</span></a><span class="cop_gpl">' . '&nbsp;' . '&copy;2009-' . date('Y') . '&nbsp;' . 'provides no warranty and is redistributable under the '.((basename($PHP_SELF)=='index.php' && $_SERVER['QUERY_STRING']=='')?'<a class="cop_gpl" href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License (Version 2)</a>':'GNU General Public License (Version 2)').'</span></div>';

  //web28 - making output W3C-Conform: replace ampersands, rest is covered by the modified shopstat_functions.php - preg_replace by cYbercOsmOnauT: don't replace &&
  $tpl_output = preg_replace("/((?<!&))&(?!(&|amp;|#[0-9]+;|[a-z0-9]+;))/i", "&amp;", $tpl_output);

  return $tpl_output.$cop;
}

# SIE SIND IM BEGRIFF ETWAS ZU ÄNDERN, WAS NICHT FAIR IST. SIE MÖCHTEN MIT
# DIESER SOFTWARE GELD VERDIENEN ODER KUNDEN GEWINNEN. SIE HABEN NICHT STUNDEN 
# UND MONATE VERBRACHT DIESE SOFTWARE ZU ENTWICKELN UND ZU VERBESSEREN. ALS
# DANKESCHÖN AN DIE ENTWICKLER UND CODER LASSEN SIE DIESE DATEI, WIE SIE IST 
# ODER KRATZEN SIE AUCH VON IHREN ELEKTROGERÄTEN IM HAUS DIE MARKENZEICHEN AB!!!!
?>