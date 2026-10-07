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
define('TITLE_SERVER_DATE', 'Server Date:');
define('TITLE_SERVER_UP_TIME', 'Server Up Time:');
define('TITLE_HTTP_SERVER', 'HTTP Server:');
define('TITLE_PHP_VERSION', 'PHP Version:');
define('TITLE_ZEND_VERSION', 'Zend:');
define('TITLE_DATABASE_HOST', 'Database Host:');
define('TITLE_DATABASE', 'Database:');
define('TITLE_DATABASE_DATE', 'Database Date:');
define('TITLE_SSL_VERSION', 'SSL version:');

define('TITLE_WEBSERVER_RULES', 'Web server rules:');
define('TEXT_WEBSERVER_RULES_HTACCESS', 'The file .htaccess exists in the shop root.');
define('TEXT_WEBSERVER_RULES_HTACCESS_MISSING', 'The file .htaccess is missing in the shop root. Rename _.htaccess to .htaccess. Only then do SEO URLs, the protection of hidden files such as .git and the cache rules take effect.');
define('TEXT_WEBSERVER_RULES_HTACCESS_SUBDIR', 'For the shop in a subdirectory, adapt RewriteBase and the paths of the ErrorDocument lines in the file.');
define('TEXT_WEBSERVER_RULES_NGINX', 'nginx does not read .htaccess files. Take over the following configuration into the nginx configuration. Check the path of the PHP-FPM socket and set up HTTPS. Then run nginx -t and reload nginx.');
define('TEXT_WEBSERVER_RULES_NGINX_MISSING', 'nginx does not read .htaccess files. The template _nginx.conf is missing in the shop root.');
define('TEXT_WEBSERVER_RULES_NGINX_SUBDIR', 'nginx does not read .htaccess files. The template _nginx.conf is meant for a shop in the domain root and must be adapted for the subdirectory.');
define('TEXT_WEBSERVER_RULES_UNKNOWN', 'The web server was not recognised. The access protection check shows whether its access rules apply.');
define('BUTTON_NGINX_CONFIG_DOWNLOAD', 'Download configuration');

define('TITLE_ACCESS_CHECK', 'Access protection:');
define('TEXT_ACCESS_CHECK_RUNNING', 'Checking ...');
define('TEXT_ACCESS_CHECK_OK', 'Files from protected directories are not publicly accessible.');
define('TEXT_ACCESS_CHECK_EXPOSED', '<strong>WARNING:</strong> These files from protected directories are publicly accessible. The access rules of the web server do not apply:');
define('TEXT_ACCESS_CHECK_FAILED', 'The check was not possible or not complete. Check again later.');
define('TEXT_ACCESS_CHECK_HINT_APACHE', 'Check whether the web server evaluates the .htaccess files (AllowOverride All). OpenLiteSpeed does not evaluate access rules from .htaccess files. If an nginx in front of Apache serves static files directly, as Plesk offers as an option, it bypasses the .htaccess files.');
define('TEXT_ACCESS_CHECK_HINT_NGINX', 'The rules from _nginx.conf are not active. Take them over into the nginx configuration and reload nginx.');
define('TEXT_ACCESS_CHECK_TIME', 'Checked:');
define('BUTTON_ACCESS_CHECK_REPEAT', 'Check again');
?>