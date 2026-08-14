<?php

(static function (): void {
    $selectSmartyEngine = static function (): int {
        $selectedEngineVersion = defined('SMARTY_ENGINE_VERSION') ? SMARTY_ENGINE_VERSION : 4;
        if (!is_int($selectedEngineVersion) || !in_array($selectedEngineVersion, [4, 5], true)) {
            throw new RuntimeException('SMARTY_ENGINE_VERSION must be defined as the integer 4 or 5.');
        }

        return $selectedEngineVersion;
    };

    $bootstrapSmartyEngine = static function (int $selectedEngineVersion): void {
        $engineStateKey = '__modified_storefront_smarty_engine_version';
        $loadedEngineVersion = $GLOBALS[$engineStateKey] ?? null;

        if ($loadedEngineVersion !== null && $loadedEngineVersion !== $selectedEngineVersion) {
            throw new RuntimeException('The selected Smarty version conflicts with the engine already loaded by the storefront bootstrap.');
        }

        if ($loadedEngineVersion !== null) {
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
        $smartyEntry = $selectedEngineVersion === 5
            ? $smartyRoot . 'smarty_5/libs/Smarty.class.php'
            : $smartyRoot . 'smarty_4/Smarty.class.php';

        if (!is_file($smartyEntry) || !is_readable($smartyEntry)) {
            throw new RuntimeException(sprintf(
                'The Smarty %d entry file does not exist or is not readable: %s',
                $selectedEngineVersion,
                $smartyEntry
            ));
        }

        require_once $smartyEntry;

        if ($selectedEngineVersion === 5 && !class_exists('Smarty\\Smarty')) {
            throw new RuntimeException('The Smarty 5 entry file did not provide the expected vendor class Smarty\\Smarty.');
        }
        if ($selectedEngineVersion === 4 && !class_exists('Smarty', false)) {
            throw new RuntimeException('The Smarty 4 entry file did not provide the expected global vendor class Smarty.');
        }

        $GLOBALS[$engineStateKey] = $selectedEngineVersion;
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

    $loadStorefrontEntryPoints = static function (int $selectedEngineVersion): void {
        if ($selectedEngineVersion === 5) {
            require_once __DIR__ . '/Smarty.php';
        }

        require_once __DIR__ . '/Template.php';
    };

    $selectedEngineVersion = $selectSmartyEngine();
    $bootstrapSmartyEngine($selectedEngineVersion);
    $registerStorefrontAutoloader();
    $loadStorefrontEntryPoints($selectedEngineVersion);
})();
