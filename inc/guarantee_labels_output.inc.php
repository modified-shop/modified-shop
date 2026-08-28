<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  /**
   * guarantee_labels_active()
   *
   * Whether the shop outputs EU labels for the current visitor. Guests count as B2C until
   * their customer group is listed as B2B.
   *
   * @return bool
   */
  function guarantee_labels_active() {
    if (!defined('MODULE_GUARANTEE_LABELS_STATUS') || MODULE_GUARANTEE_LABELS_STATUS != 'true') {
      return false;
    }

    if (!defined('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS') || trim(MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS) === '') {
      return true;
    }

    $status = isset($_SESSION['customers_status']['customers_status_id']) ? (int)$_SESSION['customers_status']['customers_status_id'] : 0;
    $b2b = array_map('intval', array_filter(array_map('trim', explode(',', MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS)), 'strlen'));

    return !in_array($status, $b2b, true);
  }

  /**
   * guarantee_labels_candidate()
   *
   * Whether a product row could carry a label at all. Checked before the manufacturer names
   * are loaded, so a page without qualifying articles causes no extra query.
   *
   * @param array $product one product row of a storefront query
   * @return bool
   */
  function guarantee_labels_candidate($product) {
    if (!isset($product['products_garan_duration']) || $product['products_garan_duration'] === null) {
      return false;
    }

    if (empty($product['manufacturers_id']) || !isset($product['products_manufacturers_model']) || trim((string)$product['products_manufacturers_model']) === '') {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    return $renderer->qualifies($product['products_garan_duration']);
  }

  /**
   * guarantee_labels_manufacturer_names()
   *
   * Loads the names of active manufacturers for a whole result block with one query instead of
   * one query per article. Names already delivered by another query are deliberately not used:
   * only this lookup guarantees that the manufacturer is active.
   *
   * @param array $manufacturers_ids
   * @return array manufacturer id to name, missing for an unknown or inactive manufacturer
   */
  function guarantee_labels_manufacturer_names($manufacturers_ids) {
    static $known = array();

    $names = array();
    $missing = array();

    foreach ((array)$manufacturers_ids as $manufacturers_id) {
      $manufacturers_id = (int)$manufacturers_id;

      if ($manufacturers_id < 1) {
        continue;
      }

      if (array_key_exists($manufacturers_id, $known)) {
        if ($known[$manufacturers_id] !== false) {
          $names[$manufacturers_id] = $known[$manufacturers_id];
        }
        continue;
      }

      $missing[$manufacturers_id] = $manufacturers_id;
    }

    if (count($missing) > 0) {
      $manufacturers_query = xtc_db_query("SELECT manufacturers_id,
                                                  manufacturers_name
                                             FROM ".TABLE_MANUFACTURERS."
                                            WHERE manufacturers_id IN (".implode(', ', $missing).")
                                              AND manufacturers_status = '1'");
      while ($manufacturers = xtc_db_fetch_array($manufacturers_query)) {
        $names[(int)$manufacturers['manufacturers_id']] = $manufacturers['manufacturers_name'];
        $known[(int)$manufacturers['manufacturers_id']] = $manufacturers['manufacturers_name'];
      }

      // remember the misses as well, so a block full of inactive manufacturers asks only once
      foreach ($missing as $manufacturers_id) {
        if (!array_key_exists($manufacturers_id, $known)) {
          $known[$manufacturers_id] = false;
        }
      }
    }

    return $names;
  }

  /**
   * guarantee_labels_collect_manufacturers()
   *
   * Gathers the manufacturers of every article of a result block that could carry a label.
   *
   * @param array $products rows of a storefront query
   * @return array manufacturer id to name
   */
  function guarantee_labels_collect_manufacturers($products) {
    $manufacturers_ids = array();

    foreach ((array)$products as $product) {
      if (guarantee_labels_candidate($product)) {
        $manufacturers_ids[(int)$product['manufacturers_id']] = (int)$product['manufacturers_id'];
      }
    }

    if (count($manufacturers_ids) < 1) {
      return array();
    }

    return guarantee_labels_manufacturer_names($manufacturers_ids);
  }

  /**
   * guarantee_labels_product_label()
   *
   * Builds both label variants for one article of a result block.
   *
   * @param array $product one product row
   * @param array $names the manufacturer names of the block
   * @return mixed array with hash, colour.svg and nested.svg, false when no label applies
   */
  function guarantee_labels_product_label($product, $names) {
    if (!guarantee_labels_candidate($product)) {
      return false;
    }

    $manufacturers_id = (int)$product['manufacturers_id'];

    if (!isset($names[$manufacturers_id])) {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    $label = $renderer->label($names[$manufacturers_id], $product['products_manufacturers_model'], $product['products_garan_duration']);

    if ($label === false) {
      return false;
    }

    // the text alternative of the graphic is built from the same values
    $label['manufacturer'] = $names[$manufacturers_id];
    $label['model'] = $product['products_manufacturers_model'];
    $label['duration'] = $renderer->duration_text($renderer->normalize_duration($product['products_garan_duration']));

    return $label;
  }

  /**
   * guarantee_labels_attribute()
   *
   * Prepares a language constant for an html attribute. The language packages write umlauts as
   * entities, so escaping them again would turn the ampersand into &amp; and the attribute would
   * show the entity itself. The text is decoded first and then escaped once.
   *
   * @param string $text
   * @return string
   */
  function guarantee_labels_attribute($text) {
    return htmlspecialchars(html_entity_decode($text, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
  }

  /**
   * guarantee_labels_markup()
   *
   * Wraps both label variants into the block a template places. The compact label opens the
   * full one, which is what the implementing regulation allows for the GARAN label. Both svg
   * are written inline, so they use the fonts the module loads once instead of carrying a copy.
   *
   * @param array $label the return value of guarantee_labels_product_label()
   * @return string ready markup, empty when there is no label
   */
  function guarantee_labels_markup($label) {
    if (!is_array($label) || !isset($label['colour.svg'], $label['nested.svg'], $label['hash'])) {
      return '';
    }

    $link = '';

    if (defined('TEXT_GUARANTEE_LABEL_URL') && trim(TEXT_GUARANTEE_LABEL_URL) !== '') {
      $link = '<a class="guarantee-label__link" href="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_URL).'" target="_blank" rel="noopener">'.TEXT_GUARANTEE_LABEL_LINK.'</a>';
    }

    // The full label carries the GARAN title in every EU language as outlined paths and weighs
    // around 290 kB. Only the compact one goes into the page; the full one is fetched from the
    // cache on the first open. It is inserted into the dom, so it uses the fonts loaded once.
    $source = guarantee_labels_cache_url($label['hash']);
    $full = ($source === '') ? guarantee_labels_inline_svg($label['colour.svg']) : '';

    // the content is opened in the lightbox of the template when there is one, so the label
    // behaves like every other overlay of the shop; the dialog is the fallback without it
    static $counter = 0;
    $id = 'guarantee-label-content-'.(++$counter);

    $duration = isset($label['duration']) ? $label['duration'] : '';
    $alt_compact = sprintf(TEXT_GUARANTEE_LABEL_ALT_COMPACT, $duration);
    $alt_full = sprintf(TEXT_GUARANTEE_LABEL_ALT, $duration, isset($label['manufacturer']) ? $label['manufacturer'] : '', isset($label['model']) ? $label['model'] : '');

    return '<div class="guarantee-label">'.
             '<button type="button" class="guarantee-label__compact" data-guarantee-label-content="'.$id.'" data-guarantee-label-title="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_TITLE).'" title="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_OPEN).'" aria-label="'.guarantee_labels_attribute($alt_compact.' '.TEXT_GUARANTEE_LABEL_OPEN).'">'.
               guarantee_labels_inline_svg($label['nested.svg']).
             '</button>'.
             '<dialog class="guarantee-label__dialog" aria-label="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_TITLE).'">'.
               '<div class="guarantee-label__content" id="'.$id.'">'.
                 '<div class="guarantee-label__full"'.($source !== '' ? ' data-guarantee-label-src="'.htmlspecialchars($source).'" data-guarantee-label-error="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_RELOAD).'"' : '').'>'.
                   '<div class="guarantee-label__graphic" role="img" aria-label="'.guarantee_labels_attribute($alt_full).'">'.$full.'</div>'.
                   $link.
                 '</div>'.
               '</div>'.
               '<button type="button" class="guarantee-label__close">'.TEXT_GUARANTEE_LABEL_CLOSE.'</button>'.
             '</dialog>'.
           '</div>';
  }

  /**
   * guarantee_labels_cache_url()
   *
   * The address the full label is fetched from. Empty when the cached file is missing, in which
   * case the caller writes the graphic into the page instead of pointing at a file that is not
   * there.
   *
   * @param string $hash
   * @return string
   */
  function guarantee_labels_cache_url($hash) {
    if (!is_file(DIR_FS_CATALOG.'cache/guarantee_labels/'.$hash.'/colour.svg')) {
      return '';
    }

    return (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '').'cache/guarantee_labels/'.$hash.'/colour.svg';
  }

  /**
   * guarantee_labels_inline_svg()
   *
   * Prepares one graphic for being written into an html document.
   *
   * The official templates carry a style block with generic class names and ids: both files use
   * cls-1 to cls-7 for entirely different things, and the full label clips through url(#clippath-6).
   * Inline they all land in one global namespace, so the last definition would win for every copy
   * on the page and a reference would resolve to the first element of that id in the document.
   * Class names and ids are therefore given a prefix that is unique per occurrence. Nothing else
   * is touched, the graphic renders exactly as the official file does.
   *
   * @param string $svg
   * @return string
   */
  function guarantee_labels_inline_svg($svg, $alt = '') {
    static $counter = 0;

    $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
    $svg = preg_replace('/<!DOCTYPE.*?>/s', '', $svg);
    $svg = preg_replace('/<!--.*?-->/s', '', $svg);

    $prefix = 'gl'.(++$counter).'-';

    // the ids the file defines itself, so a foreign reference is never rewritten
    preg_match_all('/\sid="([^"]+)"/', $svg, $matches);
    $ids = array_unique($matches[1]);

    foreach ($ids as $id) {
      $quoted = preg_quote($id, '/');
      $svg = preg_replace('/(\sid=")'.$quoted.'(")/', '$1'.$prefix.$id.'$2', $svg);
      $svg = preg_replace('/url\(#'.$quoted.'\)/', 'url(#'.$prefix.$id.')', $svg);
      $svg = preg_replace('/((?:xlink:)?href=")#'.$quoted.'(")/', '$1#'.$prefix.$id.'$2', $svg);
    }

    // class names inside the style block and on the elements
    $svg = preg_replace('/\.(cls-[0-9]+)/', '.'.$prefix.'$1', $svg);
    $svg = preg_replace_callback('/\sclass="([^"]+)"/', function ($match) use ($prefix) {
      $classes = array();

      foreach (preg_split('/\s+/', trim($match[1])) as $class) {
        $classes[] = (strpos($class, 'cls-') === 0) ? $prefix.$class : $class;
      }

      return ' class="'.implode(' ', $classes).'"';
    }, $svg);

    $svg = trim($svg);

    // a graphic carrying information needs a text alternative; without one it is decoration
    $role = ($alt === '') ? ' aria-hidden="true"' : ' role="img" aria-label="'.htmlspecialchars($alt).'"';

    return preg_replace('/<svg\b/', '<svg'.$role, $svg, 1);
  }
