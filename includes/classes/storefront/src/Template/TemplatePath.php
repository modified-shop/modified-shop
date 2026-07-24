<?php

namespace Modified\Storefront\Template;

final class TemplatePath
{
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

    public static function joinFilesystem(string $baseDirectory, string $relativePath = ''): string
    {
        $baseDirectory = rtrim(str_replace('\\', '/', $baseDirectory), '/');

        return $relativePath === ''
            ? $baseDirectory
            : $baseDirectory . '/' . ltrim($relativePath, '/');
    }

    public static function joinUrl(string $baseUrl, string $relativePath): string
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
