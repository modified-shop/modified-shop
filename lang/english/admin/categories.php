<?php
/* --------------------------------------------------------------
   $Id: categories.php 3392 2012-08-06 12:45:26Z web28 $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(categories.php,v 1.22 2002/08/17); www.oscommerce.com
   (c) 2003	 nextcommerce (categories.php,v 1.10 2003/08/14); www.nextcommerce.org
   (c) 2006 xt:Commerce; www.xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------*/
 
// BOF - Tomcraft - 2009-11-02 - Admin language tabs
define('TEXT_EDIT_STATUS', 'Status active');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs
define('HEADING_TITLE', 'Category');
define('HEADING_TITLE_SEARCH', 'Search: ');
define('HEADING_TITLE_GOTO', 'Go To:');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_CATEGORIES_PRODUCTS', 'Categories / Products');
define('TABLE_HEADING_ACTION', 'Action');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_STARTPAGE', 'TOP');
define('TABLE_HEADING_STOCK','Stock Warning');
define('TABLE_HEADING_SORT','Sort');
define('TABLE_HEADING_EDIT','Edit');
// BOF - Tomcraft - 2010-04-07 - Added definition for products model
define('TABLE_HEADING_PRODUCTS_MODEL','Products Model');
// EOF - Tomcraft - 2010-04-07 - Added definition for products model

// BOF - Hendrik - 2010-08-11 - Thumbnails in admin products list
define('TABLE_HEADING_IMAGE','Image');
// EOF - Hendrik - 2010-08-11 - Thumbnails in admin products list

define('TEXT_ACTIVE_ELEMENT','Active Element');
define('TEXT_INFORMATIONS','Informations');
define('TEXT_MARKED_ELEMENTS','Marked Elements');
define('TEXT_INSERT_ELEMENT','New Element');

define('TEXT_WARN_MAIN','Main');
define('TEXT_NEW_PRODUCT', 'New Product in &quot;%s&quot;');
define('TEXT_CATEGORIES', 'Categories:');
define('TEXT_PRODUCTS', 'Products:');
define('TEXT_PRODUCTS_PRICE_INFO', 'Price:');
define('TEXT_PRODUCTS_TAX_CLASS', 'Tax Class:');
define('TEXT_PRODUCTS_AVERAGE_RATING', 'Average Rating:');
define('TEXT_PRODUCTS_QUANTITY_INFO', 'Quantity:');
define('TEXT_PRODUCTS_DISCOUNT_ALLOWED_INFO', 'Max. allowed Discount:');
define('TEXT_DATE_ADDED', 'Date Added:');
define('TEXT_DATE_AVAILABLE', 'Date Available:');
define('TEXT_LAST_MODIFIED', 'Last Modified:');
define('TEXT_IMAGE_NONEXISTENT', 'IMAGE DOES NOT EXIST');
define('TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS', 'Please insert a new category or product in <br />&nbsp;<br /><b>%s</b>');
define('TEXT_PRODUCT_MORE_INFORMATION', 'For more information, please visit this products <a href="http://%s" target="_blank"><u>webpage</u></a>.');
define('TEXT_PRODUCT_DATE_ADDED', 'This product was added to our catalog on %s.');
define('TEXT_PRODUCT_DATE_AVAILABLE', 'This product will be in stock on %s.');

define('TEXT_EDIT_INTRO', 'Please make any necessary changes');
define('TEXT_EDIT_CATEGORIES_ID', 'Category ID:');
define('TEXT_EDIT_CATEGORIES_NAME', 'Category Name:');
define('TEXT_EDIT_CATEGORIES_HEADING_TITLE', 'Category Heading:');
define('TEXT_EDIT_CATEGORIES_DESCRIPTION', 'Category Description:');
define('TEXT_EDIT_CATEGORIES_IMAGE', 'Category Image:');

define('TEXT_EDIT_SORT_ORDER', 'Sort Order:');

define('TEXT_INFO_COPY_TO_INTRO', 'Please choose a new category you wish to copy this product to');
define('TEXT_INFO_CURRENT_CATEGORIES', 'Current Categories:');

