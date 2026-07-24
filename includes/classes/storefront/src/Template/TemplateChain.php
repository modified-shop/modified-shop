<?php

namespace Modified\Storefront\Template;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

final class TemplateChain implements Countable, IteratorAggregate
{
    private array $templates;

    public function __construct(array $templates)
    {
        if ($templates === []) {
            throw new InvalidTemplateIdException('Eine Template-Kette darf nicht leer sein.');
        }

        foreach ($templates as $template) {
            if (!$template instanceof TemplateId) {
                throw new InvalidTemplateIdException('Eine Template-Kette darf nur Template-IDs enthalten.');
            }
        }

        $this->templates = array_values($templates);
    }

    public function current(): TemplateId
    {
        return $this->templates[0];
    }

    public function at(int $index): TemplateId
    {
        if (!isset($this->templates[$index])) {
            throw new InvalidTemplateIdException(sprintf(
                'Die Template-Kette enthält keinen Eintrag an Position %d.',
                $index
            ));
        }

        return $this->templates[$index];
    }

    public function indexOf(TemplateId $templateId): ?int
    {
        foreach ($this->templates as $index => $candidate) {
            if ($candidate->equals($templateId)) {
                return $index;
            }
        }

        return null;
    }

    public function names(): array
    {
        return array_map(
            static fn (TemplateId $templateId): string => $templateId->value(),
            $this->templates
        );
    }

    public function count(): int
    {
        return count($this->templates);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->templates);
    }
}
