<?php

use Modified\Storefront\Template\Smarty\ResourceNameTransformerInterface;
use Modified\Storefront\Template\Smarty\SmartyConfigurator;
use Smarty\Resource\BasePlugin;
use Smarty\Smarty as BaseSmarty;
use Smarty\Template as SmartyTemplate;

class Smarty extends BaseSmarty
{
    private const LEGACY_DIRECTORY_ACCESSORS = [
        'template_dir' => 'TemplateDir',
        'config_dir' => 'ConfigDir',
        'plugins_dir' => 'PluginsDir',
        'compile_dir' => 'CompileDir',
        'cache_dir' => 'CacheDir',
    ];

    /** @var string[] */
    private array $legacyPluginDirectories = [];

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

    /**
     * @param string|array $tpl_var
     *
     * @deprecated Since Smarty 5. Use clearAssign() instead.
     */
    public function clear_assign($tpl_var)
    {
        $this->clearAssign($tpl_var);
    }

    /**
     * @param null|string|string[] $plugins_dir
     *
     * @return static
     *
     * @deprecated Since Smarty 5. Use addExtension() or registerPlugin() instead.
     */
    public function addPluginsDir($plugins_dir)
    {
        foreach ((array)$plugins_dir as $directory) {
            $this->legacyPluginDirectories[] = $this->_realpath(
                rtrim($directory ?? '', '/\\') . DIRECTORY_SEPARATOR,
                true
            );
        }

        return @parent::addPluginsDir($plugins_dir);
    }

    /**
     * @return string[]
     *
     * @deprecated Since Smarty 5. Plugin directories belong to the legacy extension API.
     */
    public function getPluginsDir()
    {
        return $this->legacyPluginDirectories;
    }

    /**
     * @param null|string|string[] $plugins_dir
     *
     * @return static
     *
     * @deprecated Since Smarty 5. Use addExtension() or registerPlugin() instead.
     */
    public function setPluginsDir($plugins_dir)
    {
        $this->legacyPluginDirectories = [];

        return $this->addPluginsDir($plugins_dir);
    }

    /**
     * @param string $name
     *
     * @return mixed
     *
     * @deprecated Since Smarty 5. Use the corresponding directory getter instead.
     */
    public function __get($name)
    {
        if (isset(self::LEGACY_DIRECTORY_ACCESSORS[$name])) {
            $method = 'get' . self::LEGACY_DIRECTORY_ACCESSORS[$name];

            return $this->{$method}();
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_NOTICE);

        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @deprecated Since Smarty 5. Use the corresponding directory setter instead.
     */
    public function __set($name, $value)
    {
        if (isset(self::LEGACY_DIRECTORY_ACCESSORS[$name])) {
            $method = 'set' . self::LEGACY_DIRECTORY_ACCESSORS[$name];
            $this->{$method}($value);

            return;
        }

        trigger_error('Undefined property: ' . static::class . '::$' . $name, E_USER_NOTICE);
    }

    /** @deprecated Since Smarty 5. Use clearAllAssign() instead. */
    public function clear_all_assign()
    {
        $this->clearAllAssign();
    }

    /** @deprecated Since Smarty 5. Use clearCache() instead. */
    public function clear_cache($tpl_file = null, $cache_id = null, $compile_id = null, $exp_time = null)
    {
        return $this->clearCache($tpl_file, $cache_id, $compile_id, $exp_time);
    }

    /** @deprecated Since Smarty 5. Use clearAllCache() instead. */
    public function clear_all_cache($exp_time = null)
    {
        return $this->clearAllCache($exp_time);
    }

    /** @deprecated Since Smarty 5. Use clearCompiledTemplate() instead. */
    public function clear_compiled_tpl($tpl_file = null, $compile_id = null, $exp_time = null)
    {
        return $this->clearCompiledTemplate($tpl_file, $compile_id, $exp_time);
    }

    /** @deprecated Since Smarty 5. Use templateExists() instead. */
    public function template_exists($tpl_file)
    {
        return $this->templateExists($tpl_file);
    }

    /** @deprecated Since Smarty 5. Use getTemplateVars() instead. */
    public function get_template_vars($name = null)
    {
        return $this->getTemplateVars($name);
    }

    /** @deprecated Since Smarty 5. Use getConfigVars() instead. */
    public function get_config_vars($name = null)
    {
        return $this->getConfigVars($name);
    }

    /** @deprecated Since Smarty 5. Use getRegisteredObject() instead. */
    public function get_registered_object($name)
    {
        return $this->getRegisteredObject($name);
    }

    /** @deprecated Since Smarty 5. Use clearConfig() instead. */
    public function clear_config($var = null)
    {
        $this->clearConfig($var);
    }

    /** @deprecated Since Smarty 5. Use trigger_error() directly instead. */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING)
    {
        trigger_error('Smarty error: ' . $error_msg, $error_type);
    }
}
