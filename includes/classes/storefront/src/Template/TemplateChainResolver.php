<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\ParentTemplateNotFoundException;
use Modified\Storefront\Template\Exception\TemplateInheritanceCycleException;

/**
 * Builds the complete inheritance chain for an active template.
 *
 * It follows the parent declarations in the manifests, for example from
 * "custom" through "base", while detecting missing parents and cycles.
 */
final class TemplateChainResolver
{
    private TemplateManifestRepository $manifestRepository;

    /**
     * Creates a resolver backed by the repository containing the template manifests.
     */
    public function __construct(TemplateManifestRepository $manifestRepository)
    {
        $this->manifestRepository = $manifestRepository;
    }

    /**
     * Resolves the active template and every declared parent into lookup order.
     *
     * For example, a "custom" template extending "base" produces [custom, base].
     */
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
