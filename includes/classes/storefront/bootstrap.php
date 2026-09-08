<?php

(static function (): void {
    $selectSmartyEngine = static function (): string {
        $selectedEngine = defined('TEMPLATE_ENGINE') ? TEMPLATE_ENGINE : 'smarty_4';
        if (!is_string($selectedEngine) || !in_array($selectedEngine, ['smarty_4', 'smarty_5'], true)) {
            throw new RuntimeException("TEMPLATE_ENGINE must be defined as 'smarty_4' or 'smarty_5'.");
        }

        return $selectedEngine;
    };

    $bootstrapSmartyEngine = static function (string $selectedEngine): void {
        $engineStateKey = '__modified_storefront_template_engine';
        $loadedEngine = $GLOBALS[$engineStateKey] ?? null;

        if ($loadedEngine !== null && $loadedEngine !== $selectedEngine) {
            throw new RuntimeException('The selected Smarty version conflicts with the engine already loaded by the storefront bootstrap.');
        }

        if ($loadedEngine !== null) {
            return;
        }

        if (
            class_exists('Smarty', false)
            || class_exists('Smarty\\Smarty', false)
            || function_exists('smarty_ucfirst_ascii')
        ) {
            throw new RuntimeException('A Smarty engine was loaded before the central storefront bootstrap.');
        }

        $smartyRoot = dirname(__DIR__, 2) . '/external/smarty/';
        $smartyEntry = $selectedEngine === 'smarty_5'
            ? $smartyRoot . 'smarty_5/libs/Smarty.class.php'
            : $smartyRoot . 'smarty_4/Smarty.class.php';

        if (!is_file($smartyEntry) || !is_readable($smartyEntry)) {
            throw new RuntimeException(sprintf(
                'The %s entry file does not exist or is not readable: %s',
                $selectedEngine,
                $smartyEntry
            ));
        }

        require_once $smartyEntry;

        if ($selectedEngine === 'smarty_5' && !class_exists('Smarty\\Smarty')) {
            throw new RuntimeException('The Smarty 5 entry file did not provide the expected vendor class Smarty\\Smarty.');
        }
        if ($selectedEngine === 'smarty_4' && !class_exists('Smarty', false)) {
            throw new RuntimeException('The Smarty 4 entry file did not provide the expected global vendor class Smarty.');
        }

        $GLOBALS[$engineStateKey] = $selectedEngine;
    };

    $registerStorefrontAutoloader = static function (): void {
        $autoloaderStateKey = '__modified_storefront_bootstrap_autoloader';
        $storefrontAutoloader = $GLOBALS[$autoloaderStateKey] ?? null;

        if (!is_callable($storefrontAutoloader)) {
            $storefrontNamespacePrefix = 'Modified\\Storefront\\';
            $storefrontSourceDirectory = __DIR__ . '/src/';
            $storefrontAutoloader = static function (string $class) use (
                $storefrontNamespacePrefix,
                $storefrontSourceDirectory
            ): void {
                if (!str_starts_with($class, $storefrontNamespacePrefix)) {
                    return;
                }

                $relativeClass = substr($class, strlen($storefrontNamespacePrefix));
                $file = $storefrontSourceDirectory . str_replace('\\', '/', $relativeClass) . '.php';

                if (is_file($file)) {
                    require_once $file;
                }
            };
            $GLOBALS[$autoloaderStateKey] = $storefrontAutoloader;
        }

        if (!in_array($storefrontAutoloader, spl_autoload_functions() ?: [], true)) {
            spl_autoload_register($storefrontAutoloader);
        }
    };

    $loadStorefrontEntryPoints = static function (string $selectedEngine): void {
        if ($selectedEngine === 'smarty_5') {
            require_once __DIR__ . '/Smarty.php';
        }

        require_once __DIR__ . '/Template.php';
    };

    $selectedEngine = $selectSmartyEngine();
    $bootstrapSmartyEngine($selectedEngine);
    $registerStorefrontAutoloader();
    $loadStorefrontEntryPoints($selectedEngine);
})();
