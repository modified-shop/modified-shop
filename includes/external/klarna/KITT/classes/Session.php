<?php
/**
 * Session wrapper
 * Dispatches calls to the AJAX provider
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
 * KiTT_Session
 *
 * Proxy for session function for testing
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_Session
{
    /**
     * Calls session_id()
     */
    public function session_id()
    {
        return session_id();
    }

    /**
     * Calls session_start
     *
     * @return bool
     */
    public function session_start()
    {
        return session_start();
    }

    /**
     * Calls session_destroy
     *
     * @return bool
     */
    public function session_destroy()
    {
        return session_destroy();
    }
}
