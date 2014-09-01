<?php
/* --------------------------------------------------------------
   $Id: new_attributes_config.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(new_attributes_config); www.oscommerce.com 
   (c) 2003	 nextcommerce (new_attributes_config.php,v 1.5 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------
   Third Party contributions:
   New Attribute Manager v4b				Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/ 
defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  // Change to your default language ID if it differs.
  $languageFilter = '1';
  
  // If you have Options Type Feature ver. 2.02.MS1 by countezero installed - make this = "1"
  $optionTypeInstalled = '0';

  // If you have "Attributes Sorter & Copier v5.1 with weight" 
  // by Linda McGrath installed - make this = "1"
  $optionSortCopyInstalled = '0';

  // If you have Option Type Feature v-1.4 by Chandra Roukema installed - make this = "1"
  $optionTypeTextInstalled = '0';

  // If you have Option Type Feature v-1.4 by Chandra Roukema installed - set this to your
  // PRODUCTS_OPTIONS_VALUE_TEXT_ID value ( usually "0" )
  $optionTypeTextInstalledID = '0';
?>