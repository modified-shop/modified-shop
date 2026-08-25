<?php

namespace Modified\Storefront\Template;

/**
 * Provides URL operations used by the template system.
 */
final class TemplateUrl
{
    /**
     * Joins a URL base with a relative path at their slash boundary.
     *
     * Example: join('https://shop.example/templates/', '/tpl_modified/css') returns
     * 'https://shop.example/templates/tpl_modified/css'.
     */
    public static function join(string $baseUrl, string $relativePath): string
    {
        $baseUrl = rtrim($baseUrl, '/');
        $relativePath = ltrim($relativePath, '/');

        if ($baseUrl === '') {
            return $relativePath;
        }

        if ($relativePath === '') {
            return $baseUrl . '/';
        }

        return $baseUrl . '/' . $relativePath;
    }
}
