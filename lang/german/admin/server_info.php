<?php
/* --------------------------------------------------------------
   $Id: server_info.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(server_info.php,v 1.4 2002/03/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (server_info.php,v 1.4 2003/08/14); www.nextcommerce.org

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE', 'Server Information');

define('TITLE_SERVER_HOST', 'Server Host:');
define('TITLE_SERVER_OS', 'Server OS:');
define('TITLE_SERVER_DATE', 'Server Datum:');
define('TITLE_SERVER_UP_TIME', 'Server Up Time:');
define('TITLE_HTTP_SERVER', 'HTTP Server:');
define('TITLE_PHP_VERSION', 'PHP Version:');
define('TITLE_ZEND_VERSION', 'Zend:');
define('TITLE_DATABASE_HOST', 'Datenbank Host:');
define('TITLE_DATABASE', 'Datenbank:');
define('TITLE_DATABASE_DATE', 'Datenbank Datum:');
define('TITLE_SSL_VERSION', 'SSL Version:');

define('TITLE_WEBSERVER_RULES', 'Webserver-Regeln:');
define('TEXT_WEBSERVER_RULES_HTACCESS', 'Die Datei .htaccess im Hauptverzeichnis des Shops ist vorhanden.');
define('TEXT_WEBSERVER_RULES_HTACCESS_MISSING', 'Im Hauptverzeichnis des Shops fehlt die Datei .htaccess. Benennen Sie _.htaccess in .htaccess um. Erst dann greifen SEO-URLs, der Schutz versteckter Dateien wie .git und die Cache-Regeln.');
define('TEXT_WEBSERVER_RULES_HTACCESS_SUBDIR', 'Passen Sie f&uuml;r den Shop im Unterverzeichnis in der Datei RewriteBase und die Pfade der ErrorDocument-Zeilen an.');
define('TEXT_WEBSERVER_RULES_NGINX', 'nginx liest keine .htaccess-Dateien. &Uuml;bernehmen Sie die folgende Konfiguration in die nginx-Konfiguration. Pr&uuml;fen Sie den Pfad des PHP-FPM-Sockets und richten Sie HTTPS ein. F&uuml;hren Sie danach nginx -t aus und laden Sie nginx neu.');
define('TEXT_WEBSERVER_RULES_NGINX_MISSING', 'nginx liest keine .htaccess-Dateien. Die Vorlage _nginx.conf fehlt im Hauptverzeichnis des Shops.');
define('TEXT_WEBSERVER_RULES_NGINX_SUBDIR', 'nginx liest keine .htaccess-Dateien. Die Vorlage _nginx.conf gilt f&uuml;r einen Shop im Domain-Root und muss f&uuml;r das Unterverzeichnis angepasst werden.');
define('TEXT_WEBSERVER_RULES_UNKNOWN', 'Der Webserver wurde nicht erkannt. Ob seine Zugriffsregeln greifen, zeigt die Pr&uuml;fung des Zugriffsschutzes.');
define('BUTTON_NGINX_CONFIG_DOWNLOAD', 'Konfiguration herunterladen');

define('TITLE_ACCESS_CHECK', 'Zugriffsschutz:');
define('TEXT_ACCESS_CHECK_RUNNING', 'Wird gepr&uuml;ft ...');
define('TEXT_ACCESS_CHECK_OK', 'Dateien aus gesch&uuml;tzten Verzeichnissen sind nicht &ouml;ffentlich abrufbar.');
define('TEXT_ACCESS_CHECK_EXPOSED', '<strong>WARNUNG:</strong> Diese Dateien aus gesch&uuml;tzten Verzeichnissen sind &ouml;ffentlich abrufbar. Die Zugriffsregeln des Webservers greifen nicht:');
define('TEXT_ACCESS_CHECK_FAILED', 'Die Pr&uuml;fung war nicht m&ouml;glich.');
define('TEXT_ACCESS_CHECK_HINT_APACHE', 'Pr&uuml;fen Sie, ob der Webserver die .htaccess-Dateien auswertet (AllowOverride All). OpenLiteSpeed wertet Zugriffsregeln aus .htaccess-Dateien nicht aus. Liefert ein vorgeschalteter nginx statische Dateien direkt aus, wie es Plesk optional anbietet, umgeht er die .htaccess-Dateien.');
define('TEXT_ACCESS_CHECK_HINT_NGINX', 'Die Regeln aus _nginx.conf sind nicht aktiv. &Uuml;bernehmen Sie sie in die nginx-Konfiguration und laden Sie nginx neu.');
define('TEXT_ACCESS_CHECK_TIME', 'Gepr&uuml;ft:');
define('BUTTON_ACCESS_CHECK_REPEAT', 'Erneut pr&uuml;fen');
?>