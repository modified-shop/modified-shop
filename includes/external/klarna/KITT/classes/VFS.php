<?php
/**
 * KiTT Filesystem Wrapper
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
 * KiTT_VFS used to abstract some filesystem methods
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_VFS
{
    /**
     * checks if file exists
     *
     * @param string $filename filename
     *
     * @return bool
     */
    public function file_exists($filename)
    {
        return file_exists($filename);
    }

    /**
     * get contents of file
     *
     * @param string $filename filename
     *
     * @return string
     */
    public function file_get_contents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * Join arguments together as a path
     *
     * @return string
     */
    public function join()
    {
        $args = func_get_args();
        $paths = array();
        foreach ($args as $arg) {
            $paths = array_merge($paths, (array)$arg);
        }

        foreach ($paths as &$path) {
            $path = trim($path, '/');
        }

        // remove empty elements from the array
        $paths = array_filter($paths);

        // make sure if the path was originally an absolute
        // path that it is kept that way
        if (substr($args[0], 0, 1) == '/') {
            $paths[0] = '/' . $paths[0];
        }

        return join('/', $paths);
    }
}
