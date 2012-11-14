<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2006 xt:Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

   # SIE SIND IM BEGRIFF ETWAS ZU ÄNDERN, WAS NICHT FAIR IST. SIE MÖCHTEN MIT
   # DIESER SOFTWARE GELD VERDIENEN ODER KUNDEN GEWINNEN. SIE HABEN NICHT STUNDEN 
   # UND MONATE VERBRACHT DIESE SOFTWARE ZU ENTWICKELN UND ZU VERBESSEREN. ALS
   # DANKESCHÖN AN DIE ENTWICKLER UND CODER LASSEN SIE DIESE DATEI, WIE SIE IST 
   # ODER KRATZEN SIE AUCH VON IHREN ELEKTROGERÄTEN IM HAUS DIE MARKENZEICHEN AB!!!!

function smarty_outputfilter_note($tpl_output, &$smarty) {
  global $PHP_SELF;
  
  $cop='<div class="copyright"><a style="text-decoration:none;" '.((basename($PHP_SELF)=='index.php' && $_SERVER['QUERY_STRING']=='')?'href="http://www.modified-shop.org" target="_blank"':'href="'.xtc_href_link('copyright.php').'"').'><span style="color:#B0347E;">mod</span><span style="color:#6D6D6D;">ified eCommerce Shopsoftware</span></a><span style="color:#555555;">' . '&nbsp;' . '&copy;' . date('Y') . '&nbsp;' . 'provides no warranty and is redistributable under the </span><a style="color:#555555;text-decoration:none;" href="http://www.fsf.org/licensing/licenses/gpl.txt" target="_blank">GNU General Public License</a></div>';

  // making output W3C-Conform: replace ampersands, rest is covered by the modified shopstat_functions.php  
  $tpl_output = preg_replace("/&(?!(amp;|&|#[0-9]+;|[a-z0-9]+;))/i", "&amp;", $tpl_output);

  // uncomment the next line to strip whitespaces (i.e. compress HTML)
  //$tpl_output =  preg_replace('!\s+!', ' ',$tpl_output);

  return $tpl_output.$cop;
}
?>
