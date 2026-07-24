<?php

namespace Modified\Storefront\Template;

final class ResolvedTemplateFile
{
    private TemplateId $sourceTemplate;
    private string $logicalName;
    private string $absolutePath;

    public function __construct(TemplateId $sourceTemplate, string $logicalName, string $absolutePath)
    {
        $this->sourceTemplate = $sourceTemplate;
        $this->logicalName = $logicalName;
        $this->absolutePath = $absolutePath;
    }

    public function sourceTemplate(): TemplateId
    {
        return $this->sourceTemplate;
    }

    public function logicalName(): string
    {
        return $this->logicalName;
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function smartyReference(): string
    {
        return $this->sourceTemplate->value() . '/' . $this->logicalName;
    }
}
