<?php

namespace Modified\Storefront\Template;

/**
 * Represents the parsed manifest of a template.
 *
 * It exposes the declared parent used for inheritance and preserves additional
 * sections so later features can read their own manifest configuration.
 */
final class TemplateManifest
{
    private TemplateId $templateId;
    private ?TemplateId $parent;
    private array $rawData;

    /**
     * Creates a manifest for a template with its optional parent and decoded data.
     */
    public function __construct(TemplateId $templateId, ?TemplateId $parent, array $rawData)
    {
        $this->templateId = $templateId;
        $this->parent = $parent;
        $this->rawData = $rawData;
    }

    /**
     * Creates an empty manifest for a template without a template.json file.
     */
    public static function empty(TemplateId $templateId): self
    {
        return new self($templateId, null, []);
    }

    /**
     * Returns the template to which this manifest belongs.
     */
    public function templateId(): TemplateId
    {
        return $this->templateId;
    }

    /**
     * Returns the declared parent template, or null for a root template.
     */
    public function parent(): ?TemplateId
    {
        return $this->parent;
    }

    /**
     * Reports whether the decoded manifest contains the named top-level section.
     */
    public function hasSection(string $name): bool
    {
        return array_key_exists($name, $this->rawData);
    }

    /**
     * Returns a top-level manifest section, or null when it is not declared.
     *
     * @return mixed
     */
    public function section(string $name)
    {
        return $this->rawData[$name] ?? null;
    }

    /**
     * Returns all decoded data exactly as read from the manifest.
     */
    public function rawData(): array
    {
        return $this->rawData;
    }
}
