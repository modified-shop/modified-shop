<?php

namespace Modified\Storefront\Template\Smarty\Resource;

use Modified\Storefront\Template\Exception\CurrentTemplateFileException;
use Modified\Storefront\Template\ResolvedTemplateFile;
use Modified\Storefront\Template\Smarty\ResourceNameTransformerInterface;
use Modified\Storefront\Template\TemplateFileResolver;
use Modified\Storefront\Template\TemplateRuntime;
use RuntimeException;
use Smarty\Resource\CustomPlugin;
use Smarty\Resource\FilePlugin;
use Smarty\Template;
use Smarty\Template\Source;
use WeakMap;

/**
 * Resolves parent:<name> relative to the template file containing the call.
 *
 * Smarty's regular file: resource always starts in the first configured template directory.
 * This resource delegates to the shared TemplateFileResolver instead, allowing the search to
 * continue after the current child or parent in the modified template chain.
 *
 * Smarty's CustomPlugin::fetch() receives only the logical name, even though parent: also needs
 * the calling template file. populate() therefore resolves this context first and temporarily
 * retains it while the inherited CustomPlugin implementation calls fetch().
 */
final class ParentTemplateResource extends CustomPlugin implements ResourceNameTransformerInterface
{
    private const RESOURCE_PREFIX = 'parent:';

    private ?TemplateFileResolver $resolver;

    /** @var WeakMap<Source, ResolvedTemplateFile> */
    private WeakMap $resolvedSources;

    private ?ResolvedTemplateFile $fetchContext = null;

    /**
     * Creates the resource with an optionally injected resolver for isolated tests.
     */
    public function __construct(?TemplateFileResolver $resolver = null)
    {
        $this->resolver = $resolver;
        $this->resolvedSources = new WeakMap();
    }

    /**
     * Adds the caller context before Smarty selects its template instance.
     *
     * The public name parent:<logical path> becomes
     * parent:<SHA-1 of the caller path>@<logical path> internally. Names that already include
     * context remain unchanged.
     */
    public function transformResourceName(string $resourceName, ?Template $callingTemplate): string
    {
        if (!str_starts_with($resourceName, self::RESOURCE_PREFIX)) {
            return $resourceName;
        }

        $sourceName = substr($resourceName, strlen(self::RESOURCE_PREFIX));
        if ($this->hasContextIdentifier($sourceName) || !$callingTemplate instanceof Template) {
            return $resourceName;
        }

        return self::RESOURCE_PREFIX
            . sha1($this->templatePath($callingTemplate))
            . '@'
            . $sourceName;
    }

    /**
     * Resolves the concrete parent template file and describes it to Smarty.
     *
     * The remembered result retains its concrete origin in case a parent: source later provides
     * the context for another parent: resolution.
     */
    public function populate(Source $source, ?Template $template = null): void
    {
        $resolved = $this->resolveParent($source, $template);

        $this->rememberResolution($source, $resolved);
        $this->populateWithFetchContext($source, $template, $resolved);
        $this->setCompileIdentity($source, $resolved);
    }

    /**
     * Adapts Smarty's context-free CustomPlugin contract to the previously resolved parent file.
     *
     * @param mixed $name Logical resource name passed by Smarty.
     * @param mixed $source Receives the contents of the template file.
     * @param mixed $mtime Receives the modification time of the file.
     */
    protected function fetch($name, &$source, &$mtime)
    {
        $this->fetchWithContext(
            $this->requiredFetchContext((string) $name),
            $source,
            $mtime
        );
    }

    /**
     * Resolves the requested logical name after the calling template file.
     */
    private function resolveParent(Source $source, ?Template $template): ResolvedTemplateFile
    {
        return $this->resolver()->resolveAfter(
            $this->logicalName($source->name),
            $this->currentTemplatePath($template)
        );
    }

    /**
     * Remembers the concrete origin of a parent: source for subsequent context resolution.
     */
    private function rememberResolution(Source $source, ResolvedTemplateFile $resolved): void
    {
        $this->resolvedSources[$source] = $resolved;
    }

    /**
     * Provides CustomPlugin::populate() with the resolved file context for content and timestamp.
     *
     * CustomPlugin::populate() synchronously calls this resource's fetch() method. The previous
     * context is restored defensively so nested calls cannot overwrite an outer operation.
     */
    private function populateWithFetchContext(
        Source $source,
        ?Template $template,
        ResolvedTemplateFile $resolved
    ): void {
        $previousContext = $this->fetchContext;
        $this->fetchContext = $resolved;

        try {
            parent::populate($source, $template);
        } finally {
            $this->fetchContext = $previousContext;
        }
    }

