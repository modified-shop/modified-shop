<?php

namespace Modified\Storefront\Template;

/**
 * Rewrites relative CSS asset references against the public source URL.
 *
 * The rewriter operates only on CSS and URLs. It does not know where the CSS
 * comes from on the filesystem or how the shop exposes catalog files.
 */
final class CssUrlRewriter
{
    /**
     * Rewrites relative url() references while preserving absolute and embedded URLs.
     *
     * The source URL tells the rewriter where the CSS file is publicly located.
     * References are resolved against its directory, as a browser would do:
     * `url(../fonts/font.woff2)` in `/templates/example/css/main.css` becomes
     * `url(/templates/example/fonts/font.woff2)`.
     */
    public function rewrite(string $css, string $sourceUrl): string
    {
        return preg_replace_callback(
            '~url\(\s*(?:(["\'])(.*?)\1|([^"\')]*))\s*\)~is',
            function (array $matches) use ($sourceUrl): string {
                $quote = $matches[1] ?? '';
                $reference = trim($quote === '' ? ($matches[3] ?? '') : $matches[2]);
                $rewritten = $this->rewriteReference($reference, $sourceUrl);

                if ($rewritten === $reference) {
                    return $matches[0];
                }

                return 'url(' . $quote . $rewritten . $quote . ')';
            },
            $css
        ) ?? $css;
    }

    /**
     * Resolves a relative reference against the source file's public directory.
     *
     * The reference is split at its first `?` or `#`. Only the path before it is
     * resolved; the optional query string and fragment are appended unchanged.
     * Absolute, embedded, and otherwise non-relative references remain unchanged.
     */
    private function rewriteReference(string $reference, string $sourceUrl): string
    {
        if (
            $reference === ''
            || $reference[0] === '/'
            || $reference[0] === '#'
            || str_contains($reference, '\\')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $reference) === 1
        ) {
            return $reference;
        }

        $suffixOffset = strcspn($reference, '?#');
        $relativePath = substr($reference, 0, $suffixOffset);
        $suffix = substr($reference, $suffixOffset);

        if ($relativePath === '' || str_starts_with(strtolower($relativePath), 'var(')) {
            return $reference;
        }

        $sourcePath = substr($sourceUrl, 0, strcspn($sourceUrl, '?#'));
        $lastSlash = strrpos($sourcePath, '/');
        $sourceDirectoryUrl = $lastSlash === false ? '' : substr($sourcePath, 0, $lastSlash + 1);

        return $this->normalizeUrl($sourceDirectoryUrl . $relativePath) . $suffix;
    }

    /**
     * Removes dot segments from a URL path without changing its origin or root type.
     *
     * For example, `/assets/css/../fonts/font.woff2` becomes
     * `/assets/fonts/font.woff2`.
     */
    private function normalizeUrl(string $url): string
    {
        $origin = '';
        $path = $url;

        if (preg_match('~^([a-z][a-z0-9+.-]*://[^/]*|//[^/]*)(/.*)?$~i', $url, $matches) === 1) {
            $origin = $matches[1];
            $path = $matches[2] ?? '/';
        }

        $absolute = str_starts_with($path, '/');
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                if ($segments !== [] && end($segments) !== '..') {
                    array_pop($segments);
                } elseif (!$absolute) {
                    $segments[] = $segment;
                }

                continue;
            }

            $segments[] = $segment;
        }

        return $origin . ($absolute ? '/' : '') . implode('/', $segments);
    }
}
