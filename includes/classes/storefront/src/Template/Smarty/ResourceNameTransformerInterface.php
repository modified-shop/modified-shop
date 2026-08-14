<?php

namespace Modified\Storefront\Template\Smarty;

use Smarty\Template;

/**
 * Transforms a resource name before Smarty internally selects the template instance.
 *
 * This allows a resource to include caller context in its identity without requiring the global
 * Smarty integration to know the concrete resource type or its name format.
 */
interface ResourceNameTransformerInterface
{
    /**
     * Returns the resource name to use when creating the template.
     */
    public function transformResourceName(string $resourceName, ?Template $callingTemplate): string;
}