define('TEXT_INFO_HEADING_NEW_CATEGORY', 'New Category');
define('TEXT_INFO_HEADING_EDIT_CATEGORY', 'Edit Category');
define('TEXT_INFO_HEADING_DELETE_CATEGORY', 'Delete Category');
define('TEXT_INFO_HEADING_MOVE_CATEGORY', 'Move Category');
define('TEXT_INFO_HEADING_DELETE_PRODUCT', 'Delete Product');
define('TEXT_INFO_HEADING_MOVE_PRODUCT', 'Move Product');
define('TEXT_INFO_HEADING_COPY_TO', 'Copy To');
define('TEXT_INFO_HEADING_MOVE_ELEMENTS', 'Move Elements');
define('TEXT_INFO_HEADING_DELETE_ELEMENTS', 'Delete Elements');

define('TEXT_DELETE_CATEGORY_INTRO', 'Are you sure you want to delete this category?');
define('TEXT_DELETE_PRODUCT_INTRO', 'Are you sure you want to permanently delete this product?');

define('TEXT_DELETE_WARNING_CHILDS', '<b>WARNING:</b> There are %s (Child-)Categories still linked to this category!');
define('TEXT_DELETE_WARNING_PRODUCTS', '<b>WARNING:</b> There are %s products still linked to this category!');

define('TEXT_MOVE_WARNING_CHILDS', '<b>Info:</b> There are %s (Child-)Categories still linked to this category!');
define('TEXT_MOVE_WARNING_PRODUCTS', '<b>Info:</b> There are %s products still linked to this category!');

define('TEXT_MOVE_PRODUCTS_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_MOVE_CATEGORIES_INTRO', 'Please select which category you wish <b>%s</b> to reside in');
define('TEXT_MOVE', 'Move <b>%s</b> to:');
define('TEXT_MOVE_ALL', 'Move all to:');

define('TEXT_NEW_CATEGORY_INTRO', 'Please fill out the following information for the new category.');
define('TEXT_CATEGORIES_NAME', 'Category Name:');
define('TEXT_CATEGORIES_IMAGE', 'Category Image:');

define('TEXT_META_TITLE', 'Meta Title:');
define('TEXT_META_DESCRIPTION', 'Meta Description:');
define('TEXT_META_KEYWORDS', 'Meta Keywords:');

define('TEXT_SORT_ORDER', 'Sort Order:');

define('TEXT_PRODUCTS_STATUS', 'Products Status:');
define('TEXT_PRODUCTS_STARTPAGE', 'Show on startpage:');
define('TEXT_PRODUCTS_STARTPAGE_YES', 'Yes');
define('TEXT_PRODUCTS_STARTPAGE_NO', 'No');
define('TEXT_PRODUCTS_STARTPAGE_SORT', 'Sort order (startpage):');
define('TEXT_PRODUCTS_DATE_AVAILABLE', 'Date Available:');
// BOF - Hetfield - 2010-01-28 - Changing product available in correctly names for status
define('TEXT_PRODUCT_AVAILABLE', 'Active');
define('TEXT_PRODUCT_NOT_AVAILABLE', 'Deactivated');
// EOF - Hetfield - 2010-01-28 - Changing product available in correctly names for status
define('TEXT_PRODUCTS_MANUFACTURER', 'Products Manufacturer:');
define('TEXT_PRODUCTS_MANUFACTURER_MODEL', 'Manufacturer model no.:');
define('TEXT_PRODUCTS_NAME', 'Products Name:');
define('TEXT_PRODUCTS_DESCRIPTION', 'Products Description:');
define('TEXT_PRODUCTS_QUANTITY', 'Products Quantity:');
define('TEXT_PRODUCTS_MODEL', 'Products Model:');
define('TEXT_PRODUCTS_IMAGE', 'Products Image:');
define('TEXT_PRODUCTS_URL', 'Products URL:');
define('TEXT_PRODUCTS_URL_WITHOUT_HTTP', '<small>(without leading http://)</small>');
define('TEXT_PRODUCTS_PRICE', 'Product price:');
define('TEXT_PRODUCTS_WEIGHT', 'Product weight:');
define('TEXT_PRODUCTS_EAN','Barcode/EAN');
define('TEXT_PRODUCT_LINKED_TO','Linked to:');
define('TEXT_DELETE', 'Delete');
define('EMPTY_CATEGORY', 'Empty Category');

define('TEXT_HOW_TO_COPY', 'Copy Method:');
define('TEXT_COPY_AS_LINK', 'Link product');
define('TEXT_COPY_AS_DUPLICATE', 'Duplicate product');

