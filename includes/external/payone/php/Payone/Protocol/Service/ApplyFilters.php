<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License (GPL 3)
 * that is bundled with this package in the file LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Payone to newer
 * versions in the future. If you wish to customize Payone for your
 * needs please refer to http://www.payone.de for more information.
 *
 * @category        Payone
 * @package         Payone_Protocol
 * @subpackage      Service
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @author          Matthias Walter <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Protocol
 * @subpackage      Service
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */
class Payone_Protocol_Service_ApplyFilters
{
    const CONFIG_FILTER_KEY = 'filter_key';
    const CONFIG_FILTER_CONFIG = 'config';

    /**
     * @var Payone_Protocol_Config_Filter
     */
    protected $config = null;

    /**
     * @var Payone_Protocol_Filter_Interface[]
     */
    protected $filters = array();

    /**
     * @param null|Payone_Protocol_Config_Filter $config
     */
    public function __construct(Payone_Protocol_Config_Filter $config = null)
    {
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    /**
     * @param Payone_Protocol_Filter_Filterable $object
     * @return bool
     * @throws Payone_Protocol_Exception_FilterNotFound|Payone_Protocol_Exception_InvalidConfig
     */
    public function apply(Payone_Protocol_Filter_Filterable $object)
    {
        $class = get_class($object);

        if ($this->getConfig() === null) {
            throw new Payone_Protocol_Exception_InvalidConfig();
        }

        $filtersForClass = $this->getConfig()->getFiltersByClass($class);

        // No Filters found for this Class
        if (!is_array($filtersForClass)) {
            return false;
        }

        foreach ($filtersForClass as $key => $filterConfig)
        {
            $config = null;
            $filterKey = '';

            // Init
            if (is_array($filterConfig) and array_key_exists(self::CONFIG_FILTER_KEY, $filterConfig)) {
                $filterKey = $filterConfig[self::CONFIG_FILTER_KEY];
                if (array_key_exists(self::CONFIG_FILTER_CONFIG, $filterConfig)) {
                    $config = $filterConfig[self::CONFIG_FILTER_CONFIG];
                }
            }
            else {
                $filterKey = $filterConfig;
            }

            // Fetch Filter
            $filter = $this->getFilter($filterKey);
            /**
             * @var Payone_Protocol_Filter_Interface|null $filter
             */
            if (!($filter instanceof Payone_Protocol_Filter_Interface)) {
                throw new Payone_Protocol_Exception_FilterNotFound($filterKey);
            }

            if (is_array($config)) {
                $filter->initConfig($config);
            }

            // Filter Value
            $objectValue = $object->getValue($key);
            if ($objectValue !== null) {
                $filteredValue = $filter->filterValue($objectValue);
                $object->setValue($key, $filteredValue);
            }
        }

        return true;
    }

    /**
     * @param Payone_Protocol_Filter_Interface $filter
     */
    public function addFilter(Payone_Protocol_Filter_Interface $filter)
    {
        $this->filters[$filter->getKey()] = $filter;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function removeFilter($key)
    {
        if (array_key_exists($key, $this->filters)) {
            unset($this->filters[$key]);
            return true;
        }
        return false;
    }

    /**
     * @param \Payone_Protocol_Config_Filter $config
     */
    public function setConfig(Payone_Protocol_Config_Filter $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Payone_Protocol_Config_Filter
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Payone_Protocol_Filter_Interface[] $filters
     */
    public function setFilters(array $filters)
    {
        $this->filters = array();
        foreach($filters as $key => /** @var $value Payone_Protocol_Filter_Interface */ $value)
        {
            $this->addFilter($value);
        }
    }

    /**
     * @return Payone_Protocol_Filter_Interface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param $key
     * @return null|Payone_Protocol_Filter_Interface
     */
    public function getFilter($key)
    {
        if (array_key_exists($key, $this->filters)) {
            return $this->filters[$key];
        }
        return null;
    }

}
