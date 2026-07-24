<?php

namespace Modified\Storefront\Template;

use Stringable;

final class TemplateId implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        if (preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9_-]*[a-zA-Z0-9])?$/', $value) !== 1) {
            throw new InvalidTemplateIdException(sprintf('Ungültige Template-ID "%s".', $value));
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
