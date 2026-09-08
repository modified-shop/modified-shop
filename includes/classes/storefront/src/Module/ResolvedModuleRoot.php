<?php

namespace Modified\Storefront\Module;

/**
 * Describes the one installation root selected for a module.
 */
final class ResolvedModuleRoot
{
    public const EXTENSION = 'extension';
    public const LEGACY = 'legacy';

    private ModuleId $moduleId;
    private string $absolutePath;
    private string $origin;
    private ?string $ignoredDuplicatePath;

    public function __construct(
        ModuleId $moduleId,
        string $absolutePath,
        string $origin,
        ?string $ignoredDuplicatePath = null
    ) {
        $this->moduleId = $moduleId;
        $this->absolutePath = $absolutePath;
        $this->origin = $origin;
        $this->ignoredDuplicatePath = $ignoredDuplicatePath;
    }

    public function moduleId(): ModuleId
    {
        return $this->moduleId;
    }

    public function absolutePath(): string
    {
        return $this->absolutePath;
    }

    public function origin(): string
    {
        return $this->origin;
    }

    public function hasDuplicateInstallation(): bool
    {
        return $this->ignoredDuplicatePath !== null;
    }

    public function ignoredDuplicatePath(): ?string
    {
        return $this->ignoredDuplicatePath;
    }
}
