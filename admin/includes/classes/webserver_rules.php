<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  class webserver_rules {

    /**
     * Name of the running web server.
     *
     * @return string apache, litespeed, nginx or an empty string for other servers
     */
    function server() {
      $software = isset($_SERVER['SERVER_SOFTWARE']) ? strtolower((string)$_SERVER['SERVER_SOFTWARE']) : '';

      foreach (array('litespeed', 'nginx', 'apache') as $server) {
        if (strpos($software, $server) !== false) {
          return $server;
        }
      }

      return '';
    }

    /**
     * Shipped empty files in protected directories that a browser must not be able to load.
     *
     * Apache logs every denied request as an error that fail2ban may count, so it only gets
     * one denied probe; the backups answer with 401 there, which is not logged.
     *
     * @return array urls
     */
    function check_files() {
      $files = array(
        'log/index.html',
        DIR_ADMIN.'backups/index.html',
      );
      if (!in_array($this->server(), array('apache', 'litespeed'))) {
        $files[] = 'templates_c/index.html';
      }

      $check_files = array();
      foreach ($files as $file) {
        if (is_file(DIR_FS_CATALOG.$file)) {
          $check_files[] = DIR_WS_CATALOG.$file;
        }
      }

      return $check_files;
    }

    /**
     * nginx configuration from _nginx.conf with the values of this shop.
     *
     * The template is a valid configuration, so only its documented example values are replaced.
     *
     * @return string|false false without template or for a shop in a subdirectory
     */
    function nginx_config() {
      if (!is_file(DIR_FS_CATALOG.'_nginx.conf') || DIR_WS_CATALOG != '/') {
        return false;
      }

      $config = file_get_contents(DIR_FS_CATALOG.'_nginx.conf');
      if ($config === false) {
        return false;
      }

      $replace = array(
        'root /var/www/modified-shop;' => 'root "'.rtrim(DIR_FS_CATALOG, '/').'";',
        'unix:/run/php/php8.5-fpm.sock' => 'unix:/run/php/php'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'-fpm.sock',
        'admin(?:/|$)' => preg_quote(trim(DIR_ADMIN, '/')).'(?:/|$)',
      );

      // keep the example host rather than writing an invalid server_name
      $host = parse_url((HTTPS_CATALOG_SERVER != '') ? HTTPS_CATALOG_SERVER : HTTP_CATALOG_SERVER, PHP_URL_HOST);
      if (is_string($host) && $host != '') {
        $replace['server_name shop.example.com;'] = 'server_name '.$host.';';
      }

      return strtr($config, $replace);
    }
  }
