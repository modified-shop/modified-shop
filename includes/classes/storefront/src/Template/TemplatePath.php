<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\InvalidTemplatePathException;

/**
 * Validates template-relative logical names used by the existing template API.
 *
 * It rejects absolute or traversing logical names such as "../file.html".
 */
final class TemplatePath
{
    /**
     * Validates and returns a safe template-relative logical file name.
     *
     * Names such as "module/product.html" are accepted; absolute paths, empty
     * segments, and traversal through "." or ".." are rejected.
     */
    public static function normalizeLogicalName(string $logicalName, bool $allowEmpty = false): string
    {
        if (str_contains($logicalName, "\0")) {
            throw new InvalidTemplatePathException('Der Template-Dateipfad enthält ein Nullbyte.');
        }

        if ($logicalName === '') {
            if ($allowEmpty) {
                return '';
            }

            throw new InvalidTemplatePathException('Der Template-Dateipfad darf nicht leer sein.');
        }

        if (
            $logicalName[0] === '/'
            || $logicalName[0] === '\\'
            || preg_match('/^[a-zA-Z]:[\\\\\\/]/', $logicalName) === 1
        ) {
            throw new InvalidTemplatePathException(sprintf(
                'Der Template-Dateipfad "%s" muss relativ sein.',
                $logicalName
            ));
        }

        if (str_contains($logicalName, '\\')) {
            throw new InvalidTemplatePathException(sprintf(
                'Der Template-Dateipfad "%s" enthält unzulässige Verzeichnistrenner.',
                $logicalName
            ));
        }

        $pathToValidate = rtrim($logicalName, '/');
        if ($pathToValidate === '') {
            throw new InvalidTemplatePathException('Der Template-Dateipfad darf nicht auf das Wurzelverzeichnis zeigen.');
        }

        foreach (explode('/', $pathToValidate) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidTemplatePathException(sprintf(
                    'Unsicherer relativer Template-Dateipfad "%s".',
                    $logicalName
                ));
            }
        }

        return $logicalName;
    }

    /**
     * Compatibility entry point for the PR-1 API; new code uses FilesystemPath directly.
     */
    public static function joinFilesystem(string $baseDirectory, string $relativePath = ''): string
    {
        return FilesystemPath::join($baseDirectory, $relativePath);
    }

    /**
     * Compatibility entry point for the PR-1 API; new code uses TemplateUrl directly.
     */
    public static function joinUrl(string $baseUrl, string $relativePath): string
    {
        return TemplateUrl::join($baseUrl, $relativePath);
    }
}
