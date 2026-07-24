<?php

namespace Modified\Storefront\Template;

/**
 * Generates browser URLs for resolved template files.
 *
 * It combines the requested URL base with the actual source template, so an
 * inherited asset is linked from "templates/base/..." instead of the child.
 */
final class TemplateUrlGenerator
{
    private string $relativeBase;
    private string $catalogBase;
    private string $absoluteBase;

    /**
     * Creates a generator with the URL bases used by the different shop contexts.
     */
    public function __construct(string $relativeBase, string $catalogBase, string $absoluteBase)
    {
        $this->relativeBase = $relativeBase;
        $this->catalogBase = $catalogBase;
        $this->absoluteBase = $absoluteBase;
    }

    /**
     * Creates a generator from the shop URL and SSL constants.
     */
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

    /**
     * Returns a URL relative to the configured shop base.
     */
    public function relativeUrl(ResolvedTemplateFile $file): string
    {
        return $this->generate($this->relativeBase, $file);
    }

    /**
     * Returns a URL rooted at the catalog path.
     */
    public function catalogUrl(ResolvedTemplateFile $file): string
    {
        return $this->generate($this->catalogBase, $file);
    }

    /**
     * Returns a fully qualified URL including the configured HTTP or HTTPS server.
     */
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
