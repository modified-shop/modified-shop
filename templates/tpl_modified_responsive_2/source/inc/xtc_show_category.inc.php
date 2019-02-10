<?php
  /* -----------------------------------------------------------------------------------------
   $Id: xtc_show_category.inc.php 4200 2013-01-10 19:47:11Z Tomcraft1980 $

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

  function xtc_show_category($counter, $oldlevel=1) {
    global $foo, $categories_string, $id, $cPath;  

    $level = $foo[$counter]['level']+1;

    //BOF +++ UL LI Verschachtelung  mit Quelltext Tab Einzügen +++    
    $ul = $tab = '';  
    for ($i = 1; $i <= $level; $i++) {
      $tab .= "\t";
    }    
    
    if ($level > $oldlevel) { //neue Unterebene
      $ul = "\n" . $tab. '<ul>'. "\n";
      $categories_string = rtrim($categories_string, "\n"); //Zeilenumbruch entfernen
      $categories_string = substr($categories_string, 0, strlen($categories_string) -5);  //letztes  </li>  entfernen  
    } elseif ($level < $oldlevel) { //zurück zur höheren Ebene
      $ul = close_ul_tags($level,$oldlevel);      
    }
    //EOF +++ UL LI Verschachtelung  mit Quelltext Tab Einzügen +++

    //BOF +++ Kategorien markieren +++
    $category_path = explode('_',$cPath); //Kategoriepfad in Array einlesen

    //Elternkategorie markieren
    $cat_active_parent = '';
    $in_path = in_array($counter, $category_path); //Testen, ob aktuelle Kategorie ID im Kategoriepfad enthalten ist
    if ($in_path) $cat_active_parent = " activeparent".$level; 
    
    //Aktive Kategorie markieren
    $cat_active = '';
    $this_category = array_pop($category_path); //Letzter Eintrag im Array ist die aktuelle Kategorie
    if ($this_category == $counter) $cat_active = " active".$level;
    //EOF +++ Kategorien markieren +++

    //BOF +++ Kategorie Linkerstellung +++  
    if (trim($categories_string == '')) $categories_string = "\n"; //Zeilenschaltung Codedarstellung  
    $categories_string .= $ul; //UL LI Versschachtelung
    $categories_string .= $tab; //Tabulator Codedarstellung
    $categories_string .= '<li class="level'.$level.$cat_active.$cat_active_parent.'">';
    $categories_string .= '<a href="'.$foo[$counter]['link'].'" title="'.str_replace(array('"', "'"), array('&quot;', '&apos;'), $foo[$counter]['name']).'">';
    $categories_string .= $foo[$counter]['name'];
    //Anzeige Anzahl der Produkte in Kategorie, für bessere Performance im Admin deaktivieren
    if (SHOW_COUNTS == 'true') {
      $products_in_category = xtc_count_products_in_category($counter);
      if ($products_in_category > 0) {
        $categories_string .= '&nbsp;(' . $products_in_category . ')';
      }
    }  
    $categories_string .= '</a></li>';
    $categories_string .= "\n"; //Zeilenschaltung Codedarstellung  
    //EOF  +++ Kategorie Linkerstellung +++

    //Nächste Kategorie
    if ($foo[$counter]['next_id']) {
      xtc_show_category($foo[$counter]['next_id'], $level);
    } else {  
      if ($level > 1) $categories_string .= close_ul_tags(1,$level);
      return;
    }
  }
?>