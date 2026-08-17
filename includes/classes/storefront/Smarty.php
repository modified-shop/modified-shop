<?php

use Modified\Storefront\Template\Smarty\ResourceNameTransformerInterface;
use Modified\Storefront\Template\Smarty\SmartyConfigurator;
use Smarty\Resource\BasePlugin;
use Smarty\Smarty as BaseSmarty;
use Smarty\Template as SmartyTemplate;

class Smarty extends BaseSmarty
{
    public function __construct()
    {
        parent::__construct();
        (new SmartyConfigurator())->configure($this);
    }

    /**
     * Overrides BaseSmarty::doCreateTemplate() to transform registered resource names
     * before Smarty's internal instance selection.
     *
     * @param string $resource_name
     * @param mixed|null $cache_id
     * @param mixed|null $compile_id
     * @param object|null $parent
     * @param bool|int|null $caching
     * @param mixed|null $cache_lifetime
     * @param bool $isConfig
     * @param array $data
     *
     * @inheritDoc
     */
    public function doCreateTemplate(
        $resource_name,
        $cache_id = null,
        $compile_id = null,
        $parent = null,
        $caching = null,
        $cache_lifetime = null,
        bool $isConfig = false,
        array $data = []
    ): SmartyTemplate {
        $resource = is_string($resource_name)
            ? $this->registeredResourceFor($resource_name)
            : null;
        if ($resource instanceof ResourceNameTransformerInterface) {
            $resource_name = $resource->transformResourceName(
                $resource_name,
                $parent instanceof SmartyTemplate ? $parent : null
            );
        }

        return parent::doCreateTemplate(
            $resource_name,
            $cache_id,
            $compile_id,
            $parent,
            $caching,
            $cache_lifetime,
            $isConfig,
            $data
        );
    }

    /**
     * Returns the explicitly registered resource for a qualified resource name.
     */
    private function registeredResourceFor(string $resourceName): ?BasePlugin
    {
        if (preg_match('/^([A-Za-z0-9_-]{2,}):/', $resourceName, $matches) !== 1) {
            return null;
        }

        $resource = $this->registered_resources[$matches[1]] ?? null;

        return $resource instanceof BasePlugin ? $resource : null;
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @deprecated Since Smarty 5. Use Smarty::addDefaultModifiers(), Smarty::addExtension(),
     * or Smarty::registerFilter() instead.
     */
    public function load_filter($type, $name)
    {
        // The snake_case entry point originates from modified/Smarty 4. Smarty 5 can still
        // register the filter function already loaded through the legacy plugin directories.
        return @$this->loadFilter($type, $name);
    }

    /**
     * @param null|string|SmartyTemplate $template
     * @param mixed|null $cache_id
     * @param mixed|null $compile_id
     *
     * @deprecated Since Smarty 5. Use isCached() instead.
     */
    public function is_cached($template, $cache_id = null, $compile_id = null)
    {
        return $this->isCached($template, $cache_id, $compile_id);
    }
}
