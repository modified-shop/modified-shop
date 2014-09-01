<?php
/* --------------------------------------------------------------
   $Id: reviews.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(reviews.php,v 1.6 2002/02/06); www.oscommerce.com 
   (c) 2003	 nextcommerce (reviews.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Reviews');

define('TABLE_HEADING_PRODUCTS', 'Products');
define('TABLE_HEADING_RATING', 'Rating');
define('TABLE_HEADING_DATE_ADDED', 'Date Added');
define('TABLE_HEADING_ACTION', 'Action');

define('ENTRY_PRODUCT', 'Product:');
define('ENTRY_FROM', 'From:');
define('ENTRY_DATE', 'Date:');
define('ENTRY_REVIEW', 'Review:');
define('ENTRY_REVIEW_TEXT', '<small><font color="#ff0000"><b>NOTE:</b></font></small>&nbsp;HTML is not translated!&nbsp;');
define('ENTRY_RATING', 'Rating:');

define('TEXT_INFO_DELETE_REVIEW_INTRO', 'Are you sure you want to delete this review?');

define('TEXT_INFO_DATE_ADDED', 'Date Added:');
define('TEXT_INFO_LAST_MODIFIED', 'Last Modified:');
//BOF - DokuMan - 2010-02-15 - Change wrong constant-name
//define('TEXT_INFO_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');
define('TEXT_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');
//EOF - DokuMan - 2010-02-15 - Change wrong constant-name

define('TEXT_INFO_REVIEW_AUTHOR', 'Author:');
define('TEXT_INFO_REVIEW_RATING', 'Rating:');
define('TEXT_INFO_REVIEW_READ', 'Read:');
define('TEXT_INFO_REVIEW_SIZE', 'Size:');
define('TEXT_INFO_PRODUCTS_AVERAGE_RATING', 'Average Rating:');

define('TEXT_OF_5_STARS', '%s of 5 Stars!');
define('TEXT_GOOD', '<small><font color="#ff0000"><b>GOOD</b></font></small>');
define('TEXT_BAD', '<small><font color="#ff0000"><b>BAD</b></font></small>');
define('TEXT_INFO_HEADING_DELETE_REVIEW', 'Delete Review');
?>