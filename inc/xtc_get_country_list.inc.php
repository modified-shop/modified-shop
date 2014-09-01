<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_get_country_list.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(html_output.php,v 1.52 2003/03/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_get_country_list.inc.php,v 1.5 2003/08/20); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

// include needed functions
  include_once(DIR_FS_INC . 'xtc_draw_pull_down_menu.inc.php');
  include_once(DIR_FS_INC . 'xtc_get_countries.inc.php');
  
  function xtc_get_country_list($name, $selected = '', $parameters = '') {
//    $countries_array = array(array('id' => '', 'text' => PULL_DOWN_DEFAULT));
//    Probleme mit register_globals=off -> erstmal nur auskommentiert. Kann u.U. gelöscht werden.
    $countries = xtc_get_countriesList();

    for ($i=0, $n=sizeof($countries); $i<$n; $i++) {
      $countries_array[] = array('id' => $countries[$i]['countries_id'], 'text' => $countries[$i]['countries_name']);
    }
	if (is_array($name)) return xtc_draw_pull_down_menuNote($name, $countries_array, $selected, $parameters);
    return xtc_draw_pull_down_menu($name, $countries_array, $selected, $parameters);
  }
  
  
 ?>