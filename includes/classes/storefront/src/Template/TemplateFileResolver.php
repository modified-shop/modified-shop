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
            $candidate = TemplatePath::joinFilesystem(
                $this->manifestRepository->templateDirectory($templateId),
                $logicalName
            );
            $candidateRealPath = realpath($candidate);

            if ($candidateRealPath !== false && str_replace('\\', '/', $candidateRealPath) === $currentRealPath) {
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
            $absolutePath = TemplatePath::joinFilesystem(
                $this->manifestRepository->templateDirectory($templateId),
                $logicalName
            );

            if (file_exists($absolutePath)) {
                return new ResolvedTemplateFile($templateId, $logicalName, $absolutePath);
            }
        }

        return null;
    }
}
