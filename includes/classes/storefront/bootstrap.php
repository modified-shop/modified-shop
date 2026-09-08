<?php

$storefrontNamespacePrefix = 'Modified\\Storefront\\';
$storefrontSourceDirectory = __DIR__ . '/src/';

spl_autoload_register(static function (string $class) use ($storefrontNamespacePrefix, $storefrontSourceDirectory): void {
    if (!str_starts_with($class, $storefrontNamespacePrefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($storefrontNamespacePrefix));
    $file = $storefrontSourceDirectory . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once __DIR__ . '/Template.php';
