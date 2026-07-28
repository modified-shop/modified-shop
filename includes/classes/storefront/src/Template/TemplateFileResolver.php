<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\CurrentTemplateFileException;
use Modified\Storefront\Template\Exception\InvalidTemplatePathException;
use Modified\Storefront\Template\Exception\TemplateFileNotFoundException;

/**
 * Locates logical template files along an inheritance chain.
 *
 * It returns the first matching override or, when resolving after the current
 * file, the next parent implementation of the same logical file.
 */
final class TemplateFileResolver
{
    private TemplateChain $chain;
    private TemplateManifestRepository $manifestRepository;

    /**
     * Creates a file resolver for an already resolved template inheritance chain.
     */
    public function __construct(
        TemplateChain $chain,
        TemplateManifestRepository $manifestRepository
    ) {
        $this->chain = $chain;
        $this->manifestRepository = $manifestRepository;
    }

    /**
     * Returns the first matching file in the chain or fails when none exists.
     *
     * For example, "module/product.html" may resolve to an override in the child.
     */
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

    /**
     * Finds the first matching file in the chain, returning null when none exists.
     */
    public function find(string $logicalName): ?ResolvedTemplateFile
    {
        $logicalName = TemplatePath::normalizeLogicalName($logicalName);

        return $this->findFromIndex($logicalName, 0);
    }

    /**
     * Returns all effective files in a directory, merged along the inheritance chain.
     *
     * Files are identified by their logical name. The first occurrence in the
     * chain wins, while files that exist only in a parent remain available.
     * The resulting effective list is naturally sorted by logical file name.
     *
     * @return list<ResolvedTemplateFile>
     */
    public function findAll(string $logicalDirectory, string $extension): array
    {
        $logicalDirectory = rtrim(
            TemplatePath::normalizeLogicalName($logicalDirectory),
            '/'
        ) . '/';
        $extension = $this->normalizeExtension($extension);
        $filesByLogicalName = [];

        foreach ($this->chain as $templateId) {
            $absoluteDirectory = $this->realCandidatePath($templateId, $logicalDirectory);

            if ($absoluteDirectory === null || !is_dir($absoluteDirectory)) {
                continue;
            }

            foreach (new \DirectoryIterator($absoluteDirectory) as $entry) {
                $fileName = $entry->getFilename();

                if (
                    $entry->isDot()
                    || str_starts_with($fileName, '.')
                    || !$entry->isFile()
                    || !str_ends_with($fileName, '.' . $extension)
                ) {
                    continue;
                }

                $logicalName = $logicalDirectory . $fileName;
                if (isset($filesByLogicalName[$logicalName])) {
                    continue;
                }

                $absolutePath = $this->realCandidatePath($templateId, $logicalName);
                if ($absolutePath !== null) {
                    $filesByLogicalName[$logicalName] = new ResolvedTemplateFile(
                        $templateId,
                        $logicalName,
                        $absolutePath
                    );
                }
            }
        }

        uksort(
            $filesByLogicalName,
            static function (string $left, string $right): int {
                $naturalComparison = strnatcasecmp($left, $right);

                return $naturalComparison !== 0
                    ? $naturalComparison
                    : strcmp($left, $right);
            }
        );

        return array_values($filesByLogicalName);
    }

    /**
     * Resolves the next inherited implementation after the specified current file.
     *
     * This lets a child override include the corresponding file from its parent.
     */
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

    /**
     * Returns the inheritance chain searched by this resolver.
     */
    public function chain(): TemplateChain
    {
        return $this->chain;
    }

    /**
     * Returns the canonical filesystem directory for a template in the repository.
     */
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

    private function normalizeExtension(string $extension): string
    {
        if (
            $extension === ''
            || preg_match('/^[a-zA-Z0-9]+$/D', $extension) !== 1
        ) {
            throw new InvalidTemplatePathException(sprintf(
                'Die Template-Dateiendung "%s" ist ungültig.',
                $extension
            ));
        }

        return $extension;
    }
}
