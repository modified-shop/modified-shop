<?php

namespace Modified\Storefront\Template;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Modified\Storefront\Template\Exception\InvalidTemplateIdException;
use Traversable;

/**
 * Represents the ordered inheritance chain of an active template.
 *
 * For a child extending "base", it keeps [child, base] in lookup order and
 * provides indexed, iterable access to the participating template IDs.
 */
final class TemplateChain implements Countable, IteratorAggregate
{
    private array $templates;

    /**
     * Creates a non-empty chain ordered from the active template to its ancestors.
     *
     * @param TemplateId[] $templates
     */
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

    /**
     * Returns the active template at the start of the chain.
     */
    public function current(): TemplateId
    {
        return $this->templates[0];
    }

    /**
     * Returns the template at the given zero-based position.
     */
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

    /**
     * Returns the template's zero-based position, or null when it is not in the chain.
     */
    public function indexOf(TemplateId $templateId): ?int
    {
        foreach ($this->templates as $index => $candidate) {
            if ($candidate->equals($templateId)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Returns all template ID values in lookup order, for example ["child", "base"].
     *
     * @return string[]
     */
    public function names(): array
    {
        return array_map(
            static fn (TemplateId $templateId): string => $templateId->value(),
            $this->templates
        );
    }

    /**
     * Returns the number of templates participating in the chain.
     */
    public function count(): int
    {
        return count($this->templates);
    }

    /**
     * Iterates over the template IDs in lookup order.
     *
     * @return Traversable<int, TemplateId>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->templates);
    }
}
