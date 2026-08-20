<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\InvalidTemplateIdException;
use Stringable;

/**
 * Represents a validated template identifier as a value object.
 *
 * Names such as "custom-shop" can safely identify template directories, while
 * malformed values containing path separators or invalid edge characters fail.
 */
final class TemplateId implements Stringable
{
    private string $value;

    /**
     * Creates a template ID after validating that it is safe as a directory name.
     */
    public function __construct(string $value)
    {
        if (preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9_-]*[a-zA-Z0-9])?$/', $value) !== 1) {
            throw new InvalidTemplateIdException(sprintf('Ungültige Template-ID "%s".', $value));
        }

        $this->value = $value;
    }

    /**
     * Returns the validated identifier, for example "custom-shop".
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Reports whether another template ID contains the same value.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Returns the validated identifier when the value object is used as a string.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
