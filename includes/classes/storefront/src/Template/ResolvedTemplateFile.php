<?php

namespace Modified\Storefront\Template;

/**
 * Describes the result of resolving a logical template file.
 *
 * It records which template supplied the file and provides both its absolute
 * filesystem path and a Smarty reference such as "base/module/product.html".
 */
final class ResolvedTemplateFile
{
    private TemplateId $sourceTemplate;
    private string $logicalName;
    private string $absolutePath;

    /**
     * Creates a reference to a logical file found in a specific source template.
     */
    public function __construct(TemplateId $sourceTemplate, string $logicalName, string $absolutePath)
    {
        $this->sourceTemplate = $sourceTemplate;
        $this->logicalName = $logicalName;
        $this->absolutePath = $absolutePath;
    }

    /**
     * Returns the template that actually provides the resolved file.
     */
    public function sourceTemplate(): TemplateId
    {
        return $this->sourceTemplate;
    }

    /**
     * Returns the file name relative to its template directory.
     */
    public function logicalName(): string
    {
        return $this->logicalName;
    }

    /**
     * Returns the canonical filesystem path, preserving a requested trailing slash.
     */
    public function absolutePath(): string
    {
        if (str_ends_with($this->logicalName, '/') && !str_ends_with($this->absolutePath, '/')) {
            return $this->absolutePath . '/';
        }

        return $this->absolutePath;
    }

    /**
     * Returns a template-qualified Smarty reference such as "base/index.html".
     */
    public function smartyReference(): string
    {
        return $this->sourceTemplate->value() . '/' . $this->logicalName;
    }
}
