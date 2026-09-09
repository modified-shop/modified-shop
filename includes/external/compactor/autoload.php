<?php

/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// Vendored packages: matthiasmullie/minify 1.3.75 and matthiasmullie/path-converter 1.1.3.

spl_autoload_register(function ($class_path) {
    $prefix = 'MatthiasMullie\\';
    if (strncmp($class_path, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative_path = substr($class_path, strlen($prefix));
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_path) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
