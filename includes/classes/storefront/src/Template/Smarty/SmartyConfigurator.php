<?php

namespace Modified\Storefront\Template\Smarty;

use LogicException;
use Modified\Storefront\Template\Exception\TemplateNotFoundException;
use Modified\Storefront\Template\FilesystemPath;
use Modified\Storefront\Template\TemplateManifestRepository;
use Modified\Storefront\Template\TemplateRuntime;
use Modified\Storefront\Template\Smarty\Cache\ModifiedCacheResource;
use Modified\Storefront\Template\Smarty\Legacy\LegacyTemplateEngineExtensionApi;
use Modified\Storefront\Template\Smarty\Resource\ParentTemplateResource;
use Smarty\Smarty;

final class SmartyConfigurator
{
    public function configure(Smarty $smarty): void
    {
        $catalogRoot = $this->catalogRoot();
        $runtime = $this->availableRuntime();
        $smarty->setCompileDir($catalogRoot . '/templates_c');
        $smarty->setCacheDir($catalogRoot . '/cache');
        $smarty->setTemplateDir($this->templateDirectories($catalogRoot, $runtime));
        $smarty->setConfigDir($this->configDirectories($catalogRoot, $runtime));
        (new LegacyTemplateEngineExtensionApi())->register($smarty, $runtime, $catalogRoot);
        $smarty->registerResource('parent', new ParentTemplateResource());
        if (!defined('RUN_MODE_INSTALLER')) {
            $modifiedCache = $this->availableModifiedCache();
            if ($modifiedCache !== null) {
                $smarty->setCacheResource(new ModifiedCacheResource($modifiedCache));
            }
        }
    }

    /**
     * Returns the global language source followed by the language directories of the template chain.
     */
    private function configDirectories(string $catalogRoot, ?TemplateRuntime $runtime): array
    {
        $canonicalCatalogRoot = FilesystemPath::canonicalize($catalogRoot);
        $directories = [FilesystemPath::join(
            $canonicalCatalogRoot ?? $catalogRoot,
            'lang'
        )];
        if ($runtime === null) {
            return $directories;
        }

        foreach ($runtime->chain() as $templateId) {
            $directories[] = FilesystemPath::join(
                $runtime->fileResolver()->templateDirectory($templateId),
                'lang'
            );
        }

        return $directories;
    }

    /**
     * Returns the modified cache configured by the shop once its initialization is available.
     */
    private function availableModifiedCache(): ?object
    {
        foreach (['DIR_FS_CATALOG', 'DIR_FS_EXTERNAL', 'DB_CACHE_TYPE', 'DIR_FS_CACHE', 'SQL_CACHEDIR'] as $constant) {
            if (!defined($constant)) {
                return null;
            }
        }
        if (!function_exists('auto_include')) {
            return null;
        }

        $cacheInitialization = FilesystemPath::join((string) DIR_FS_CATALOG, 'includes/modified_cache.php');
        if (!is_file($cacheInitialization)) {
            return null;
        }

        global $modified_cache;
        include $cacheInitialization;

        if (!is_object($modified_cache)) {
            throw new LogicException('The modified cache initialization did not provide a cache object.');
        }

        return $modified_cache;
    }

    private function templateDirectories(string $catalogRoot, ?TemplateRuntime $runtime): array
    {
        if ($runtime === null) {
            $repository = new TemplateManifestRepository(
                FilesystemPath::join($catalogRoot, 'templates')
            );

            return [$this->directoryWithinRoot($repository->templatesDirectory(), $catalogRoot)];
        }

        $directories = [dirname($runtime->rootReference()->absolutePath())];
        foreach ($runtime->chain() as $templateId) {
            $directories[] = $runtime->fileResolver()->templateDirectory($templateId);
        }

        return $directories;
    }

    private function availableRuntime(): ?TemplateRuntime
    {
        try {
            return TemplateRuntime::get();
        } catch (LogicException $exception) {
            if (
                !defined('DIR_FS_CATALOG')
                || !is_scalar(DIR_FS_CATALOG)
                || (string) DIR_FS_CATALOG === ''
                || !defined('CURRENT_TEMPLATE')
                || !is_scalar(CURRENT_TEMPLATE)
                || (string) CURRENT_TEMPLATE === ''
            ) {
                return null;
            }

            throw $exception;
        }
    }

    private function directoryWithinRoot(string $directory, string $root): string
    {
        $canonicalRoot = FilesystemPath::canonicalize($root);
        $directory = FilesystemPath::normalize($directory);

        if ($canonicalRoot === null || !FilesystemPath::isWithin($directory, $canonicalRoot)) {
            throw new TemplateNotFoundException(sprintf(
                'The template directory "%s" is outside the shop root "%s".',
                $directory,
                $root
            ));
        }

        return $directory;
    }

    private function catalogRoot(): string
    {
        if (defined('DIR_FS_CATALOG') && is_string(DIR_FS_CATALOG) && trim(DIR_FS_CATALOG) !== '') {
            return FilesystemPath::normalize(DIR_FS_CATALOG);
        }

        return dirname(__DIR__, 6);
    }
}
