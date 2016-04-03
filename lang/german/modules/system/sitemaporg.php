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

define('MODULE_SITEMAPORG_TEXT_DESCRIPTION', 'Die Standard Definition finden Sie hier: <a href="http://www.sitemaps.org/" target="_blank">www.sitemap.org</a>');
define('MODULE_SITEMAPORG_TEXT_TITLE', 'XML Sitemap.org');
define('MODULE_SITEMAPORG_FILE_TITLE' , '<hr />Dateiname');
define('MODULE_SITEMAPORG_FILE_DESC' , 'Geben Sie einen Dateinamen ein, falls die Exportadatei am Server gespeichert werden soll.<br />(Verzeichnis "export/")');
define('MODULE_SITEMAPORG_STATUS_DESC','Modulstatus');
define('MODULE_SITEMAPORG_STATUS_TITLE','Status');
define('MODULE_SITEMAPORG_CHANGEFREQ_TITLE','Wechsel Frequenz');
define('MODULE_SITEMAPORG_CHANGEFREQ_DESC','Die H&auml;ufigkeit, mit der sich die Seite voraussichtlich &auml;ndern wird.');
define('MODULE_SITEMAPORG_ROOT_TITLE', '<hr /><b>Installation im Root?</b>');
define('MODULE_SITEMAPORG_ROOT_DESC', 'Soll die Sitemap-Datei gleich im Rootverzeichnis abgelegt werden?');
define('MODULE_SITEMAPORG_PRIORITY_LIST_TITLE', '<b>Priorit&auml;t f&uuml;r die Liste</b>');
define('MODULE_SITEMAPORG_PRIORITY_LIST_DESC', '');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_TITLE', '<b>Priorit&auml;t f&uuml;r die Produkte</b>');
define('MODULE_SITEMAPORG_PRIORITY_PRODUCT_DESC', '');
define('MODULE_SITEMAPORG_GZIP_TITLE', '<b>gzip Komprimierung nutzen?</b>');
define('MODULE_SITEMAPORG_GZIP_DESC', 'Die Endung ".gz" wird automatisch ans Ende der Datei gesetzt!');
define('MODULE_SITEMAPORG_EXPORT_TITLE', '<hr /><b>Herunterladen?</b>');
define('MODULE_SITEMAPORG_EXPORT_DESC', 'M&ouml;chten Sie die Datei herunterladen?');
define('MODULE_SITEMAPORG_YAHOO_TITLE', 'YahooID');
define('MODULE_SITEMAPORG_YAHOO_DESC','Geben Sie hier Ihre die Yahoo ID an! Diese wird ben&ouml;tigt, um Yahoo die Sitemap mitzuteilen');

?>