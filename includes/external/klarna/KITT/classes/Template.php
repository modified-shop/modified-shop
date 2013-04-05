<?php
/**
 * Template renderer
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
 * KiTT Mustache Extension
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Mustache extends Mustache
{
    /**
     * @var KiTT_TemplateLoader
     */
    private $_loader;

    /**
     * Create an instance of KiTT_Mustache
     *
     * @param KiTT_TemplateLoader $loader Template Loader
     *
     * @return void
     */
    public function __construct(KiTT_TemplateLoader $loader)
    {
        $this->_loader = $loader;
    }

    /**
     * Override _getPartial to autoload partials.
     *
     * @param string $tag_name partial name
     *
     * @return string
     */
    protected function _getPartial($tag_name)
    {
        return $this->_loader->load($tag_name . '.mustache')->getTemplate();
    }
}

/**
 * KiTT_Template
 *
 * Extends mustache with default partials and convience methods
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Template
{
    /**
     * @var KiTT_Mustache
     */
    private $_mustache;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var string
     */
    private $_template;

    /**
     * Create a template object.
     *
     * @param KiTT_TemplateLoader $loader   Template Loader object
     * @param string              $path     path
     * @param string              $template template
     *
     * @return void
     */
    public function __construct(KiTT_TemplateLoader $loader, $path, $template)
    {
        $this->_mustache = new KiTT_Mustache($loader);
        $this->_path = $path;
        $this->_template = $template;
    }

    /**
     * Get the path of the template
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Get the template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Fill the template with the supplied data and render it.
     *
     * @param mixed $data data to fill the template with
     *
     * @return string
     */
    public function render($data)
    {
        return $this->_mustache->render(
            $this->_template,
            $data
        );
    }
}