define('ERROR_CANNOT_LINK_TO_SAME_CATEGORY', 'Error: Can not link products in the same directory.');
define('ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Error: Catalog images directory is not writeable: ' . DIR_FS_CATALOG_IMAGES);
define('ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'Error: Catalog images directory does not exist: ' . DIR_FS_CATALOG_IMAGES);

define('TEXT_PRODUCTS_DISCOUNT_ALLOWED','Max. allowed Discount:');
define('HEADING_PRICES_OPTIONS','<b>Price options</b>');
define('HEADING_PRODUCT_IMAGES','<b>Products Images</b>');
define('TEXT_PRODUCTS_WEIGHT_INFO','<small>(kg)</small>');
define('TEXT_PRODUCTS_SHORT_DESCRIPTION','Short description:');
define('TEXT_PRODUCTS_KEYWORDS', 'Extra words for Search:');
define('TXT_STK','Pcs: ');
define('TXT_PRICE','a :');
define('TXT_NETTO','Net price: ');
define('TXT_STAFFELPREIS','Graduated Price');

define('HEADING_PRODUCTS_MEDIA','<b>Products Media</b>');
define('TABLE_HEADING_PRICE','Price');

define('TEXT_FSK18','FSK 18:');
define('TEXT_CHOOSE_INFO_TEMPLATE_CATEGORIE','Template for Category Listing');
define('TEXT_CHOOSE_INFO_TEMPLATE_LISTING','Template for Product Listing');
// BOF - Tomcraft - 2009-11-02 - Admin language tabs
//define('TEXT_PRODUCTS_SORT','Sorting:');
define('TEXT_PRODUCTS_SORT','Sort order:');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs
define('TEXT_EDIT_PRODUCT_SORT_ORDER','Product Sorting');
define('TXT_PRICES','Price');
define('TXT_NAME','Product name');
define('TXT_ORDERED','Products ordered');
// BOF - Tomcraft - 2009-11-02 - Admin language tabs
define('TXT_SORT','Sort order');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs
define('TXT_WEIGHT','Weight');
define('TXT_QTY','On Stock');
// BOF - Tomcraft - 2009-09-12 - add option to sort by date and products model
define('TXT_DATE','Release date');
define('TXT_MODEL','Products Model');
// EOF - Tomcraft - 2009-09-12 - add option to sort by date and products model

define('TEXT_MULTICOPY','Multiple');
define('TEXT_MULTICOPY_DESC','Copy elements into following categories (If one box selected, Single settings will be ignored.)');
define('TEXT_SINGLECOPY','Single');
define('TEXT_SINGLECOPY_DESC','Copy elements into following category');
define('TEXT_SINGLECOPY_CATEGORY','Category:');

define('TEXT_PRODUCTS_VPE','Unit');
define('TEXT_PRODUCTS_VPE_VISIBLE','Show Unit Price:');
define('TEXT_PRODUCTS_VPE_VALUE',' Value:');

define('CROSS_SELLING','Cross selling for article');
define('CROSS_SELLING_SEARCH','Search product:<br/><small><i>enter productnr.</i></small>');
define('BUTTON_EDIT_CROSS_SELLING','Cross selling');
define('HEADING_DEL','delete');
define('HEADING_ADD','Add?');
define('HEADING_GROUP','Group');
define('HEADING_SORTING','sorting');
define('HEADING_MODEL','model');
define('HEADING_NAME','article');
define('HEADING_CATEGORY','category');

// BOF - Tomcraft - 2009-11-02 - Admin language tabs
define('TEXT_SORT_ASC','ascending');
define('TEXT_SORT_DESC','descending');
// EOF - Tomcraft - 2009-11-02 - Admin language tabs

// BOF - Tomcraft - 2009-11-06 - Use variable TEXT_PRODUCTS_DATE_FORMAT
define('TEXT_PRODUCTS_DATE_FORMAT', 'JJJJ-MM-TT');
// EOF - Tomcraft - 2009-11-06 - Use variable TEXT_PRODUCTS_DATE_FORMAT

// BOF - web28 - 2010-08-03 - add metatags max charakters info
define('TEXT_CHARACTERS','characters');
// EOF - web28 - 2010-08-03 - add metatags max charakters info

define('TEXT_ATTRIBUTE_COPY', 'Product attributes also copy');
define('TEXT_ATTRIBUTE_COPY_INFO', 'Product attributes also copy <br/> Only single-copy (1 item) recommended ');

define('TEXT_PRODUCTS_ORDER_DESCRIPTION','Order description');

define('TEXT_HOW_TO_LINK', '<b>Page view after copying / link</b>');
define('TEXT_HOW_TO_LINK_INFO', 'Item entry screen <br/> (For multiple items to last in the list)');
?>