<?php
  /* -----------------------------------------------------------------------------------------
   $Id: xtc_show_category.inc.php 3072 2012-06-18 15:01:13Z hhacker $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.23 2002/11/12); www.oscommerce.com
   (c) 2003   nextcommerce (xtc_show_category.inc.php,v 1.4 2003/08/13); www.nextcommerce.org 
   (c) 2010  web28 (xtc_show_category.inc.php, v 2.1 2010/11/12); www.rpa-com.de

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/   

  function xtc_show_content($counter, $oldlevel=1) {
    global $content_array, $content_string, $coid, $coPath;  

    $level = $content_array[$counter]['level']+1;

    //BOF +++ UL LI Verschachtelung  mit Quelltext Tab Einzügen +++    
    $ul = $tab = '';  
    for ($i = 1; $i <= $level; $i++) {
      $tab .= "\t";
    }    
    
    if ($level > $oldlevel) { //neue Unterebene
      $ul = "\n" . $tab. '<ul>'. "\n";
      $content_string = rtrim($content_string, "\n"); //Zeilenumbruch entfernen
      $content_string = substr($content_string, 0, strlen($content_string) -5);  //letztes  </li>  entfernen  
    } elseif ($level < $oldlevel) { //zurück zur höheren Ebene
      $ul = close_ul_tags($level,$oldlevel);      
    }
    //EOF +++ UL LI Verschachtelung  mit Quelltext Tab Einzügen +++

    //BOF +++ Content markieren +++
    $content_path = explode('_',$coPath); //Contentpfad in Array einlesen

    //Elterncontent markieren
    $content_active_parent = '';
    $in_path = in_array($counter, $content_path); //Testen, ob aktuelle Content ID im Contentpfad enthalten ist
    if ($in_path) $content_active_parent = " activeparent".$level; 
    
    //Aktiven Content markieren
    $content_active = '';
    $this_content = array_pop($content_path); //Letzter Eintrag im Array ist der aktuelle Content
    if ($this_content == $counter) $content_active = " active".$level;
    //EOF +++ Content markieren +++

    //BOF +++ Content Linkerstellung +++  
    if (trim($content_string == '')) $content_string = "\n"; //Zeilenschaltung Codedarstellung  
    $content_string .= $ul; //UL LI Versschachtelung
    $content_string .= $tab; //Tabulator Codedarstellung
    $content_string .= '<li class="level'.$level.$content_active.$content_active_parent.'">';
    $content_string .= '<a href="'.xtc_href_link(FILENAME_CONTENT, 'coID='.$content_array[$counter]['coID']).'" title="'. $content_array[$counter]['name'] . '">';
    $content_string .= $content_array[$counter]['name'];
    $content_string .= '</a></li>';
    $content_string .= "\n"; //Zeilenschaltung Codedarstellung  
    //EOF  +++ Content Linkerstellung +++

    //Nächste Content
    if ($content_array[$counter]['next_id']) {
      xtc_show_content($content_array[$counter]['next_id'], $level);
    } else {  
      if ($level > 1) $content_string .= close_ul_tags(1,$level);
      return;
    }
  }
?>