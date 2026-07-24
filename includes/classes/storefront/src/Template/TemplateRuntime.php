<?php

namespace Modified\Storefront\Template;

use LogicException;

final class TemplateRuntime
{
    private static ?self $instance = null;

    private TemplateChain $chain;
    private TemplateFileResolver $fileResolver;
    private ?TemplateUrlGenerator $urlGenerator;

    public function __construct(
        TemplateChain $chain,
        TemplateFileResolver $fileResolver,
        ?TemplateUrlGenerator $urlGenerator = null
    ) {
        $this->chain = $chain;
        $this->fileResolver = $fileResolver;
        $this->urlGenerator = $urlGenerator;
    }

    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = self::fromGlobals();
        }

        return self::$instance;
    }

    public static function install(self $runtime): void
    {
        self::$instance = $runtime;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

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

    public function chain(): TemplateChain
    {
        return $this->chain;
    }

    public function fileResolver(): TemplateFileResolver
    {
        return $this->fileResolver;
    }

    public function urlGenerator(): TemplateUrlGenerator
    {
        if ($this->urlGenerator === null) {
            $this->urlGenerator = TemplateUrlGenerator::fromGlobals();
        }

        return $this->urlGenerator;
    }

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
