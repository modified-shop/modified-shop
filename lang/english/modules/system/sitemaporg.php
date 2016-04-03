<?php
/* -----------------------------------------------------------------------------------------
   
   $Id: sitemaporg.php 
   XML-Sitemap.org for xt:Commerce SP2.1a
   by Mathis Klooss
   V1.2
   -----------------------------------------------------------------------------------------
      Original Script:
   $Id: gsitemaps.php 
   Google Sitemaps by hendrik.koch@gmx.de
   V1.1 August 2006
   -----------------------------------------------------------------------------------------
   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.28 2003/02/14); www.oscommerce.com 
   (c) 2003	 nextcommerce (invoice.php,v 1.6 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SITEMAPORG_TEXT_DESCRIPTION', 'The standard definition can be found here: <a href="http://www.sitemaps.org/" target="_blank">www.sitemap.org</a>');
define('MODULE_SITEMAPORG_TEXT_TITLE', 'XML Sitemap.org');
define('MODULE_SITEMAPORG_FILE_TITLE' , '<hr />Filename');
define('MODULE_SITEMAPORG_FILE_DESC' , 'Enter a filename for the sitemap, if you want it to be saved locally<br />(Directory "export/")');
define('MODULE_SITEMAPORG_STATUS_DESC','Module status');
define('MODULE_SITEMAPORG_STATUS_TITLE','Status');
define('MODULE_SITEMAPORG_CHANGEFREQ_TITLE','Changing Frequency');
define('MODULE_SITEMAPORG_CHANGEFREQ_DESC','The frequency the content is likely to be changed');
define('MODULE_SITEMAPORG_ROOT_TITLE', '<hr /><b>Installation in Shoproot?</b>');
define('MODULE_SITEMAPORG_ROOT_DESC', 'Save the sitemap file in root directory?');
define('MODULE_SITEMAPORG_PRIORITY_LIST_TITLE', '<b>Priority for List</b>');
define('MODULE_SITEMAPORG_PRIORITY_LIST_DESC', '');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_TITLE', '<b>Priority for products</b>');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_DESC', '');
define('MODULE_SITEMAPORG_GZIP_TITLE', '<b>Use gzip compression?</b>');
define('MODULE_SITEMAPORG_GZIP_DESC', 'File extension ".gz" is extended automatically added!');
define('MODULE_SITEMAPORG_EXPORT_TITLE', '<hr /><b>Download?</b>');
define('MODULE_SITEMAPORG_EXPORT_DESC', 'Would you like to download the file?');
define('MODULE_SITEMAPORG_YAHOO_TITLE', 'YahooID');
define('MODULE_SITEMAPORG_YAHOO_DESC','Enter your Yahoo ID. This is needed to transmit the sitemap to Yahoo.');

?>