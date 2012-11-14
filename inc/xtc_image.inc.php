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
   (c) 2003 nextcommerce (xtc_image.inc.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2006 XT-Commerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/
 // include needed functions
 require_once(DIR_FS_INC . 'xtc_parse_input_field_data.inc.php');
 require_once(DIR_FS_INC . 'xtc_not_null.inc.php');
// The HTML image wrapper function
  function xtc_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if ( (empty($src) || ($src == DIR_WS_IMAGES) || ( $src == DIR_WS_THUMBNAIL_IMAGES))) {
      return false;
    }

    // alt is added to the img tag even if it is null to prevent browsers from outputting
    // the image filename as default
    //BOF - GTB - 2010-08-03 - Security Fix - Base
    $image = '<img src="' . xtc_parse_input_field_data(DIR_WS_BASE.$src, array('"' => '&quot;')) . '" alt="' . xtc_parse_input_field_data($alt, array('"' => '&quot;')) . '"';
    //$image = '<img src="' . xtc_parse_input_field_data($src, array('"' => '&quot;')) . '" alt="' . xtc_parse_input_field_data($alt, array('"' => '&quot;')) . '"';
    //EOF - GTB - 2010-08-03 - Security Fix - Base

    //BOF - Dokuman - 2009-10-14 - Remove title-attribute from images to avoid "keyword stuffing" (SEO), as it is identical to the alt-attribute
    /*
    if (xtc_not_null($alt)) {
      $image .= ' title="' . xtc_parse_input_field_data($alt, array('"' => '&quot;')) . '"';
    }
    */
    //EOF - Dokuman - 2009-10-14 - Remove title-attribute from images to avoid "keyword stuffing" (SEO), as it is identical to the alt-attribute

    if (defined('CONFIG_CALCULATE_IMAGE_SIZE') && (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && xtc_not_null($height)) {
          $ratio = $height / $image_size[1];
          //BOF - Dokuman - 2011-05-09 - make sure the values are rounded ints
          //$width = $image_size[0] * $ratio;
          $width = (int)($image_size[0] * $ratio);
          //EOF - Dokuman - 2011-05-09 - make sure the values are rounded ints
        } elseif (xtc_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          //BOF - Dokuman - 2011-05-09 - make sure the values are rounded ints
          //$height = $image_size[1] * $ratio;
          $height = (int)($image_size[1] * $ratio);
          //EOF - Dokuman - 2011-05-09 - make sure the values are rounded ints
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (defined('IMAGE_REQUIRED') && IMAGE_REQUIRED == 'false') { //DokuMan - 2010-02-26 - set undefined constant
        return false;
      }
    }

    if (xtc_not_null($width) && xtc_not_null($height)) {
      $image .= ' width="' . xtc_parse_input_field_data($width, array('"' => '&quot;')) . '" height="' . xtc_parse_input_field_data($height, array('"' => '&quot;')) . '"';
    }

    if (xtc_not_null($parameters)) {
      $image .= ' ' . $parameters;
    }
    $image .= ' />';
    return $image;
  }
 ?>