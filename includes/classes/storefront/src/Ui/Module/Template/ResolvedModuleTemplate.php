<?php

namespace Modified\Storefront\Ui\Module\Template;

use Modified\Storefront\Template\LogicalTemplatePath;
use Modified\Storefront\Template\TemplateId;
use Modified\Storefront\Module\ModuleId;

/**
 * Describes one concrete file from the module-template candidate sequence.
 */
final class ResolvedModuleTemplate
{
    public const TEMPLATE_OVERRIDE = 'template_override';
    public const MODULE_VARIANT = 'module_variant';
    public const MODULE_DEFAULT = 'module_default';

    private ModuleId $moduleId;
    private LogicalTemplatePath $logicalPath;
    private string $absolutePath;
    private string $origin;
    private ?TemplateId $sourceTemplate;

    public function __construct(
        ModuleId $moduleId,
        LogicalTemplatePath $logicalPath,
        string $absolutePath,
        string $origin,
        ?TemplateId $sourceTemplate = null
    ) {
        $this->moduleId = $moduleId;
        $this->logicalPath = $logicalPath;
        $this->absolutePath = $absolutePath;
        $this->origin = $origin;
        $this->sourceTemplate = $sourceTemplate;
    }

    public function moduleId(): ModuleId
    {
        return $this->moduleId;
    }

    public function logicalPath(): LogicalTemplatePath
    {
        return $this->logicalPath;
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function sourceTemplate(): ?TemplateId
    {
        return $this->sourceTemplate;
    }
}
