<?php

namespace Modified\Storefront\Ui\Integration\Smarty;

use Modified\Storefront\Template\LogicalTemplatePath;
use Modified\Storefront\Template\Smarty\ResourceNameTransformerInterface;
use Modified\Storefront\Template\Smarty\Resource\SmartyResourceContext;
use Modified\Storefront\Module\ModuleId;
use Modified\Storefront\Ui\Module\Template\Exception\CurrentModuleTemplateException;
use Modified\Storefront\Ui\Module\Template\Exception\InvalidModuleTemplateNameException;
use Modified\Storefront\Ui\Module\Template\ModuleTemplateResolver;
use Modified\Storefront\Ui\Module\Template\ResolvedModuleTemplate;
use Modified\Storefront\Ui\Runtime\UiRuntime;
use RuntimeException;
use Smarty\Resource\CustomPlugin;
use Smarty\Template;
use Smarty\Template\Source;

/**
 * Thin Smarty 5 adapter for module:<vendor>/<module>/<logical-name> resources.
 */
final class ModuleTemplateResource extends CustomPlugin implements ResourceNameTransformerInterface
{
    private const RESOURCE_PREFIX = 'module:';

    private ?ModuleTemplateResolver $resolver;
    private SmartyResourceContext $resourceContext;

    private ?ResolvedModuleTemplate $fetchContext = null;

    public function __construct(
        ?ModuleTemplateResolver $resolver = null,
        ?SmartyResourceContext $resourceContext = null
    ) {
        $this->resolver = $resolver;
        $this->resourceContext = $resourceContext ?? new SmartyResourceContext();
    }

    public function transformResourceName(string $resourceName, ?Template $callingTemplate): string
    {
        return $this->resourceContext->contextualizeWhenPossible(
            self::RESOURCE_PREFIX,
            $resourceName,
            $callingTemplate
        );
    }

    public function populate(Source $source, ?Template $template = null): void
    {
        $resolved = $this->resolveTemplate($source, $template);
        $this->resourceContext->remember($source, $resolved->absolutePath());

        $previousContext = $this->fetchContext;
        $this->fetchContext = $resolved;
        try {
            parent::populate($source, $template);
        } finally {
            $this->fetchContext = $previousContext;
        }

        $source->uid = sha1('module:' . $resolved->absolutePath());
    }

    protected function fetch($name, &$source, &$mtime)
    {
        $logicalResource = $this->resourceContext->logicalName((string) $name);
        $resolved = $this->fetchContext;
        if ($resolved === null || $this->resourceName($resolved) !== $logicalResource) {
            throw new RuntimeException(sprintf(
                'The module: resource "%s" is missing its resolved template context.',
                $logicalResource
            ));
        }

        $source = file_get_contents($resolved->absolutePath());
        if ($source === false) {
            throw new RuntimeException(sprintf(
                'The module template file "%s" could not be read.',
                $resolved->absolutePath()
            ));
        }

        $mtime = filemtime($resolved->absolutePath());
        if ($mtime === false) {
            throw new RuntimeException(sprintf(
                'The timestamp of the module template file "%s" is missing.',
                $resolved->absolutePath()
            ));
        }
    }

    private function resolveTemplate(Source $source, ?Template $template): ResolvedModuleTemplate
    {
        [$moduleId, $logicalPath] = $this->parseResourceName($source->name);

        if ($this->resourceContext->hasContextIdentifier($source->name)) {
            try {
                return $this->resolver()->resolveAfter(
                    $moduleId,
                    $logicalPath,
                    $this->resourceContext->callingTemplatePath($template, self::RESOURCE_PREFIX)
                );
            } catch (CurrentModuleTemplateException $exception) {
                return $this->resolver()->resolve($moduleId, $logicalPath);
            }
        }

        return $this->resolver()->resolve($moduleId, $logicalPath);
    }

    /** @return array{ModuleId, LogicalTemplatePath} */
    private function parseResourceName(string $sourceName): array
    {
        $resourceName = $this->resourceContext->logicalName($sourceName);
        $parts = explode('/', $resourceName, 3);

        if (
            str_contains($resourceName, '\\')
            || count($parts) !== 3
            || $parts[0] === ''
            || $parts[1] === ''
            || $parts[2] === ''
        ) {
            throw new InvalidModuleTemplateNameException(sprintf(
                'Invalid module: resource "%s".',
                $resourceName
            ));
        }

        return [
            new ModuleId($parts[0] . '/' . $parts[1]),
            new LogicalTemplatePath($parts[2]),
        ];
    }

    private function resourceName(ResolvedModuleTemplate $resolved): string
    {
        return $resolved->moduleId()->value() . '/' . $resolved->logicalPath()->value();
    }

    private function resolver(): ModuleTemplateResolver
    {
        return $this->resolver ??= UiRuntime::get()->moduleTemplateResolver();
    }
}
