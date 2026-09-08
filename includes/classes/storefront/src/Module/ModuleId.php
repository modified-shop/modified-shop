<?php

namespace Modified\Storefront\Module;

use Modified\Storefront\Module\Exception\InvalidModuleIdException;
use Stringable;

/**
 * Identifies a module by its validated vendor/module pair.
 */
final class ModuleId implements Stringable
{
    private string $value;

    public function __construct(string $value)
    {
        if (!$this->isValidModuleName($value)) {
            throw new InvalidModuleIdException(sprintf('Invalid module ID "%s".', $value));
        }

        $this->value = $value;
    }

    /**
     * Validates a module identifier in the "vendor/module" format.
     *
     * Valid examples:
     * - "modified/checkout"
     * - "acme/widget"
     * - "acme/feature-toggle"
     *
     * Invalid example:
     * - "modified"
     * - "modified/"
     * - "modified/feature-"
     *
     * @param string $value The candidate module identifier.
     * @return bool True if the value matches the allowed module ID pattern.
     */
    private function isValidModuleName(string $value): bool
    {
        return preg_match(
            '/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\/[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/D',
            $value
        ) === 1;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
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
