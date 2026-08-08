<?php

namespace Modified\Storefront\Template;

use LogicException;
use RuntimeException;

/**
 * Reads catalog CSS files and supplies their public source URL to the CSS rewriter.
 */
final class CatalogCssFileRewriter
{
    private CssUrlRewriter $rewriter;
    private string $catalogDirectory;
    private string $catalogUrl;

    public function __construct(CssUrlRewriter $rewriter, string $catalogDirectory, string $catalogUrl)
    {
        $this->rewriter = $rewriter;
        $this->catalogDirectory = rtrim(str_replace('\\', '/', $catalogDirectory), '/') . '/';
        $this->catalogUrl = $catalogUrl === '' ? '/' : $catalogUrl;
    }

    /**
     * Creates the catalog adapter from the shop's filesystem and URL constants.
     */
    public static function fromGlobals(): self
    {
        if (!defined('DIR_FS_CATALOG') || !is_scalar(DIR_FS_CATALOG) || (string) DIR_FS_CATALOG === '') {
            throw new LogicException('Die Konstante DIR_FS_CATALOG muss für die CSS-URL-Auflösung definiert sein.');
        }

        $catalogUrl = defined('DIR_WS_CATALOG') && is_scalar(DIR_WS_CATALOG)
            ? (string) DIR_WS_CATALOG
            : '/';

        return new self(new CssUrlRewriter(), (string) DIR_FS_CATALOG, $catalogUrl);
    }

    /**
     * Reads a catalog-relative CSS file and rewrites its relative url() references.
     */
    public function rewriteFile(string $sourceCatalogPath): string
    {
        $sourceCatalogPath = TemplatePath::normalizeLogicalName($sourceCatalogPath);
        $sourcePath = $this->catalogDirectory . $sourceCatalogPath;
        $css = file_get_contents($sourcePath);

        if ($css === false) {
            throw new RuntimeException(sprintf('Die CSS-Datei "%s" konnte nicht gelesen werden.', $sourcePath));
        }

        $sourceUrl = $this->catalogUrl === '/'
            ? '/' . $sourceCatalogPath
            : TemplatePath::joinUrl($this->catalogUrl, $sourceCatalogPath);

        return $this->rewriter->rewrite($css, $sourceUrl);
    }
}
