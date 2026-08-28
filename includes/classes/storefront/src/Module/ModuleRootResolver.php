<?php

namespace Modified\Storefront\Module;

use Modified\Storefront\Module\Exception\InvalidModuleRootException;
use Modified\Storefront\Module\Exception\ModuleRootNotFoundException;
use Modified\Storefront\Template\FilesystemPath;

/**
 * Selects one complete module installation, preferring extensions over legacy modules.
 */
final class ModuleRootResolver
{
    private string $shopRoot;

    public function __construct(string $shopRoot)
    {
        $resolvedRoot = FilesystemPath::canonicalize($shopRoot);
        if ($resolvedRoot === null || !is_dir($resolvedRoot)) {
            throw new InvalidModuleRootException(sprintf('The shop root "%s" does not exist.', $shopRoot));
        }

        $this->shopRoot = $resolvedRoot;
    }

    /**
     * Resolves the preferred installation root for a module.
     *
     * Example: "modified/paypal" resolves to "extensions/modified/paypal" when present,
     * otherwise to "includes/external/modified/paypal". If both exist, the extension
     * root is selected and the legacy root is recorded as the ignored duplicate.
     *
     * @throws InvalidModuleRootException
     * @throws ModuleRootNotFoundException
     */
    public function resolve(ModuleId $moduleId): ResolvedModuleRoot
    {
        $extension = $this->moduleRoot('extensions', $moduleId);
        $legacy = $this->moduleRoot('includes/external', $moduleId);

        if ($extension !== null) {
            return new ResolvedModuleRoot(
                $moduleId,
                $extension,
                ResolvedModuleRoot::EXTENSION,
                $legacy
            );
        }

        if ($legacy !== null) {
            return new ResolvedModuleRoot($moduleId, $legacy, ResolvedModuleRoot::LEGACY);
        }

        throw new ModuleRootNotFoundException(sprintf(
            'Neither an extension nor a legacy root was found for module "%s".',
            $moduleId->value()
        ));
    }

    /**
     * Finds the module root within a specific installation base.
     *
     * Example: moduleRoot('extensions', ModuleId::fromString('modified/paypal'))
     * returns /shop/extensions/modified/paypal when it exists.
     */
    private function moduleRoot(string $relativeBase, ModuleId $moduleId): ?string
    {
        $base = FilesystemPath::join($this->shopRoot, $relativeBase);
        $candidate = FilesystemPath::join($base, $moduleId->value());
        $resolved = FilesystemPath::canonicalize($candidate);

        if ($resolved === null || !is_dir($resolved)) {
            return null;
        }

        $resolvedBase = FilesystemPath::canonicalize($base) ?? FilesystemPath::normalize($base);

        if (!FilesystemPath::isWithin($resolved, $resolvedBase)) {
            throw new InvalidModuleRootException(sprintf(
                'The module root "%s" leaves the permitted installation directory.',
                $candidate
            ));
        }

        return $resolved;
    }
}
