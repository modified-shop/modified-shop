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
 * (c) 2010 - 2019 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/**
 * Class GoogleshoppingSizeValue
 *
 * Encapsulates size system that Google understands
 *
 * @see https://support.google.com/merchants/answer/6324502?hl=en
 */
class GoogleshoppingSizeValue {
    const TYPE_REGULAR = 'regular';

    const TYPE_PETITE = 'petite';

    const TYPE_SMALL = 'small';

    const TYPE_BIG_AND_TALL = 'big and tall';

    const TYPE_MATERNITY = 'maternity';

    const SYSTEM_AU = 'AU';

    const SYSTEM_BR = 'BR';

    const SYSTEM_CN = 'CN';

    const SYSTEM_DE = 'DE';

    const SYSTEM_EU = 'EU';

    const SYSTEM_FR = 'FR';

    const SYSTEM_IT = 'IT';

    const SYSTEM_JP = 'JP';

    const SYSTEM_MEX = 'MEX';

    const SYSTEM_UK = 'UK';

    const SYSTEM_US = 'US';

    private static $allowedTypes = array(
        self::TYPE_REGULAR,
        self::TYPE_PETITE,
        self::TYPE_SMALL,
        self::TYPE_BIG_AND_TALL,
        self::TYPE_MATERNITY,
    );

    private static $allowedSystems = array(
        self::SYSTEM_AU,
        self::SYSTEM_BR,
        self::SYSTEM_CN,
        self::SYSTEM_DE,
        self::SYSTEM_EU,
        self::SYSTEM_FR,
        self::SYSTEM_IT,
        self::SYSTEM_JP,
        self::SYSTEM_MEX,
        self::SYSTEM_UK,
        self::SYSTEM_US,
    );

    /** @internal  array */
    private static $registry = array();

    /** @var string */
    private $system;

    /** @var string */
    private $type;

    /** @var mixed */
    private $size;

    public function __construct($size, $system = self::SYSTEM_DE, $type = self::TYPE_REGULAR) {
        $this->validateSystem($system);
        $this->validateType($type);

        $this->system = $system;
        $this->type = $type;

        $this->validateSize($size);

        $this->size = $size;
    }

    /**
     * @param $system
     *
     * @return void
     *
     * @throws DomainException
     */
    private function validateSystem($system) {
        if (!in_array($system, self::$allowedSystems)) {
            throw new DomainException(sprintf('Size system "%s" is not supported by GoogleShopping'));
        }
    }

    /**
     * @param $type
     */
    private function validateType($type) {
        if (!in_array($type, self::$allowedTypes)) {
            throw new DomainException(sprintf('Size type "%s" is not supported by GoogleShopping'));
        }
    }

    /**
     * @param $size
     *
     * @return void
     *
     * @throws DomainException
     */
    private function validateSize($size) {
        if (!in_array($size, self::$registry[$this->system][$this->type])) {
            throw new DomainException(sprintf('Size "%s" is invalid for type "%s" and/or system "%s"'));
        }

        $this->size = $size;
    }

    /**
     * @param $size
     * @param $system
     * @param $type
     *
     * @throws InvalidArgumentException
     * @internal used for populating registry map with proper size_system and size_types
     *
     */
    public static function addSize($size, $system, $type) {
        if (!in_array($system, self::$allowedSystems) || !in_array($type, self::$allowedTypes)) {
            throw new InvalidArgumentException(sprintf('Invalid system or type'));
        }

        self::$registry[$system][$type][] = $size;
    }
}
