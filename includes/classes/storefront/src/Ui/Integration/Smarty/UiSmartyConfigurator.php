<?php

namespace Modified\Storefront\Ui\Integration\Smarty;

use Modified\Storefront\Template\Smarty\Resource\SmartyResourceContext;
use Smarty\Smarty;

/**
 * Registers the Smarty adapters owned by the Storefront UI.
 */
final class UiSmartyConfigurator
{
    public function configure(Smarty $smarty, ?SmartyResourceContext $resourceContext = null): void
    {
        $resourceContext ??= new SmartyResourceContext();
        $smarty->registerResource(
            'module',
            new ModuleTemplateResource(null, $resourceContext)
        );
    }
}
