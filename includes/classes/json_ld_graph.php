<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_CATALOG.'inc/html_encoding.php');

  /**
   * Collects the structured data of the current page and renders it as one JSON-LD graph.
   *
   * The nodes are kept in a graph instead of separate script blocks so they can reference each
   * other by @id: the offer names its seller, the website its publisher. Every builder returns
   * true only when it really added a node, so a caller can tell an empty page from a filled one.
   */
  class json_ld_graph {

    var $nodes;
    var $shop_url;
    var $has_organization;

    function __construct() {
      $this->nodes = array();
      $this->shop_url = self::link(FILENAME_DEFAULT);
      $this->has_organization = false;
    }

    /**
     * Adds a prepared node to the graph
     *
     * @param array $node
     * @return boolean
     */
    function add($node) {
      if (!is_array($node) || count($node) === 0) {
        return false;
      }
      $this->nodes[] = $node;
      return true;
    }

    /**
     * The shop itself, referenced as the seller of every offer
     *
     * @return boolean
     */
    function addOrganization() {
      $node = array(
        '@type' => 'Organization',
        '@id' => $this->shop_url.'#organization',
        'name' => self::text(STORE_NAME),
        'url' => $this->shop_url,
      );

      $logo = self::templateLogo();
      if ($logo != '') {
        $node['logo'] = $logo;
      }
      if (defined('STORE_OWNER_EMAIL_ADDRESS') && STORE_OWNER_EMAIL_ADDRESS != '') {
        $node['email'] = STORE_OWNER_EMAIL_ADDRESS;
      }
      if (defined('STORE_OWNER_VAT_ID') && STORE_OWNER_VAT_ID != '') {
        $node['vatID'] = self::text(STORE_OWNER_VAT_ID);
      }

      $this->has_organization = $this->add($node);

      return $this->has_organization;
    }

    /**
     * The reference other nodes use for the shop, empty while no organization node exists
     *
     * @return array
     */
    function organizationRef() {
      return ($this->has_organization) ? array('@id' => $this->shop_url.'#organization') : array();
    }

    /**
     * The start page, including the search entry point crawlers may offer as a sitelink
     *
     * @return boolean
     */
    function addWebSite() {
      $search_url = self::link(FILENAME_ADVANCED_SEARCH_RESULT, 'keywords=', false);

      $node = array(
        '@type' => 'WebSite',
        '@id' => $this->shop_url.'#website',
        'name' => self::text(STORE_NAME),
        'url' => $this->shop_url,
        'inLanguage' => (isset($_SESSION['language_code']) ? $_SESSION['language_code'] : ''),
        'potentialAction' => array(
          '@type' => 'SearchAction',
          'target' => array(
            '@type' => 'EntryPoint',
            'urlTemplate' => $search_url.'{search_term_string}',
          ),
          'query-input' => 'required name=search_term_string',
        ),
      );

      $publisher = $this->organizationRef();
      if (count($publisher) > 0) {
        $node['publisher'] = $publisher;
      }

      return $this->add($node);
    }

    /**
     * The trail of the current page
     *
     * @param object $breadcrumb
     * @return boolean
     */
    function addBreadcrumb($breadcrumb) {
      if (!is_object($breadcrumb) || !isset($breadcrumb->_trail) || !is_array($breadcrumb->_trail)) {
        return false;
      }

      $elements = array();
      $position = 0;
      foreach ($breadcrumb->_trail as $entry) {
        $title = self::text(isset($entry['title']) ? $entry['title'] : '');
        if ($title === '') {
          continue;
        }
        $position++;
        $element = array(
          '@type' => 'ListItem',
          'position' => $position,
          'name' => $title,
        );
        // the last entry is the current page and carries no link, schema.org allows that
        if (isset($entry['link']) && $entry['link'] != '') {
          $element['item'] = self::url($entry['link']);
        }
        $elements[] = $element;
      }

      if (count($elements) < 2) {
        return false;
      }

      return $this->add(array(
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
      ));
    }

    /**
     * The product of a product_info page, with its offer and rating
     *
     * @param object $product
     * @return boolean
     */
    function addProduct($product) {
      if (!is_object($product) || $product->isProduct() !== true) {
        return false;
      }

      $data = $product->data;
      $url = self::link(FILENAME_PRODUCT_INFO, xtc_product_link($data['products_id'], $data['products_name']));

      $node = array(
        '@type' => 'Product',
        '@id' => $url.'#product',
        'name' => self::text((!empty($data['products_heading_title'])) ? $data['products_heading_title'] : $data['products_name']),
        'url' => $url,
      );

      // the long text first: the ticket is about giving crawlers more than the meta description
      $description = self::text((!empty($data['products_description'])) ? $data['products_description'] : $data['products_short_description'], self::descriptionLength());
      if ($description !== '') {
        $node['description'] = $description;
      }

      $images = self::productImages($product);
      if (count($images) > 0) {
        $node['image'] = $images;
      }

      if (!empty($data['products_model'])) {
        $node['sku'] = self::text($data['products_model']);
      }
      if (!empty($data['products_manufacturers_model'])) {
        $node['mpn'] = self::text($data['products_manufacturers_model']);
      }
      if (!empty($data['products_ean'])) {
        $node = array_merge($node, self::gtin($data['products_ean']));
      }
      if (!empty($data['manufacturers_name'])) {
        $node['brand'] = array(
          '@type' => 'Brand',
          'name' => self::text($data['manufacturers_name']),
        );
      }

      $rating = self::aggregateRating($product);
      if (count($rating) > 0) {
        $node['aggregateRating'] = $rating;
      }

      $offer = $this->offer($product, $url);
      if (count($offer) > 0) {
        $node['offers'] = $offer;
      }

      return $this->add($node);
    }

    /**
     * The offer of a product, left out entirely when the customer group sees no prices
     *
     * @param object $product
     * @param string $url
     * @return array
     */
    function offer($product, $url) {
      $data = $product->buildDataArray($product->data, 'info');

      if (!isset($data['PRICE_ALLOWED']) || $data['PRICE_ALLOWED'] !== 'true') {
        return array();
      }
      if (!isset($data['PRODUCTS_PRICE_PLAIN'])) {
        return array();
      }

      $offer = array(
        '@type' => 'Offer',
        '@id' => $url.'#offer',
        'url' => $url,
        'price' => self::price($data['PRODUCTS_PRICE_PLAIN']),
        'priceCurrency' => (isset($_SESSION['currency']) ? $_SESSION['currency'] : ''),
        'availability' => self::availability($product->data),
        'itemCondition' => 'https://schema.org/NewCondition',
      );

      $seller = $this->organizationRef();
      if (count($seller) > 0) {
        $offer['seller'] = $seller;
      }

      if (!empty($product->data['products_date_available'])
          && strtotime($product->data['products_date_available']) > time()
          )
      {
        $offer['availabilityStarts'] = date('c', strtotime($product->data['products_date_available']));
      }

      return $offer;
    }

    /**
     * The products of a listing page, as the list a crawler can walk through
     *
     * The summary form carries the links alone and stays small even on a long page, the full
     * form repeats name, image and price so a crawler never has to open every article.
     *
     * @param array $module_content
     * @param object $listing_split
     * @return boolean
     */
    function addItemList($module_content, $listing_split) {
      $mode = (defined('MODULE_JSON_LD_LISTING')) ? MODULE_JSON_LD_LISTING : 'false';
      if (!is_array($module_content) || count($module_content) === 0
          || !in_array($mode, array('summary', 'full'))
          )
      {
        return false;
      }

      $page = self::listingPage();

      // the positions run through the whole listing, so page two starts where page one ended
      $offset = 0;
      $total = count($module_content);
      if (is_object($listing_split)) {
        $offset = ((int)$listing_split->current_page_number - 1) * (int)$listing_split->number_of_rows_per_page;
        $total = (int)$listing_split->number_of_rows;
      }

      $elements = array();
      $position = $offset;
      foreach ($module_content as $entry) {
        if (empty($entry['PRODUCTS_LINK'])) {
          continue;
        }
        $position++;
        $url = self::url($entry['PRODUCTS_LINK']);

        $element = array(
          '@type' => 'ListItem',
          'position' => $position,
          'url' => $url,
        );

        if ($mode === 'full') {
          $element['item'] = $this->listingProduct($entry, $url);
        } elseif (!empty($entry['PRODUCTS_NAME'])) {
          $element['name'] = self::text($entry['PRODUCTS_NAME']);
        }

        $elements[] = $element;
      }

      if (count($elements) === 0) {
        return false;
      }

      $list = array('@type' => 'ItemList');
      if (count($page) > 0) {
        $list['@id'] = $page['url'].'#itemlist';
      }
      $name = self::listingName();
      if ($name !== '') {
        $list['name'] = $name;
      }
      $list['numberOfItems'] = $total;
      $list['itemListOrder'] = 'https://schema.org/ItemListOrderAscending';
      $list['itemListElement'] = $elements;

      if (count($page) > 0) {
        $this->add($page['node']);
      }

      return $this->add($list);
    }

    /**
     * The page a listing belongs to, empty where this view is not the canonical one
     *
     * A manufacturer chosen inside a category, a filtered view and every noindex page point
     * their canonical somewhere else. A page node there would claim a url that already
     * belongs to the unfiltered listing, so those views keep the bare list.
     *
     * @return array
     */
    static function listingPage() {
      global $metadata_array, $meta_robots, $current_category_id, $manufacturer, $category;

      if (empty($metadata_array['link'])
          || (isset($meta_robots) && strpos($meta_robots, 'noindex') !== false)
          || (isset($current_category_id) && (int)$current_category_id > 0 && isset($manufacturer))
          )
      {
        return array();
      }

      $url = self::url($metadata_array['link']);
      $node = array(
        '@type' => 'CollectionPage',
        '@id' => $url.'#collection',
        'url' => $url,
      );

      $name = self::listingName();
      if ($name !== '') {
        $node['name'] = $name;
      }

      $description = '';
      if (!empty($category['categories_description'])) {
        $description = self::text($category['categories_description'], self::descriptionLength());
      } elseif (!empty($metadata_array['description'])) {
        $description = self::text($metadata_array['description'], self::descriptionLength());
      }
      if ($description !== '') {
        $node['description'] = $description;
      }

      // only a listing that is nothing but one manufacturer really is about that brand
      if (isset($manufacturer['manufacturers_name']) && (!isset($current_category_id) || (int)$current_category_id === 0)) {
        $node['about'] = array(
          '@type' => 'Brand',
          'name' => self::text($manufacturer['manufacturers_name']),
        );
      }

      $node['mainEntity'] = array('@id' => $url.'#itemlist');

      return array('url' => $url, 'node' => $node);
    }

    /**
     * The heading the listing shows, which is the category, manufacturer or list name
     *
     * @return string
     */
    static function listingName() {
      global $list_title;

      return (isset($list_title)) ? self::text($list_title) : '';
    }

    /**
     * One product of a listing, built from the data the listing already read
     *
     * @param array $entry
     * @param string $url
     * @return array
     */
    function listingProduct($entry, $url) {
      $node = array(
        '@type' => 'Product',
        '@id' => $url.'#product',
        'name' => self::text((!empty($entry['PRODUCTS_HEADING_TITLE'])) ? $entry['PRODUCTS_HEADING_TITLE'] : $entry['PRODUCTS_NAME']),
        'url' => $url,
      );

      if (!empty($entry['PRODUCTS_IMAGE'])) {
        $node['image'] = self::url($entry['PRODUCTS_IMAGE']);
      }
      if (!empty($entry['PRODUCTS_MODEL'])) {
        $node['sku'] = self::text($entry['PRODUCTS_MODEL']);
      }
      if (!empty($entry['PRODUCTS_MANUFACTURERS_MODEL'])) {
        $node['mpn'] = self::text($entry['PRODUCTS_MANUFACTURERS_MODEL']);
      }
      if (!empty($entry['PRODUCTS_EAN'])) {
        $node = array_merge($node, self::gtin($entry['PRODUCTS_EAN']));
      }

      if (isset($entry['PRICE_ALLOWED']) && $entry['PRICE_ALLOWED'] === 'true'
          && isset($entry['PRODUCTS_PRICE_PLAIN'])
          )
      {
        $node['offers'] = array(
          '@type' => 'Offer',
          'url' => $url,
          'price' => self::price($entry['PRODUCTS_PRICE_PLAIN']),
          'priceCurrency' => (isset($_SESSION['currency']) ? $_SESSION['currency'] : ''),
          'availability' => self::availability(array(
            'products_quantity' => (isset($entry['PRODUCTS_QUANTITY']) ? $entry['PRODUCTS_QUANTITY'] : 1),
            'products_date_available' => (isset($entry['PRODUCTS_DATE_AVAILABLE']) ? $entry['PRODUCTS_DATE_AVAILABLE'] : ''),
          )),
          'itemCondition' => 'https://schema.org/NewCondition',
        );

        $seller = $this->organizationRef();
        if (count($seller) > 0) {
          $node['offers']['seller'] = $seller;
        }
      }

      return $node;
    }

    /**
     * Renders the collected nodes as one script block
     *
     * @return string
     */
    function render() {
      if (count($this->nodes) === 0) {
        return '';
      }

      $graph = array(
        '@context' => 'https://schema.org',
        '@graph' => $this->nodes,
      );

      // JSON_HEX_TAG keeps a description from closing the script block, and leaving unicode
      // escaped keeps the output ascii, which a shop running ISO-8859-15 needs
      $flags = JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
      if (defined('MODULE_JSON_LD_PRETTY') && MODULE_JSON_LD_PRETTY == 'true') {
        $flags |= JSON_PRETTY_PRINT;
      }

      $json = json_encode($graph, $flags);
      if ($json === false) {
        return '';
      }

      return '<script type="application/ld+json">'.$json.'</script>'.PHP_EOL;
    }

    /**
     * A price in the notation schema.org expects, with the decimals of the active currency
     *
     * @param float $price
     * @return string
     */
    static function price($price) {
      global $xtPrice;

      $decimals = 2;
      if (isset($xtPrice->currencies[$xtPrice->actualCurr]['decimal_places'])) {
        $decimals = (int)$xtPrice->currencies[$xtPrice->actualCurr]['decimal_places'];
      }

      return number_format((float)$price, $decimals, '.', '');
    }

    /**
     * The configured cut off for a product description
     *
     * @return integer
     */
    static function descriptionLength() {
      return (defined('MODULE_JSON_LD_DESCRIPTION_LENGTH')) ? (int)MODULE_JSON_LD_DESCRIPTION_LENGTH : 0;
    }

    /**
     * The stock state of a product, following the same rules the shop applies in the cart
     *
     * @param array $data
     * @return string
     */
    static function availability($data) {
      if (!empty($data['products_date_available'])
          && strtotime($data['products_date_available']) > time()
          )
      {
        return 'https://schema.org/PreOrder';
      }

      if (defined('STOCK_CHECK') && STOCK_CHECK == 'true'
          && isset($data['products_quantity']) && $data['products_quantity'] <= 0
          )
      {
        if (defined('STOCK_ALLOW_CHECKOUT') && STOCK_ALLOW_CHECKOUT == 'true') {
          return 'https://schema.org/BackOrder';
        }
        return 'https://schema.org/OutOfStock';
      }

      return 'https://schema.org/InStock';
    }

    /**
     * The rating of a product, empty when the group may not read reviews or none exist
     *
     * @param object $product
     * @return array
     */
    static function aggregateRating($product) {
      if ($_SESSION['customers_status']['customers_status_read_reviews'] != '1') {
        return array();
      }

      $count = (int)$product->getReviewsCount();
      if ($count < 1) {
        return array();
      }

      return array(
        '@type' => 'AggregateRating',
        'ratingValue' => (string)$product->getReviewsAverage('', 1),
        'reviewCount' => $count,
        'bestRating' => '5',
        'worstRating' => '1',
      );
    }

    /**
     * The gtin properties of an ean
     *
     * schema.org accepts the plain gtin for every length, the numbered ones only for the four
     * lengths that really exist, so an ean of any other length gets the plain property alone.
     *
     * @param string $ean
     * @return array
     */
    static function gtin($ean) {
      $ean = preg_replace('/[^0-9]/', '', (string)$ean);
      if ($ean === '') {
        return array();
      }

      $properties = array('gtin' => $ean);
      if (in_array(strlen($ean), array(8, 12, 13, 14))) {
        $properties['gtin'.strlen($ean)] = $ean;
      }

      return $properties;
    }

    /**
     * All images of a product in the largest size the shop stores
     *
     * @param object $product
     * @return array
     */
    static function productImages($product) {
      require_once(DIR_FS_INC.'xtc_get_products_mo_images.inc.php');

      $images = array();

      if (!empty($product->data['products_image'])) {
        $image = $product->productImage($product->data['products_image'], 'popup');
        if ($image != '') {
          $images[] = self::url($image);
        }
      }

      if (defined('MO_PICS') && MO_PICS != '0') {
        $mo_images = xtc_get_products_mo_images($product->data['products_id']);
        if (is_array($mo_images)) {
          foreach ($mo_images as $mo_image) {
            $image = $product->productImage($mo_image['image_name'], 'popup');
            if ($image != '') {
              $images[] = self::url($image);
            }
          }
        }
      }

      return array_values(array_unique($images));
    }

    /**
     * The shop logo of the current template, empty when the template ships none of the known names
     *
     * @return string
     */
    static function templateLogo() {
      $path = 'templates/'.CURRENT_TEMPLATE.'/img/';
      foreach (array('logo_head.png', 'logo.gif', 'top_logo.jpg') as $name) {
        if (is_file(DIR_FS_CATALOG.$path.$name)) {
          return self::url(DIR_WS_BASE.$path.$name);
        }
      }

      return '';
    }

    /**
     * A shop link, ready for JSON
     *
     * @param string $page
     * @param string $parameters
     * @param boolean $search_engine_safe
     * @return string
     */
    static function link($page, $parameters = '', $search_engine_safe = true) {
      return self::url(xtc_href_link($page, $parameters, 'NONSSL', false, $search_engine_safe));
    }

    /**
     * Turns a shop path into an absolute url a crawler can follow
     *
     * xtc_href_link() returns links for html attributes, so the entities have to go before the
     * value can become part of a JSON string.
     *
     * @param string $path
     * @return string
     */
    static function url($path) {
      global $request_type;

      $path = trim(str_replace('&amp;', '&', (string)$path));
      if ($path === '') {
        return '';
      }

      if (!preg_match('#^https?://#i', $path)) {
        $base = (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER).DIR_WS_CATALOG;
        $path = rtrim($base, '/').'/'.ltrim($path, '/');
      }

      return self::utf8($path);
    }

    /**
     * Turns shop content into plain text a JSON string may carry
     *
     * @param string $text
     * @param integer $length
     * @return string
     */
    static function text($text, $length = 0) {
      $text = (string)$text;
      if ($text === '') {
        return '';
      }

      $text = preg_replace('/<[^>]*>/', ' ', $text);
      $text = decode_htmlentities($text);
      $text = str_replace(array("\xc2\xa0", "\r", "\n", "\t"), ' ', $text);
      $text = trim(preg_replace('/\s\s+/', ' ', $text));

      $text = self::utf8($text);

      if ($length > 0 && mb_strlen($text, 'UTF-8') > $length) {
        $text = trim(mb_substr($text, 0, $length, 'UTF-8'));
      }

      return $text;
    }

    /**
     * json_encode() rejects anything that is not utf-8, and a shop may run ISO-8859-15
     *
     * @param string $text
     * @return string
     */
    static function utf8($text) {
      return encode_utf8($text, get_default_charset(), true);
    }
  }