    /**
     * Gives the compiled parent: source an identity based on the concrete resolved file.
     */
    private function setCompileIdentity(Source $source, ResolvedTemplateFile $resolved): void
    {
        $source->uid = sha1('parent:' . $resolved->absolutePath());
    }

    /**
     * Returns the context prepared by populate() and verifies that it belongs to the fetch() call.
     */
    private function requiredFetchContext(string $sourceName): ResolvedTemplateFile
    {
        $logicalName = $this->logicalName($sourceName);
        if ($this->fetchContext === null || $this->fetchContext->logicalName() !== $logicalName) {
            throw new RuntimeException(sprintf(
                'The parent: resource "%s" is missing the current template context.',
                $logicalName
            ));
        }

        return $this->fetchContext;
    }

    /**
     * Reads the content and timestamp from the already resolved parent template file.
     *
     * @param mixed $source Receives the contents of the template file.
     * @param mixed $mtime Receives the modification time of the file.
     */
    private function fetchWithContext(ResolvedTemplateFile $resolved, &$source, &$mtime): void
    {
        $path = $resolved->absolutePath();
        $source = file_get_contents($path);
        if ($source === false) {
            throw new RuntimeException(sprintf('The parent template file "%s" could not be read.', $path));
        }

        $mtime = filemtime($path);
        if ($mtime === false) {
            throw new RuntimeException(sprintf('The timestamp of the parent template file "%s" is missing.', $path));
        }
    }

    /**
     * Returns the concrete path of the template file that invoked parent:.
     */
    private function currentTemplatePath(?Template $template): string
    {
        return $this->templatePath($this->callingTemplate($template));
    }

    /**
     * Returns the Smarty template containing the parent: call.
     */
    private function callingTemplate(?Template $template): Template
    {
        $callingTemplate = $template?->getParent();
        if (!$callingTemplate instanceof Template) {
            throw new CurrentTemplateFileException(
                'The parent: resource requires a calling template file.'
            );
        }

        return $callingTemplate;
    }

    /**
     * Returns the concrete path of a regular template or one already resolved through parent:.
     */
    private function templatePath(Template $template): string
    {
        $currentSource = $this->templateSource($template);
        $rememberedPath = $this->rememberedParentPath($currentSource);
        if ($rememberedPath !== null) {
            return $rememberedPath;
        }

        $filePath = $this->fileTemplatePath($currentSource);
        if ($filePath !== null) {
            return $filePath;
        }

        throw new CurrentTemplateFileException(sprintf(
            'The current template source "%s" does not have a resolvable file path.',
            $currentSource->resource
        ));
    }

    /**
     * Determines the source of a Smarty template.
     */
    private function templateSource(Template $template): Source
    {
        $source = $template->getSource();
        if (!$source instanceof Source) {
            throw new CurrentTemplateFileException(
                'The Smarty template does not have a resolvable source.'
            );
        }

        return $source;
    }

    /**
     * Returns the concrete path of a source that was itself resolved through parent:.
     */
    private function rememberedParentPath(Source $source): ?string
    {
        return isset($this->resolvedSources[$source])
            ? $this->resolvedSources[$source]->absolutePath()
            : null;
    }

    /**
     * Determines the concrete path of a regular Smarty file: source.
     */
    private function fileTemplatePath(Source $source): ?string
    {
        if ($source->handler instanceof FilePlugin) {
            $path = $source->handler->getFilePath(
                $source->name,
                $source->getSmarty(),
                $source->isConfig
            );
            if (is_string($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Removes the technical context identifier while preserving the logical path.
     */
    private function logicalName(string $sourceName): string
    {
        return $this->hasContextIdentifier($sourceName)
            ? substr($sourceName, 41)
            : $sourceName;
    }

    /**
     * Detects the internal format <40-character SHA-1>@<logical path>.
     */
    private function hasContextIdentifier(string $sourceName): bool
    {
        return preg_match('/^[a-f0-9]{40}@/i', $sourceName) === 1;
    }

    /**
     * Returns the injected resolver or the shared resolver of the current TemplateRuntime.
     */
    private function resolver(): TemplateFileResolver
    {
        return $this->resolver ??= TemplateRuntime::get()->fileResolver();
    }
}
