<?php
/*
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
 * (c) 2010 - 2024 RedGecko GmbH -- http://www.redgecko.de
 *     Released under the MIT License (Expat)
 * -----------------------------------------------------------------------------
 */

/**
 * Data and logic for cross border configurations.
 *
 * When a new instance is created, it loads all needed configuration variables for metro tabs from the database.
 */
class MetroCrossBordersConfiguration {
    /**
     * The key is the marketplace id.
     *
     * @var array<int,array{
     *      mpID:int,
     *      "metro.clientkey":string,
     *      "metro.maxquantity":int,
     *      "metro.quantity.type":string,
     *      "metro.quantity.value":int,
     *      "metro.shippingorigin":string,
     *      "metro.stocksync.tomarketplace":string
     * }>
     */
    private $config_data = array();

    /**
     * A static instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Loads the configuration.
     */
    public function __construct() {
        $this->load();
    }

    /**
     * Return the number of marketplaces in the configuration.
     *
     * @return int
     */
    public function countMarketplaces() {
        return count($this->config_data);
    }

    /**
     * Return the value for the key to the marketplace.
     *
     * @param int $marketplace_id
     * @param string $key
     * @return int|string
     */
    public function get($marketplace_id, $key) {
        if (!array_key_exists($marketplace_id, $this->config_data) ||
            !array_key_exists($key, $this->config_data[$marketplace_id])
        ) {
            return null;
        }

        return $this->config_data[$marketplace_id][$key];
    }

    /**
     * Returns the first marketplace id which has the same client key and shipping origin as the provided marketplace id
     *  and has stock synchronization enabled.
     *
     * @param int $marketplace_id The marketplace id to check the client key with.
     * @return int|null
     */
    public function getCrossBordersStockOptionsMarketplaceId($marketplace_id) {
        $marketplace_cnt = $this->countMarketplaces();

        if ($marketplace_cnt) {
            $current_client_key = $this->get($marketplace_id, 'metro.clientkey');
            $current_shipping_origin = $this->get($marketplace_id, 'metro.shippingorigin');
            foreach ($this->iterateMarketplaces() as $marketplace) {
                if ('auto' == $marketplace['metro.stocksync.tomarketplace']
                    && $current_client_key == $marketplace['metro.clientkey']
                    && $current_shipping_origin == $marketplace['metro.shippingorigin']
                ) {
                    return $marketplace['mpID'];
                }
            }
        }

        return null;
    }

    /**
     * Get all config data for a marketplace id.
     *
     * @param int $marketplace_id
     * @return array{
     *      mpID:int,
     *      "metro.clientkey":string,
     *      "metro.maxquantity":int,
     *      "metro.quantity.type":string,
     *      "metro.quantity.value":int,
     *      "metro.shippingorigin":string,
     *      "metro.stocksync.tomarketplace":string
     *  }|null
     */
    public function getMarketplace($marketplace_id) {
        if (!array_key_exists($marketplace_id, $this->config_data)) {
            return null;
        }

        return $this->config_data[$marketplace_id];
    }

    /**
     * Get all metro marketplace ids from the global configuration.
     *
     * @return int[]
     */
    public function getMetroMarketplaceIds() {
        global $magnaConfig;

        if (empty($magnaConfig['maranon']['Marketplaces'])) {
            return array();
        }

        $metro_ids = array();
        foreach ($magnaConfig['maranon']['Marketplaces'] as $id => $marketplace) {
            if ('metro' == $marketplace) {
                $metro_ids[] = (int)$id;
            }
        }

        return $metro_ids;
    }

    /**
     * Return a static instance.
     *
     * @return self
     */
    public static function gi() {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Iterates through the marketplaces.
     *
     * @return array
     */
    public function iterateMarketplaces() {
        $aConfig = array();
        foreach ($this->config_data as $entry) {
            $aConfig[] = $entry;
        }

        return $aConfig;
    }

    /**
     * Iterates through all marketplaces which have the same metro account and the same origin and doesn't have stock
     * synchronization enabled.
     *
     * @param int $marketplace_id
     * @return array
     */
    public function iterateSameCrossBorderMarketplaces($marketplace_id) {
        $cross_border_settings = $this->getMarketplace($marketplace_id);
        $aMarketplaces = array();
        foreach ($this->iterateMarketplaces() as $marketplace) {
            if ($cross_border_settings['mpID'] != $marketplace['mpID'] &&
                $cross_border_settings['metro.clientkey'] == $marketplace['metro.clientkey'] &&
                $cross_border_settings['metro.shippingorigin'] == $marketplace['metro.shippingorigin'] &&
                'no' == $marketplace['metro.stocksync.tomarketplace']
            ) {
                $aMarketplaces[] = $marketplace;
            }
        }
        return $aMarketplaces;
    }

    /**
     * Loads all needed configuration variables for all metro marketplaces from the database.
     *
     * @return void
     */
    public function load() {
        $metro_ids = $this->getMetroMarketplaceIds();
        if (empty($metro_ids)) {
            return;
        }

        $db_data = MagnaDB::gi()->fetchArray(sprintf("SELECT *
                FROM magnalister_config
                WHERE mpID IN (%s) AND mkey IN ('metro.clientkey', 'metro.maxquantity', 'metro.quantity.type',
                    'metro.quantity.value', 'metro.shippingorigin', 'metro.stocksync.tomarketplace')",
            implode(', ', $metro_ids)));

        foreach ($db_data as $entry) {
            $this->set($entry['mpID'], $entry['mkey'], $entry['value']);
        }
    }

    /**
     * Set a value for a key to a marketplace id.
     *
     * @param int $marketplace_id
     * @param string $key
     * @param string $value
     * @return self
     */
    public function set($marketplace_id, $key, $value) {
        if (!array_key_exists($marketplace_id, $this->config_data)) {
            $this->config_data[$marketplace_id] = array(
                'mpID' => (int)$marketplace_id
            );
        }

        $this->config_data[$marketplace_id][$key] = $value;

        return $this;
    }
}
