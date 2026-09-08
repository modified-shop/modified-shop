<?php

namespace Modified\Storefront\Template;

use LogicException;

/**
 * Holds the configured template services for the current storefront request.
 *
 * It creates the active chain and file resolver from shop globals and gives
 * integrations one shared entry point for files, URLs, and the template root.
 */
final class TemplateRuntime
{
    private static ?self $instance = null;

    private TemplateChain $chain;
    private TemplateFileResolver $fileResolver;
    private ?TemplateUrlGenerator $urlGenerator;

    /**
     * Creates a runtime from a matching chain, file resolver, and optional URL generator.
     */
    public function __construct(
        TemplateChain $chain,
        TemplateFileResolver $fileResolver,
        ?TemplateUrlGenerator $urlGenerator = null
    ) {
        $this->chain = $chain;
        $this->fileResolver = $fileResolver;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Returns the shared runtime, creating it from shop globals on first access.
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = self::fromGlobals();
        }

        return self::$instance;
    }

    /**
     * Replaces the shared runtime, for example with an explicitly configured test instance.
     */
    public static function install(self $runtime): void
    {
        self::$instance = $runtime;
    }

    /**
     * Discards the shared runtime so the next access creates a fresh instance.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Builds a runtime from DIR_FS_CATALOG and CURRENT_TEMPLATE.
     */
    public static function fromGlobals(): self
    {
        $catalogDirectory = self::requiredConstantString('DIR_FS_CATALOG');
        $activeTemplate = self::requiredConstantString('CURRENT_TEMPLATE');
        $repository = new TemplateManifestRepository(
            TemplatePath::joinFilesystem($catalogDirectory, 'templates')
        );
        $chain = (new TemplateChainResolver($repository))->resolve(new TemplateId($activeTemplate));

        return new self($chain, new TemplateFileResolver($chain, $repository));
    }

    /**
     * Returns the resolved inheritance chain for the active template.
     */
    public function chain(): TemplateChain
    {
        return $this->chain;
    }

    /**
     * Returns the resolver used to locate logical template files.
     */
    public function fileResolver(): TemplateFileResolver
    {
        return $this->fileResolver;
    }

    /**
     * Returns the URL generator, creating its global configuration when first needed.
     */
    public function urlGenerator(): TemplateUrlGenerator
    {
        if ($this->urlGenerator === null) {
            $this->urlGenerator = TemplateUrlGenerator::fromGlobals();
        }

        return $this->urlGenerator;
    }

    /**
     * Returns a resolved directory reference to the active template root.
     *
     * The empty logical name makes the reference suitable for root-relative lookups.
     */
    public function rootReference(): ResolvedTemplateFile
    {
        $templateId = $this->chain->current();

        return new ResolvedTemplateFile(
            $templateId,
            '',
            $this->fileResolver->templateDirectory($templateId)
        );
    }

    private static function requiredConstantString(string $name): string
    {
        if (!defined($name) || !is_scalar(constant($name)) || (string) constant($name) === '') {
            throw new LogicException(sprintf(
                'Die Konstante %s muss vor der Verwendung des Template-Systems definiert sein.',
                $name
            ));
        }

        return (string) constant($name);
    }
}
