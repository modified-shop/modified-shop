<?php
/**
 * API extension
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
 * Extending the API in order to easily support updating version
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_API extends Klarna
{

    /**
     * Constructor
     *
     * @param KiTT_Config $config configuration
     */
    public function __construct(KiTT_Config $config)
    {
        $module = $config->get("module");
        $version = $config->get("version");
        $this->VERSION = "PHP:{$module}:{$version}";
    }

}
