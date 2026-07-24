<?php

namespace Modified\Storefront\Template;

final class TemplateManifest
{
    private TemplateId $templateId;
    private ?TemplateId $parent;
    private array $rawData;

    public function __construct(TemplateId $templateId, ?TemplateId $parent, array $rawData)
    {
        $this->templateId = $templateId;
        $this->parent = $parent;
        $this->rawData = $rawData;
    }

    public static function empty(TemplateId $templateId): self
    {
        return new self($templateId, null, []);
    }

    public function templateId(): TemplateId
    {
        return $this->templateId;
    }

    public function parent(): ?TemplateId
    {
        return $this->parent;
    }

    public function hasSection(string $name): bool
    {
        return array_key_exists($name, $this->rawData);
    }

    public function section(string $name)
    {
        return $this->rawData[$name] ?? null;
    }

    public function rawData(): array
    {
        return $this->rawData;
    }
}
