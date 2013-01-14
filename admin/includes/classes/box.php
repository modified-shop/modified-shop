<?php
/* --------------------------------------------------------------
   $Id: box.php 2666 2012-02-23 11:38:17Z dokuman $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(box.php,v 1.5 2002/03/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (box.php,v 1.5 2003/08/18); www.nextcommerce.org

   Released under the GNU General Public License 
    
   Example usage:

   $heading = array();
   $heading[] = array('params' => 'class="menuBoxHeading"',
                      'text'  => BOX_HEADING_TOOLS,
                      'link'  => xtc_href_link(basename($PHP_SELF), xtc_get_all_get_params(array('selected_box')) . 'selected_box=tools'));

   $contents = array();
   $contents[] = array('text'  => SOME_TEXT);

   echo box::infoBoxSt($heading, $contents);   
   --------------------------------------------------------------
  */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

class box extends tableBlock {
	private static $heading = array ();
	private static $contents = array ();
	
	// cYbercOsmOnauT - 2011-02-07 - Fallback method for old calls
	public function infoBox($heading, $contents) {
		return self::infoBoxSt($heading, $contents);
	}
	
	public static function infoBoxSt($heading, $contents) {
		// Clean old values
		self::$heading = array ();
		self::$contents = array ();
		
		self::$table_row_parameters = 'class="infoBoxHeading"';
		self::$table_data_parameters = 'class="infoBoxHeading"';
		self::$heading = parent::constructor($heading);
		
		self::$table_row_parameters = '';
		self::$table_data_parameters = 'class="infoBoxContent"';
		self::$contents = parent::constructor($contents);
		
		return self::$heading . self::$contents;
	}
	
	public static function menuBox($heading, $contents) {
		self::$table_data_parameters = 'class="menuBoxHeading"';
		if (isset($heading[0]['link'])) {
			self::$table_data_parameters .= ' onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"';
			$heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
		}
		else {
			$heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
		}
		self::$heading = parent::constructor($heading);
		
		self::$table_data_parameters = 'class="menuBoxContent"';
		self::$contents = parent::constructor($contents);
		
		return self::$heading . self::$contents;
	}
}
?>