<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified - community made shopping
   http://www.modified-shop.org

   Copyright (c) 2009 - 2012 modified
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com
   (c) 2003 nextcommerce (xtc_get_country_list.inc.php,v 1.5 2003/08/20); www.nextcommerce.org
   (c) 2006 XT-Commerce (xtc_get_country_list.inc.php 899 2005-04-29)

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
  include_once(DIR_FS_INC . 'xtc_draw_pull_down_menu.inc.php');
  include_once(DIR_FS_INC . 'xtc_get_countries.inc.php');

  function xtc_get_country_list($name, $selected = '', $parameters = '') {
    global $countries_names;
//    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
//    Probleme mit register_globals=off -> erstmal nur auskommentiert. Kann u.U. gelöscht werden.
    $countries = xtc_get_countriesList();

    // bof - hendrik 2011-07-17 - translations in current language
    //BOF - DokuMan - 2011-12-19 - precount for performance
    //for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
    $n=sizeof($countries);
    for ($i=0; $i<$n; $i++) {
    //EOF - DokuMan - 2011-12-19 - precount for performance

      //$countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
      $countries_array[] = array(
        'id' => $countries[$i]['countries_id'],
        'text' => isset($countries_names[$countries[$i]['countries_id']])? $countries_names[$countries[$i]['countries_id']]:$countries[$i]['countries_name']);
    }

    // if language-specific countries names defined
    if (is_array($countries_names)) {
      usort($countries_array, 'xtc_get_country_list_usort_compare'); // sort them
    }
    // eof - hendrik 2011-07-17 - translations in current language

    if (is_array($name)) {
      return xtc_draw_pull_down_menuNote($name, $countries_array, $selected, $parameters);
    }
    return xtc_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }


  // bof - hendrik 2011-07-17 - translations in current language
  // compare-function for usort in function xtc_get_country_list()
  function xtc_get_country_list_usort_compare($a, $b) {
    $at = strtolower(html_entity_decode($a['text']));     // decode an lowercase of names
    $bt = strtolower(html_entity_decode($b['text']));
    $at = strtr($at, COUNTRIES_NAMES_CONVERT_SORT_FROM, COUNTRIES_NAMES_CONVERT_SORT_TO); // specialchars remove
    $bt = strtr($bt, COUNTRIES_NAMES_CONVERT_SORT_FROM, COUNTRIES_NAMES_CONVERT_SORT_TO);
    return $at>$bt;                                       // compare strings
  }
  // eof - hendrik 2011-07-17 - translations in current language

 ?>