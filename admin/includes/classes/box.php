<?php
/* --------------------------------------------------------------
   $Id: box.php 2666 2012-02-23 11:38:17Z dokuman $ 

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
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

   $box = new box;
   echo $box->infoBox($heading, $contents);   
   --------------------------------------------------------------
*/
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

  class box extends tableBlock {
    public function __construct() {
      $this->heading = array();
      $this->contents = array();
    }

    public function infoBox($heading, $contents) {
      if (isset($heading[0]['text'])) {
        $heading[0]['text'] = '<div class="infoBoxHeadingTitle">'.$heading[0]['text'].'</div>';
      }
      
      $this->table_row_parameters = 'class="infoBoxHeading"';
      $this->table_data_parameters = 'class="infoBoxHeading"';
      $this->heading = $this->createBlock($heading);

      $this->table_row_parameters = 'class="infoBoxContent"';
      $this->table_data_parameters = 'class="infoBoxContent"';
      $this->contents = $this->createBlock($contents);

      return $this->heading . $this->contents;
    }

    public function menuBox($heading, $contents) {
      $this->table_data_parameters = 'class="menuBoxHeading"';
      if ($heading[0]['link']) {
        $this->table_data_parameters .= ' onmouseover="this.style.cursor=\'pointer\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"';
        $heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
      } else {
        $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
      }
      $this->heading = $this->createBlock($heading);

      $this->table_data_parameters = 'class="menuBoxContent"';
      $this->contents = $this->createBlock($contents);

      return $this->heading . $this->contents;
    }
  }
?>