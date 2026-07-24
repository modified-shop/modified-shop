<?php

use Modified\Storefront\Template\ResolvedTemplateFile;
use Modified\Storefront\Template\TemplateRuntime;

final class Template
{
    public static function resolve(string $logicalName): string
    {
        return self::runtime()
            ->fileResolver()
            ->resolve($logicalName)
            ->smartyReference();
    }

    public static function path(string $logicalName): string
    {
        return self::runtime()
            ->fileResolver()
            ->resolve($logicalName)
            ->absolutePath();
    }

    public static function findPath(string $logicalName): ?string
    {
        $resolved = self::runtime()->fileResolver()->find($logicalName);

        return $resolved === null ? null : $resolved->absolutePath();
    }

    public static function url(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->relativeUrl(self::resolveForUrl($runtime, $logicalName));
    }

    public static function catalogUrl(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->catalogUrl(self::resolveForUrl($runtime, $logicalName));
    }

    public static function absoluteUrl(string $logicalName = ''): string
    {
        $runtime = self::runtime();

        return $runtime->urlGenerator()->absoluteUrl(self::resolveForUrl($runtime, $logicalName));
    }

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
