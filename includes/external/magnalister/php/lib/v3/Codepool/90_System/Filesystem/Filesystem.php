<?php
/**
 * 888888ba                 dP  .88888.                    dP                
 * 88    `8b                88 d8'   `88                   88                
 * 88aaaa8P' .d8888b. .d888b88 88        .d8888b. .d8888b. 88  .dP  .d8888b. 
 * 88   `8b. 88ooood8 88'  `88 88   YP88 88ooood8 88'  `"" 88888"   88'  `88 
 * 88     88 88.  ... 88.  .88 Y8.   .88 88.  ... 88.  ... 88  `8b. 88.  .88 
 * dP     dP `88888P' `88888P8  `88888'  `88888P' `88888P' dP   `YP `88888P' 
 *
 *                          m a g n a l i s t e r
 *                                      boost your Online-Shop
 *
 * -----------------------------------------------------------------------------
 * $Id$
 *
 * (c) 2010 - 2015 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */


/**
 * A helper class for handle with files inside filesystem
 */
class ML_Filesystem_Filesystem {

    /**
     * get full path to file (clean)
     * if file not starts with / it will prepend path/to/lib
     * @param string $sInputPath
     * @return string 
     * @throws Exception path outside root
     * @see http://php.net/manual/en/function.realpath.php#81935
     */
    public function getFullPath($sInputPath) {
        $sInputPath = str_replace(array('\\'), array('/'), $sInputPath); // to linux separator

        $aPath = array();
        $iDeep = 0;
        foreach (explode('/', $sInputPath) as $iCount => $sSubPath) {
            if ($sSubPath == '' || $sSubPath == '.') {
                continue;
            }
            if ($sSubPath == '..' && $iCount > 0 && end($aPath) != '..') {
                $iDeep--;
                array_pop($aPath);
            } else {
                $iDeep++;
                $aPath[] = $sSubPath;
            }
        }
        if ($iDeep < 0) {
            throw new \Exception(sprintf('Path %s is outside root-folder.', $sInputPath));
        }
        $sInputPath = (DIRECTORY_SEPARATOR === '/' ? '/' : '') . implode(DIRECTORY_SEPARATOR, $aPath);
        return $sInputPath;
    }
    


    /**
     * checks if $sPath is writable
     * @param string $sPath
     * @return bool
     */
    public function isWritable($sPath) {
        $sPath = $this->getFullPath($sPath);
        if (!file_exists($sPath)) {
            return $this->isWritable(dirname($sPath));
        }
        return is_writable($sPath);
    }

    /**
     * writes $sContent to file in $sPath, if $sContent = null creates folder $sPath
     * @param string $sPath
     * @param string $sContent content to write
     * @param null $sContent file is folder
     * @param bool $blAppend only use, if $mContent ! null $sContent will appended, otherwise replaced
     * @return ML_Filesystem_Filesystem
     */
    public function write($sPath, $sContent = null, $blAppend = false) {
        $sPath = $this->getFullPath($sPath);
        // check permissions
        if (!$this->isWritable($sPath)) {
            if (is_dir($sPath)) {
                throw new \Exception(sprintf('Can\'t create folder `%s`.', $sPath),1407752557);
            } else {
                throw new \Exception(sprintf('File `%s` is not writable.', $sPath),1407752560);
            }
        }
        // create folder
        if (!file_exists(dirname($sPath))) {
            $this->write(dirname($sPath));
        }
        if ($sContent === null) {
            $oldumask = umask(0);
            if (!is_dir($sPath)) @mkdir($sPath, 0777);
            umask($oldumask);
        } else {
            @file_put_contents($sPath, $sContent, ($blAppend ? FILE_APPEND : 0));
        }
        if (!file_exists($sPath)) {
            if (is_dir($sPath)) {
                throw new \Exception(sprintf('Can\'t create folder `%s`.', $sPath),1407752558);
            } else {
                throw new \Exception(sprintf('File `%s` is not writable.', $sPath),1407752559);
            }
        } else {
            return $this;
        }
    }

    
}
