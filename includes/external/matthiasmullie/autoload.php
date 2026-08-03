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
    $prefix_map = array(
        'MatthiasMullie\\Minify\\' => __DIR__.'/minify/src/',
        'MatthiasMullie\\PathConverter\\' => __DIR__.'/path-converter/src/',
    );

    foreach ($prefix_map as $prefix => $base_dir) {
        if (strncmp($class_path, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relative_path = substr($class_path, strlen($prefix));
        $file = $base_dir.str_replace('\\', DIRECTORY_SEPARATOR, $relative_path).'.php';
        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});
