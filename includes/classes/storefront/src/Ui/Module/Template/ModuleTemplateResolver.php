<?php

namespace Modified\Storefront\Ui\Module\Template;

use Modified\Storefront\Module\Exception\ModuleRootNotFoundException;
use Modified\Storefront\Module\ModuleId;
use Modified\Storefront\Module\ModuleRootResolver;
use Modified\Storefront\Template\FilesystemPath;
use Modified\Storefront\Template\LogicalTemplatePath;
use Modified\Storefront\Template\TemplateChain;
use Modified\Storefront\Template\TemplateFileResolver;
use Modified\Storefront\Template\TemplateId;
use Modified\Storefront\Ui\Module\Template\Exception\CurrentModuleTemplateException;
use Modified\Storefront\Ui\Module\Template\Exception\InvalidModuleTemplateNameException;
use Modified\Storefront\Ui\Module\Template\Exception\ModuleTemplateNotFoundException;

/**
 * Resolves module templates through template overrides, module variants, and module default.
 */
final class ModuleTemplateResolver
{
    private TemplateChain $templateChain;
    private TemplateFileResolver $templateFileResolver;
    private ModuleRootResolver $moduleRootResolver;

    public function __construct(
        TemplateChain $templateChain,
        TemplateFileResolver $templateFileResolver,
        ModuleRootResolver $moduleRootResolver
    ) {
        $this->templateChain = $templateChain;
        $this->templateFileResolver = $templateFileResolver;
        $this->moduleRootResolver = $moduleRootResolver;
    }

    /**
     * Resolves the first existing module template in the complete candidate sequence.
     *
     * Example: "modified/paypal" and "checkout/button.html" first check the active
     * template override, then parent overrides, module variants, and the module default.
     *
     * @throws InvalidModuleTemplateNameException
     * @throws ModuleTemplateNotFoundException
     */
    public function resolve(ModuleId $moduleId, LogicalTemplatePath $logicalPath): ResolvedModuleTemplate
    {
        $resolved = $this->findFromIndex($this->candidates($moduleId, $logicalPath), 0);

        if ($resolved === null) {
            throw $this->notFound($moduleId, $logicalPath);
        }

        return $resolved;
    }

    /**
     * Resolves the first existing candidate after the current module template.
     *
     * Example: after an active-template override, the next match may be a parent-template
     * override, a module variant, or the module default.
     *
     * @throws CurrentModuleTemplateException
     * @throws InvalidModuleTemplateNameException
     * @throws ModuleTemplateNotFoundException
     */
    public function resolveAfter(
        ModuleId $moduleId,
        LogicalTemplatePath $logicalPath,
        string $currentAbsolutePath
    ): ResolvedModuleTemplate {
        $currentPath = FilesystemPath::canonicalize($currentAbsolutePath);
        if ($currentPath === null || !is_file($currentPath)) {
            throw new CurrentModuleTemplateException(sprintf(
                'The current module template file "%s" does not exist.',
                $currentAbsolutePath
            ));
        }

        $candidates = $this->candidates($moduleId, $logicalPath);
        $currentCandidate = $this->findCandidateByAbsolutePath($candidates, $currentPath);
        if ($currentCandidate === null) {
            throw new CurrentModuleTemplateException(sprintf(
                'The current file "%s" does not belong to the candidate sequence for module "%s" and template "%s".',
                $currentAbsolutePath,
                $moduleId->value(),
                $logicalPath->value()
            ));
        }

        $nextCandidate = $this->findAfterCandidate($candidates, $currentCandidate);
        if ($nextCandidate === null) {
            throw $this->notFound($moduleId, $logicalPath);
        }

        return $nextCandidate;
    }

