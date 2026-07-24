<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\ParentTemplateNotFoundException;
use Modified\Storefront\Template\Exception\TemplateInheritanceCycleException;

final class TemplateChainResolver
{
    private TemplateManifestRepository $manifestRepository;

    public function __construct(TemplateManifestRepository $manifestRepository)
    {
        $this->manifestRepository = $manifestRepository;
    }

    public function resolve(TemplateId $activeTemplate): TemplateChain
    {
        $this->manifestRepository->templateDirectory($activeTemplate);

        $chain = [];
        $visited = [];
        $templateId = $activeTemplate;

        while (true) {
            $key = $templateId->value();
            if (isset($visited[$key])) {
                $cycle = array_slice(array_keys($visited), $visited[$key]);
                $cycle[] = $key;

                throw new TemplateInheritanceCycleException(sprintf(
                    'Zyklische Template-Vererbung: %s.',
                    implode(' -> ', $cycle)
                ));
            }

            $visited[$key] = count($chain);
            $chain[] = $templateId;
            $parent = $this->manifestRepository->get($templateId)->parent();

            if ($parent === null) {
                return new TemplateChain($chain);
            }

            if (!$this->manifestRepository->templateExists($parent)) {
                throw new ParentTemplateNotFoundException(sprintf(
                    'Das Template "%s" deklariert den nicht vorhandenen Parent "%s".',
                    $templateId->value(),
                    $parent->value()
                ));
            }

            $templateId = $parent;
        }
    }
}
