<?php
/**
 * Collection of pclasses
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
 * KiTT_PClassCollection
 *
 * Holds a collection of pclasses that can queried for default
 * and minimum monthly payment etc
 *
 * @category  Payment
 * @package   KiTT
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KiTT_PClassCollection
{
    /**
     * @var Klarna
     */
    private $_kapi;

    /**
     * @var KiTT_Formatter
     */
    private $_formatter;

    /**
     * The selected pclass-id
     *
     * @var int
     */
    private $_default;

    /**
     * The pclass-id with minimum monthly cost
     *
     * @var int
     */
    private $_minimum;

    /**
     * Filtered array of pclasses
     *
     * @var array
     */
    public $pclasses;

    /**
     * Map of payment codes to their associated pclass types
     *
     * @var array
     */
    private static $_types = array(
        KiTT::PART => array(
            KlarnaPClass::ACCOUNT,
            KlarnaPClass::CAMPAIGN,
            KlarnaPClass::FIXED
        ),
        KiTT::SPEC => array(
            KlarnaPClass::SPECIAL
        )
    );

    /**
     * Construct PClass Collection
     *
     * @param Klarna         $kapi      api instance used to get pclasses from DB
     * @param KiTT_Formatter $formatter formatter used to format prices
     * @param int            $sum       order total sum for filtering pclasses
     * @param int            $page      PRODUCT_PAGE or CHECKOUT_PAGE
     * @param string         $code      payment code
     */
    public function __construct(
        Klarna $kapi, KiTT_Formatter $formatter, $sum, $page, $code
    ) {
        $this->_kapi = $kapi;
        $this->_formatter = $formatter;

        if (array_key_exists($code, self::$_types)) {
            $this->update($sum, $page, self::$_types[$code]);
        }
    }

    /**
     * Update list of pclasses
     *
     * @param int   $sum   order total for filtering
     * @param int   $page  PRODUCT_PAGE or CHECKOUT_PAGE
     * @param array $types PClass types to include
     *
     * @return void
     */
    public function update($sum, $page, $types)
    {
        $pclasses = array();
        $default = null;
        $minimum = null;
        $minval = null;

        foreach ($this->_kapi->getPClasses() as $pclass) {
            $type = $pclass->getType();
            if (!in_array($type, $types) || $sum < $pclass->getMinAmount()) {
                continue;
            }

            // Get monthly cost
            if (in_array($type, array(KlarnaPClass::FIXED, KlarnaPClass::SPECIAL))) {
                if ($page == KlarnaFlags::PRODUCT_PAGE) {
                    continue;
                }
                $monthlyCost = -1;

            } else {
                $lowestPayment = KlarnaCalc::get_lowest_payment_for_account(
                    $pclass->getCountry()
                );
                $monthlyCost = KlarnaCalc::calc_monthly_cost(
                    $sum, $pclass, $page
                );
                if ($monthlyCost < 0.01) {
                    continue;
                }

                if ($monthlyCost < $lowestPayment) {
                    if ($pclass->getType() == KlarnaPClass::CAMPAIGN) {
                        continue;
                    }
                    if ($page == KlarnaFlags::CHECKOUT_PAGE
                        && $pclass->getType() == KlarnaPClass::ACCOUNT
                    ) {
                        $monthlyCost = $lowestPayment;
                    }
                }
            }

            // Select the minimum
            if ($minimum === null || $minval > $monthlyCost) {
                $minimum = $pclass;
                $minval = $monthlyCost;
            }

            // Select the default
            if ($type == KlarnaPClass::ACCOUNT) {
                $default = $pclass;
            } else if ($type == KlarnaPClass::CAMPAIGN) {
                if ($default === null
                    || $default->getType() != KlarnaPClass::ACCOUNT
                ) {
                    $default = $pclass;
                }
            } else {
                if ($default === null) {
                    $default = $pclass;
                }
            }

            $pclasses[$pclass->getId()] = array(
                'pclass' => $pclass,
                'locale' => new KiTT_Locale($pclass->getCountry()),
                'monthlyCost' => $monthlyCost
            );
        }

        // Save result
        $this->pclasses = $pclasses;
        if ($default != null) {
            $this->_default = $default;
        }
        if ($minimum != null) {
            $this->_minimum = $minimum;
        }
    }

    /**
     * Get the PClass that should be pre-selected
     *
     * @return KlarnaPClass
     */
    public function getDefault ()
    {
        return $this->_default;
    }

    /**
     * Set the PClass to pre-select
     *
     * Note: Use this when returning to the checkout from a error
     *
     * @param int $id KlarnaPClass id
     *
     * @return void
     */
    public function setDefault ($id)
    {
        $pclass = $this->getPClass($id);
        if ($pclass !== null) {
            $this->_default = $pclass;
        }
    }

    /**
     * Get a pclass from the pclass collection
     *
     * @param int $id KlarnaPClass id
     *
     * @return null|KlarnaPClass
     */
    public function getPClass($id)
    {
        if (array_key_exists($id, $this->pclasses)) {
            return $this->pclasses[$id]['pclass'];
        }
        return null;
    }

    /**
     * Get formatted price for PClass with minimum monthly cost
     *
     * @return string formatted price
     */
    public function minimumPClass ()
    {
        if ($this->_minimum instanceof KlarnaPClass) {
            $info = $this->pclasses[$this->_minimum->getId()];
            return $this->_formatter->formatPrice(
                $info['monthlyCost'], $info['locale']
            );
        }
        return null;
    }

    /**
     * Get view data for selected PClass
     *
     * @return array
     */
    public function defaultPClass ()
    {
        if ($this->_default instanceof KlarnaPClass) {
            $id = $this->_default->getId();
            $info = $this->pclasses[$id];
            $pclass = array(
                'id' => $id,
                'price' => $this->_formatter->formatPrice(
                    $info['monthlyCost'], $info['locale']
                ),
                'description' => $info['pclass']->getDescription()
            );
            return $pclass;
        }
        return null;
    }

    /**
     * Get view data for table of pclasses
     *
     * @return array
     */
    public function table ()
    {
        $defaultId = 0;
        if ($this->_default instanceof KlarnaPClass) {
            $defaultId = $this->_default->getId();
        }

        $out = array();
        if (is_array($this->pclasses)) {
            foreach ($this->pclasses as $pid => $info) {
                $value = $this->_formatter->formatPrice(
                    $info['monthlyCost'], $info["locale"]
                );
                $default = ($defaultId == $pid);

                $out[] = array(
                    'id' => $pid,
                    'description' => $info['pclass']->getDescription(),
                    'cost' => ($info['monthlyCost'] > 0 ? $value : ''),
                    'default' => $default
                );
            }
        }
        return $out;
    }
}