    /** @return list<ResolvedModuleTemplate> */
    private function candidates(ModuleId $moduleId, LogicalTemplatePath $logicalPath): array
    {
        $candidates = [];
        try {
            $moduleRoot = $this->moduleRootResolver->resolve($moduleId)->absolutePath();
        } catch (ModuleRootNotFoundException $exception) {
            return [];
        }

        foreach ($this->templateChain as $templateId) {
            $relativeName = FilesystemPath::join(
                'module_templates',
                $moduleId->value(),
                $logicalPath->value()
            );
            $candidates[] = $this->candidateWithin(
                $moduleId,
                $logicalPath,
                $this->templateFileResolver->templateDirectory($templateId),
                $relativeName,
                ResolvedModuleTemplate::TEMPLATE_OVERRIDE,
                $templateId
            );
        }

        foreach ($this->templateChain as $templateId) {
            $candidates[] = $this->candidateWithin(
                $moduleId,
                $logicalPath,
                $moduleRoot,
                FilesystemPath::join(
                    'templates',
                    $templateId->value(),
                    $logicalPath->value()
                ),
                ResolvedModuleTemplate::MODULE_VARIANT,
                $templateId
            );
        }

        $candidates[] = $this->candidateWithin(
            $moduleId,
            $logicalPath,
            $moduleRoot,
            FilesystemPath::join('templates/default', $logicalPath->value()),
            ResolvedModuleTemplate::MODULE_DEFAULT
        );

        return $candidates;
    }

    private function candidateWithin(
        ModuleId $moduleId,
        LogicalTemplatePath $logicalPath,
        string $root,
        string $relativePath,
        string $origin,
        ?TemplateId $sourceTemplate = null
    ): ResolvedModuleTemplate {
        $candidate = FilesystemPath::join($root, $relativePath);
        $resolved = FilesystemPath::canonicalize($candidate);
        $absolutePath = $resolved ?? FilesystemPath::normalize($candidate);

        if ($resolved !== null && !FilesystemPath::isWithin($absolutePath, $root)) {
            throw new InvalidModuleTemplateNameException(sprintf(
                'The module template file "%s" leaves its permitted root.',
                $logicalPath->value()
            ));
        }

        return new ResolvedModuleTemplate(
            $moduleId,
            $logicalPath,
            $absolutePath,
            $origin,
            $sourceTemplate
        );
    }

    /** @param list<ResolvedModuleTemplate> $candidates */
    private function findCandidateByAbsolutePath(
        array $candidates,
        string $absolutePath
    ): ?ResolvedModuleTemplate {
        foreach ($candidates as $candidate) {
            if ($candidate->absolutePath() === $absolutePath) {
                return $candidate;
            }
        }

        return null;
    }

    /** @param list<ResolvedModuleTemplate> $candidates */
    private function findAfterCandidate(
        array $candidates,
        ResolvedModuleTemplate $currentCandidate
    ): ?ResolvedModuleTemplate {
        $currentIndex = $this->findCandidateIndex($candidates, $currentCandidate);

        return $currentIndex === null
            ? null
            : $this->findFromIndex($candidates, $currentIndex + 1);
    }

    /** @param list<ResolvedModuleTemplate> $candidates */
    private function findCandidateIndex(
        array $candidates,
        ResolvedModuleTemplate $searchedCandidate
    ): ?int {
        foreach ($candidates as $index => $candidate) {
            if ($candidate === $searchedCandidate) {
                return $index;
            }
        }

        return null;
    }

    /** @param list<ResolvedModuleTemplate> $candidates */
    private function findFromIndex(array $candidates, int $startIndex): ?ResolvedModuleTemplate
    {
        foreach (array_slice($candidates, $startIndex) as $candidate) {
            if (is_file($candidate->absolutePath())) {
                return $candidate;
            }
        }

        return null;
    }

    private function notFound(
        ModuleId $moduleId,
        LogicalTemplatePath $logicalPath
    ): ModuleTemplateNotFoundException {
        return new ModuleTemplateNotFoundException(sprintf(
            'The module template file "%s" for module "%s" was not found.',
            $logicalPath->value(),
            $moduleId->value()
        ));
    }
}
