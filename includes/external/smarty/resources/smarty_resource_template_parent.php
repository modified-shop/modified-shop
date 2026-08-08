<?php

use Modified\Storefront\Template\Exception\CurrentTemplateFileException;
use Modified\Storefront\Template\Exception\TemplateFileNotFoundException;
use Modified\Storefront\Template\TemplateFileResolver;
use Modified\Storefront\Template\TemplateRuntime;

final class Smarty_Resource_Template_Parent extends Smarty_Resource
{
    private ?TemplateFileResolver $resolver;

    public function __construct(?TemplateFileResolver $resolver = null)
    {
        $this->resolver = $resolver;
    }

    public function populate(Smarty_Template_Source $source, ?Smarty_Internal_Template $_template = null): void
    {
        $source->filepath = $this->resolvePath($source->name, $_template);

        if ($source->filepath === false) {
            $source->timestamp = $source->exists = false;
            return;
        }

        $source->exists = true;
        $source->uid = sha1($source->filepath);
        $source->timestamp = filemtime($source->filepath);
    }

    public function populateTimestamp(Smarty_Template_Source $source): void
    {
        $source->exists = is_file($source->filepath);
        $source->timestamp = $source->exists ? filemtime($source->filepath) : false;
    }

    public function getContent(Smarty_Template_Source $source): string
    {
        if ($source->exists) {
            return (string) file_get_contents($source->filepath);
        }

        throw new SmartyException("Unable to read template {$source->type} '{$source->name}'");
    }

    public function getBasename(Smarty_Template_Source $source): string
    {
        return basename($source->filepath);
    }

    private function resolvePath(string $logicalName, ?Smarty_Internal_Template $_template)
    {
        $currentPath = $this->currentTemplatePath($_template);
        if ($currentPath === null) {
            return false;
        }

        try {
            return $this->resolver()
                ->resolveAfter($logicalName, $currentPath)
                ->absolutePath();
        } catch (TemplateFileNotFoundException | CurrentTemplateFileException $exception) {
            return false;
        }
    }

    private function currentTemplatePath(?Smarty_Internal_Template $_template): ?string
    {
        if (
            $_template === null
            || $_template->parent === null
            || !isset($_template->parent->source->filepath)
            || !is_string($_template->parent->source->filepath)
        ) {
            return null;
        }

        return $_template->parent->source->filepath;
    }

    private function resolver(): TemplateFileResolver
    {
        if ($this->resolver === null) {
            $this->resolver = TemplateRuntime::get()->fileResolver();
        }

        return $this->resolver;
    }
}
