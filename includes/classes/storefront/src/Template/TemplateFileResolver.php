<?php

namespace Modified\Storefront\Template;

final class TemplateFileResolver
{
    private TemplateChain $chain;
    private TemplateManifestRepository $manifestRepository;

    public function __construct(
        TemplateChain $chain,
        TemplateManifestRepository $manifestRepository
    ) {
        $this->chain = $chain;
        $this->manifestRepository = $manifestRepository;
    }

    public function resolve(string $logicalName): ResolvedTemplateFile
    {
        $resolved = $this->find($logicalName);
        if ($resolved === null) {
            throw new TemplateFileNotFoundException(sprintf(
                'Die Template-Datei "%s" wurde in der Template-Kette [%s] nicht gefunden.',
                $logicalName,
                implode(', ', $this->chain->names())
            ));
        }

        return $resolved;
    }

    public function find(string $logicalName): ?ResolvedTemplateFile
    {
        $logicalName = TemplatePath::normalizeLogicalName($logicalName);

        return $this->findFromIndex($logicalName, 0);
    }

    public function resolveAfter(string $logicalName, string $currentAbsolutePath): ResolvedTemplateFile
    {
        $logicalName = TemplatePath::normalizeLogicalName($logicalName);
        $currentRealPath = realpath($currentAbsolutePath);

        if ($currentRealPath === false) {
            throw new CurrentTemplateFileException(sprintf(
                'Die aktuelle Template-Datei "%s" ist nicht vorhanden.',
                $currentAbsolutePath
            ));
        }

        $currentRealPath = str_replace('\\', '/', $currentRealPath);
        foreach ($this->chain as $index => $templateId) {
            $candidateRealPath = $this->realCandidatePath($templateId, $logicalName);

            if ($candidateRealPath !== null && $candidateRealPath === $currentRealPath) {
                $resolved = $this->findFromIndex($logicalName, $index + 1);
                if ($resolved !== null) {
                    return $resolved;
                }

                throw new TemplateFileNotFoundException(sprintf(
                    'Für die Template-Datei "%s" existiert hinter "%s" keine weitere Variante.',
                    $logicalName,
                    $templateId->value()
                ));
            }
        }

        throw new CurrentTemplateFileException(sprintf(
            'Die aktuelle Datei "%s" gehört für "%s" nicht zur wirksamen Template-Kette.',
            $currentAbsolutePath,
            $logicalName
        ));
    }

    public function chain(): TemplateChain
    {
        return $this->chain;
    }

    public function templateDirectory(TemplateId $templateId): string
    {
        return $this->manifestRepository->templateDirectory($templateId);
    }

    private function findFromIndex(string $logicalName, int $startIndex): ?ResolvedTemplateFile
    {
        for ($index = $startIndex; $index < $this->chain->count(); ++$index) {
            $templateId = $this->chain->at($index);
            $absolutePath = $this->realCandidatePath($templateId, $logicalName);

            if ($absolutePath !== null) {
                return new ResolvedTemplateFile($templateId, $logicalName, $absolutePath);
            }
        }

        return null;
    }

    private function realCandidatePath(TemplateId $templateId, string $logicalName): ?string
    {
        $templateDirectory = $this->manifestRepository->templateDirectory($templateId);
        $candidate = TemplatePath::joinFilesystem($templateDirectory, $logicalName);
        $realCandidate = realpath($candidate);

        if ($realCandidate === false) {
            return null;
        }

        $realCandidate = str_replace('\\', '/', $realCandidate);
        if (
            $realCandidate !== $templateDirectory
            && !str_starts_with($realCandidate, $templateDirectory . '/')
        ) {
            throw new InvalidTemplatePathException(sprintf(
                'Die Template-Datei "%s" verlässt das Verzeichnis des Templates "%s".',
                $logicalName,
                $templateId->value()
            ));
        }

        return $realCandidate;
    }
}
