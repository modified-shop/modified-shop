<?php

namespace Modified\Storefront\Template;

final class TemplateUrlGenerator
{
    private string $relativeBase;
    private string $catalogBase;
    private string $absoluteBase;

    public function __construct(string $relativeBase, string $catalogBase, string $absoluteBase)
    {
        $this->relativeBase = $relativeBase;
        $this->catalogBase = $catalogBase;
        $this->absoluteBase = $absoluteBase;
    }

    public static function fromGlobals(): self
    {
        $catalogBase = self::constantString('DIR_WS_CATALOG');
        $server = self::constantBool('ENABLE_SSL') && self::constantString('HTTPS_SERVER') !== ''
            ? self::constantString('HTTPS_SERVER')
            : self::constantString('HTTP_SERVER');

        return new self(
            self::constantString('DIR_WS_BASE'),
            $catalogBase,
            TemplatePath::joinUrl($server, $catalogBase)
        );
    }

    public function relativeUrl(ResolvedTemplateFile $file): string
    {
        return $this->generate($this->relativeBase, $file);
    }

    public function catalogUrl(ResolvedTemplateFile $file): string
    {
        return $this->generate($this->catalogBase, $file);
    }

    public function absoluteUrl(ResolvedTemplateFile $file): string
    {
        return $this->generate($this->absoluteBase, $file);
    }

    private function generate(string $base, ResolvedTemplateFile $file): string
    {
        return TemplatePath::joinUrl(
            $base,
            'templates/' . $file->sourceTemplate()->value() . '/' . $file->logicalName()
        );
    }

    private static function constantString(string $name): string
    {
        if (!defined($name)) {
            return '';
        }

        $value = constant($name);

        return is_scalar($value) ? (string) $value : '';
    }

    private static function constantBool(string $name): bool
    {
        if (!defined($name)) {
            return false;
        }

        return in_array(constant($name), [true, 1, '1', 'true'], true);
    }
}
