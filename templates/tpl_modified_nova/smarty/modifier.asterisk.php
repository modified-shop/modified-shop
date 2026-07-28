<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License 
  ---------------------------------------------------------------------------------------*/

  function smarty_modifier_asterisk($string) {  
    $string = str_replace(
      array(
        '<span class="inputRequirement">*</span>',
        '<span class="inputRequirement_textarea">*</span>',
      ),
      array(
        '<span class="inputRequirement">'.TEXT_ICON_ASTERISK.'</span>',
        '<span class="inputRequirement_textarea">'.TEXT_ICON_ASTERISK.'</span>',
      ),
      $string
    );

    return $string;
  }
