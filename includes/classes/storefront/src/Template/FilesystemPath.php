<?php

namespace Modified\Storefront\Template;

/**
 * Provides platform-independent operations for filesystem paths.
 */
final class FilesystemPath
{
    /**
     * Normalizes directory separators and removes trailing separators.
     *
     * Example: normalize('C:\\shop\\templates\\') returns 'C:/shop/templates'.
     */
    public static function normalize(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if ($normalized === '/') {
            return '/';
        }

        return rtrim($normalized, '/');
    }

    /**
     * Returns the normalized canonical path when the filesystem entry exists.
     *
     * Example: canonicalize('/shop/templates/../templates/tpl_modified') returns
     * '/shop/templates/tpl_modified' when that directory exists.
     */
    public static function canonicalize(string $path): ?string
    {
        $canonicalPath = realpath($path);

        return $canonicalPath === false ? null : self::normalize($canonicalPath);
    }

    /**
     * Joins a base path with any number of relative path segments.
     *
     * Example: join('/shop', 'templates', 'tpl_modified') returns '/shop/templates/tpl_modified'.
     */
    public static function join(string $basePath, string ...$relativePaths): string
    {
        $joinedPath = self::normalize($basePath);

        foreach ($relativePaths as $relativePath) {
            $relativePath = ltrim(self::normalize($relativePath), '/');
            if ($relativePath === '') {
                continue;
            }

            $joinedPath .= ($joinedPath === '/' ? '' : '/') . $relativePath;
        }

        return $joinedPath;
    }

    /**
     * Checks containment at path-segment boundaries.
     *
     * Example: isWithin('/shop/templates/tpl_modified', '/shop/templates') returns true.
     */
    public static function isWithin(string $path, string $root): bool
    {
        $path = self::normalize($path);
        $root = self::normalize($root);

        if ($path === $root) {
            return true;
        }

        return $root === '/'
            ? str_starts_with($path, '/')
            : str_starts_with($path, $root . '/');
    }
}
