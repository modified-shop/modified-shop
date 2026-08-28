<?php

namespace Modified\Storefront\Template;

use JsonException;
use Modified\Storefront\Template\Exception\InvalidTemplateIdException;
use Modified\Storefront\Template\Exception\TemplateManifestInvalidException;
use Modified\Storefront\Template\Exception\TemplateNotFoundException;
use stdClass;

/**
 * Loads and caches manifests from the configured templates directory.
 *
 * It validates template directories and template.json files, and supplies an
 * empty manifest when an existing template has no manifest of its own.
 */
final class TemplateManifestRepository
{
    private string $templatesDirectory;
    private array $cache = [];

    /**
     * Creates a repository rooted at an existing templates directory.
     */
    public function __construct(string $templatesDirectory)
    {
        $realDirectory = FilesystemPath::canonicalize($templatesDirectory);
        if ($realDirectory === null || !is_dir($realDirectory)) {
            throw new TemplateNotFoundException(sprintf(
                'Das Template-Verzeichnis "%s" ist nicht vorhanden.',
                $templatesDirectory
            ));
        }

        $this->templatesDirectory = $realDirectory;
    }

    /**
     * Loads and caches a template manifest, or returns an empty one if absent.
     */
    public function get(TemplateId $templateId): TemplateManifest
    {
        $key = $templateId->value();
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $templateDirectory = $this->templateDirectory($templateId);
        $manifestPath = FilesystemPath::join($templateDirectory, 'template.json');
        if (!is_file($manifestPath)) {
            return $this->cache[$key] = TemplateManifest::empty($templateId);
        }

        $realManifestPath = FilesystemPath::canonicalize($manifestPath);
        if (
            $realManifestPath === null
            || !FilesystemPath::isWithin($realManifestPath, $templateDirectory)
        ) {
            throw new TemplateManifestInvalidException(sprintf(
                'Das Template-Manifest für "%s" liegt außerhalb seines Template-Verzeichnisses.',
                $templateId->value()
            ));
        }

        $contents = file_get_contents($manifestPath);
        if ($contents === false) {
            throw new TemplateManifestInvalidException(sprintf(
                'Das Template-Manifest "%s" konnte nicht gelesen werden.',
                $manifestPath
            ));
        }

        try {
            $decoded = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new TemplateManifestInvalidException(sprintf(
                'Ungültiges Template-Manifest für "%s": %s',
                $templateId->value(),
                $exception->getMessage()
            ), 0, $exception);
        }

        if (!$decoded instanceof stdClass) {
            throw new TemplateManifestInvalidException(sprintf(
                'Das Template-Manifest für "%s" muss ein JSON-Objekt enthalten.',
                $templateId->value()
            ));
        }

        $rawData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        $parent = $this->readParent($templateId, $rawData);

        return $this->cache[$key] = new TemplateManifest($templateId, $parent, $rawData);
    }

    /**
     * Reports whether a directory currently exists for the given template ID.
     */
    public function templateExists(TemplateId $templateId): bool
    {
        return is_dir($this->unresolvedTemplateDirectory($templateId));
    }

    /**
     * Returns the canonical template directory and ensures it stays within the root.
     */
    public function templateDirectory(TemplateId $templateId): string
    {
        $directory = $this->unresolvedTemplateDirectory($templateId);
        $realDirectory = FilesystemPath::canonicalize($directory);

        if ($realDirectory === null || !is_dir($realDirectory)) {
            throw new TemplateNotFoundException(sprintf(
                'Das Template "%s" ist nicht vorhanden.',
                $templateId->value()
            ));
        }

        if (!FilesystemPath::isWithin($realDirectory, $this->templatesDirectory)) {
            throw new TemplateNotFoundException(sprintf(
                'Das Template "%s" liegt außerhalb des Template-Verzeichnisses.',
                $templateId->value()
            ));
        }

        return $realDirectory;
    }

    /**
     * Returns the canonical root directory containing all templates.
     */
    public function templatesDirectory(): string
    {
        return $this->templatesDirectory;
    }

    /**
     * Clears all cached manifests so subsequent reads use the filesystem again.
     */
    public function clear(): void
    {
        $this->cache = [];
    }

    private function unresolvedTemplateDirectory(TemplateId $templateId): string
    {
        return FilesystemPath::join($this->templatesDirectory, $templateId->value());
    }

    private function readParent(TemplateId $templateId, array $rawData): ?TemplateId
    {
        if (!array_key_exists('parent', $rawData)) {
            return null;
        }

        if (!is_string($rawData['parent']) || $rawData['parent'] === '') {
            throw new TemplateManifestInvalidException(sprintf(
                'Der Parent im Template-Manifest für "%s" muss eine gültige Template-ID sein.',
                $templateId->value()
            ));
        }

        try {
            return new TemplateId($rawData['parent']);
        } catch (InvalidTemplateIdException $exception) {
            throw new TemplateManifestInvalidException(sprintf(
                'Ungültiger Parent "%s" im Template-Manifest für "%s".',
                $rawData['parent'],
                $templateId->value()
            ), 0, $exception);
        }
    }
}
