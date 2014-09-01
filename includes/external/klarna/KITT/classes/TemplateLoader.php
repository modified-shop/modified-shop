<?php
/**
 * Loader of templates
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * KiTT_TemplateException
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_TemplateException extends Exception
{

}


/**
 * KiTT_TemplateLoader
 *
 * Handles loading of templates
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_TemplateLoader
{
    /**
     * @var KiTT_Config
     */
    private $_config;

    /**
     * @var KiTT_Locale
     */
    private $_locale;

    /**
     * @var KiTT_VFS
     */
    private $_vfs;

    /**
     * @var array
     */
    private $_cache;

    /**
     * Construct a TemplateLoader
     *
     * @param KiTT_Configuration $config configuration object
     * @param KiTT_Locale        $locale locale
     * @param KiTT_VFS           $vfs    vfs wrapper
     * @param array              &$cache cache array
     *
     * @return void
     */
    public function __construct($config, $locale, $vfs, &$cache)
    {
        $this->_config = $config;
        $this->_locale = $locale;
        $this->_vfs = $vfs;
        $this->_cache = &$cache;
    }

    /**
     * Assemble all possible paths for the wanted template
     *
     * @param string $name template name
     *
     * @return array
     */
    private function _templatePaths($name)
    {
        $basepaths = array();

        try {
            $basepaths[] = $this->_config->get('paths/extra_templates');
        } catch (KiTT_MissingConfigurationException $e) {
        }

        $basepaths[] = $this->_vfs->join(
            $this->_config->get('paths/kitt'),
            'html'
        );

        $paths = array();
        foreach ($basepaths as $index => $basepath) {
            $paths[] = $this->_vfs->join(
                $basepath,
                strtolower($this->_locale->getCountryCode()),
                $name
            );

            $paths[] = $this->_vfs->join(
                $basepath,
                $name
            );
        }

        return $paths;
    }

    /**
     * Load the given template.
     * If found in the cache it will load that template.
     * If not the following search priority is used:
     *      1: $config['paths']['extra_templates']/#countrycode#/
     *      2: $config['paths']['extra_templates']/
     *      3: $config['paths']['kitt']/html/#countrycode#/
     *      4: $config['paths']['kitt']/html/
     * First matching will be loaded.
     *
     * @param string $name template name to load
     *
     * @throws KiTT_TemplateException if template is not found.
     * @return KiTT_Template
     */
    public function load($name)
    {
        if (array_key_exists($name, $this->_cache)) {
            return $this->_cache[$name];
        }

        $paths = $this->_templatePaths($name);
        foreach ($paths as $path) {
            if ($this->_vfs->file_exists($path)) {
                $data = $this->_vfs->file_get_contents($path);
                $this->_cache[$name] = new KiTT_Template($this, $path, $data);
                return $this->_cache[$name];
            }
        }
        throw new KiTT_TemplateException("Template not found: {$name}");
    }
}
