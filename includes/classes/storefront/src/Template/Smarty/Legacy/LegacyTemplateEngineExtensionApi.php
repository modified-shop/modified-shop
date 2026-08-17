<?php

namespace Modified\Storefront\Template\Smarty\Legacy;

use Modified\Storefront\Template\TemplateRuntime;
use Smarty\Smarty;

/**
 * Binds the historical modified extension API to Smarty's BC layer.
 *
 * This class is intentionally a time-limited compatibility adapter. The public file conventions
 * remain unchanged, even though Smarty 5 marks the directory API as deprecated.
 */
final class LegacyTemplateEngineExtensionApi
{
    public function register(Smarty $smarty, ?TemplateRuntime $runtime, string $catalogRoot): void
    {
        $templatePluginDirectories = $this->templatePluginDirectories($runtime);

        foreach ($templatePluginDirectories as $templatePluginDirectory) {
            $this->addPluginsDirectory($smarty, $templatePluginDirectory);
        }
        $this->addPluginsDirectory($smarty, $this->shopPluginDirectory($catalogRoot));

        $this->registerPhpModifiers($smarty, $templatePluginDirectories);
    }

    private function addPluginsDirectory(Smarty $smarty, string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        // Smarty 5 still provides the historical file contract only through this deprecated BC API.
        // The suppression is intentionally limited to this exact call.
        @$smarty->addPluginsDir($directory);
    }

    /**
     * @param list<string> $templatePluginDirectories Child-to-parent order
     */
    private function registerPhpModifiers(Smarty $smarty, array $templatePluginDirectories): void
    {
        $inheritedPhpPlugins = [];

        foreach (array_reverse($templatePluginDirectories) as $templatePluginDirectory) {
            $registrationFile = $templatePluginDirectory . '/register_php_plugins.php';
            if (!is_file($registrationFile)) {
                continue;
            }

            $register_php_plugins = [];
            include $registrationFile;

            if (is_array($register_php_plugins)) {
                $inheritedPhpPlugins = array_merge($inheritedPhpPlugins, $register_php_plugins);
            }
        }

        foreach (array_unique($inheritedPhpPlugins) as $phpPlugin) {
            $smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, $phpPlugin, $phpPlugin);
        }
    }

    /**
     * @return list<string> Plugin directories in child-to-parent order
     */
    private function templatePluginDirectories(?TemplateRuntime $runtime): array
    {
        if ($runtime === null) {
            return [];
        }

        $directories = [];
        foreach ($runtime->chain() as $templateId) {
            $directories[] = $runtime->fileResolver()->templateDirectory($templateId) . '/smarty';
        }

        return $directories;
    }

    private function shopPluginDirectory(string $catalogRoot): string
    {
        if (defined('DIR_FS_EXTERNAL') && is_string(DIR_FS_EXTERNAL) && DIR_FS_EXTERNAL !== '') {
            return rtrim(DIR_FS_EXTERNAL, '/\\') . '/smarty/plugins';
        }

        return rtrim($catalogRoot, '/\\') . '/includes/external/smarty/plugins';
    }
}
