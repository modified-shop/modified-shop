<?php

use Modified\Storefront\Template\ResolvedTemplateFile;
use Modified\Storefront\Template\TemplateRuntime;

/**
 * Provides the global facade used by shop code to access inherited template files.
 *
 * Callers pass a logical path relative to a template directory. The facade searches
 * the active template first and then its parents. For example, if "current" inherits
 * from "parent-a" and only the parent contains "module/login.html":
 *
 * @example
 * Template::resolve('module/login.html'); // "parent-a/module/login.html"
 * Template::path('module/login.html');    // "/var/www/shop/templates/parent-a/module/login.html"
 */
final class Template
{
    /**
     * Resolves a logical file name to the template-qualified reference expected by Smarty.
     *
     * Use this result with methods such as `$smarty->fetch()` or `$smarty->display()`.
     * If the file comes from a parent, the returned reference names that parent.
     *
     * @param string $logicalName Template-relative file name, for example "module/login.html".
     *
     * @return string A Smarty reference such as "parent-a/module/login.html".
     *
     * @example
     * $smarty->fetch(Template::resolve('module/login.html'));
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     * @throws \Modified\Storefront\Template\Exception\TemplateFileNotFoundException
     */
    public static function resolve(string $logicalName): string
    {
        return self::runtime()
            ->fileResolver()
            ->resolve($logicalName)
            ->smartyReference();
    }

    /**
     * Resolves a logical file or directory name to its absolute filesystem path.
     *
     * Use this result with PHP filesystem functions such as `require`, `filemtime`,
     * or `opendir`. A trailing slash in the input is preserved in the result.
     *
     * @param string $logicalName Template-relative path, for example "source/boxes.php".
     *
     * @return string An absolute path such as "/var/www/shop/templates/parent-a/source/boxes.php".
     *
     * @example
     * require Template::path('source/boxes.php');
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     * @throws \Modified\Storefront\Template\Exception\TemplateFileNotFoundException
     */
    public static function path(string $logicalName): string
    {
        return self::runtime()
            ->fileResolver()
            ->resolve($logicalName)
            ->absolutePath();
    }

    /**
     * Finds an inherited file and returns its absolute path when it exists.
     *
     * Unlike {@see self::path()}, a missing file produces null instead of an exception.
     * Invalid or unsafe logical paths are still rejected.
     *
     * @param string $logicalName Template-relative file name, for example "mail/english/order.html".
     *
     * @return string|null An absolute path such as "/var/www/shop/templates/parent-a/mail/english/order.html", or null.
     *
     * @example
     * $mailTemplate = Template::findPath('mail/english/order.html'); // string|null
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     */
    public static function findPath(string $logicalName): ?string
    {
        $resolved = self::runtime()->fileResolver()->find($logicalName);

        return $resolved === null ? null : $resolved->absolutePath();
    }

    /**
     * Returns the resolved file URL relative to the configured shop base.
     *
     * Passing an empty string returns the active template's root URL. For a file,
     * the URL names the template that actually supplies it, including a parent.
     *
     * @param string $logicalName Template-relative file name, or an empty string for the template root.
     *
     * @return string A URL such as "/base/templates/parent-a/img/logo.png".
     *
     * @example
     * Template::url('img/logo.png'); // "/base/templates/parent-a/img/logo.png"
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     * @throws \Modified\Storefront\Template\Exception\TemplateFileNotFoundException
     */
    public static function url(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->relativeUrl(self::resolveForUrl($runtime, $logicalName));
    }

    /**
     * Returns the resolved file URL rooted at the configured catalog path.
     *
     * This is useful when the caller needs a catalog-relative browser URL rather
     * than the base-relative form returned by {@see self::url()}.
     *
     * @param string $logicalName Template-relative file name, or an empty string for the template root.
     *
     * @return string A URL such as "/catalog/templates/parent-a/img/logo.png".
     *
     * @example
     * Template::catalogUrl('img/logo.png'); // "/catalog/templates/parent-a/img/logo.png"
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     * @throws \Modified\Storefront\Template\Exception\TemplateFileNotFoundException
     */
    public static function catalogUrl(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->catalogUrl(self::resolveForUrl($runtime, $logicalName));
    }

    /**
     * Returns the fully qualified URL of a resolved template file.
     *
     * The configured HTTP or HTTPS server, catalog path, and actual source template
     * are combined into a URL suitable for external responses such as emails.
     *
     * @param string $logicalName Template-relative file name, or an empty string for the template root.
     *
     * @return string A URL such as "https://shop.example/catalog/templates/parent-a/img/logo.png".
     *
     * @example
     * Template::absoluteUrl('img/logo.png');
     * // "https://shop.example/catalog/templates/parent-a/img/logo.png"
     *
     * @throws \Modified\Storefront\Template\Exception\InvalidTemplatePathException
     * @throws \Modified\Storefront\Template\Exception\TemplateFileNotFoundException
     */
    public static function absoluteUrl(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->absoluteUrl(self::resolveForUrl($runtime, $logicalName));
    }

    /**
     * Returns all effective template IDs from the active template to the root parent.
     *
     * The order is the same order used for file lookup: overrides are checked before
     * their parents.
     *
     * @return string[] Template IDs such as ["current", "parent-b", "parent-a"].
     *
     * @example
     * Template::chain(); // ["current", "parent-b", "parent-a"]
     */
    public static function chain(): array
    {
        return self::runtime()->chain()->names();
    }

    private static function runtime(): TemplateRuntime
    {
        return TemplateRuntime::get();
    }

    private static function resolveForUrl(TemplateRuntime $runtime, string $logicalName): ResolvedTemplateFile
    {
        return $logicalName === ''
            ? $runtime->rootReference()
            : $runtime->fileResolver()->resolve($logicalName);
    }
}
