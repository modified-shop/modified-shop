<?php

namespace Modified\Storefront\Template;

use Modified\Storefront\Template\Exception\InvalidTemplatePathException;

/**
 * Identifies a concrete template file by its logical path within a search space.
 */
final class LogicalTemplatePath
{
    private string $value;

    public function __construct(string $value)
    {
        $value = TemplatePath::normalizeLogicalName($value);

        if (str_ends_with($value, '/')) {
            throw new InvalidTemplatePathException(sprintf(
                'The logical template path "%s" must designate a template file.',
                $value
            ));
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }
}
