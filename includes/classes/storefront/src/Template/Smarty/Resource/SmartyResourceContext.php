<?php

namespace Modified\Storefront\Template\Smarty\Resource;

use Modified\Storefront\Template\Exception\CurrentTemplateFileException;
use Smarty\Resource\FilePlugin;
use Smarty\Template;
use Smarty\Template\Source;
use WeakMap;

/**
 * Tracks the concrete source paths used by contextual storefront resources.
 *
 * Smarty identifies custom resources by their logical names, while inherited resources also
 * need the concrete file containing the call. This object supplies that technical context for
 * regular file sources and for all custom resources that remember their resolved source here.
 */
final class SmartyResourceContext
{
    /** @var WeakMap<Source, string> */
    private WeakMap $resolvedPaths;

    public function __construct()
    {
        $this->resolvedPaths = new WeakMap();
    }

    /**
     * Adds the caller path to a resource name before Smarty selects its template instance.
     */
    public function contextualize(
        string $resourcePrefix,
        string $resourceName,
        ?Template $callingTemplate
    ): string {
        if (!str_starts_with($resourceName, $resourcePrefix)) {
            return $resourceName;
        }

        $sourceName = substr($resourceName, strlen($resourcePrefix));
        if ($this->hasContextIdentifier($sourceName) || !$callingTemplate instanceof Template) {
            return $resourceName;
        }

        return $resourcePrefix
            . sha1($this->templatePath($callingTemplate))
            . '@'
            . $sourceName;
    }

    /**
     * Adds caller context when its concrete path is available, otherwise preserving the name.
     */
    public function contextualizeWhenPossible(
        string $resourcePrefix,
        string $resourceName,
        ?Template $callingTemplate
    ): string {
        try {
            return $this->contextualize($resourcePrefix, $resourceName, $callingTemplate);
        } catch (CurrentTemplateFileException $exception) {
            return $resourceName;
        }
    }

    /**
     * Remembers the concrete path supplied by a custom resource.
     */
    public function remember(Source $source, string $absolutePath): void
    {
        $this->resolvedPaths[$source] = $absolutePath;
    }

    /**
     * Returns the concrete path of the template containing a custom-resource call.
     */
    public function callingTemplatePath(?Template $template, string $resourcePrefix): string
    {
        $callingTemplate = $template?->getParent();
        if (!$callingTemplate instanceof Template) {
            throw new CurrentTemplateFileException(sprintf(
                'The %s resource requires a calling template file.',
                $resourcePrefix
            ));
        }

        return $this->templatePath($callingTemplate);
    }

    /**
     * Returns the concrete path of a regular file source or a remembered custom-resource source.
     */
    public function templatePath(Template $template): string
    {
        $source = $template->getSource();
        if (!$source instanceof Source) {
            throw new CurrentTemplateFileException(
                'The Smarty template does not have a resolvable source.'
            );
        }

        if (isset($this->resolvedPaths[$source])) {
            return $this->resolvedPaths[$source];
        }

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

        throw new CurrentTemplateFileException(sprintf(
            'The current template source "%s" does not have a resolvable file path.',
            $source->resource
        ));
    }

    /**
     * Removes the technical context identifier while preserving the logical resource name.
     */
    public function logicalName(string $sourceName): string
    {
        return $this->hasContextIdentifier($sourceName)
            ? substr($sourceName, 41)
            : $sourceName;
    }

    /**
     * Detects the internal format <40-character SHA-1>@<logical resource name>.
     */
    public function hasContextIdentifier(string $sourceName): bool
    {
        return preg_match('/^[a-f0-9]{40}@/i', $sourceName) === 1;
    }
}
