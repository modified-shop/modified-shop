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

  // Above this many active manufacturers the buffer keeps asking one id at a time, below it
  // one query answers the whole request. A shop past this number is a marketplace, and there
  // a listing page still shows only a handful of them.
  defined('GUARANTEE_LABELS_MANUFACTURER_LIMIT') or define('GUARANTEE_LABELS_MANUFACTURER_LIMIT', 500);

  /**
   * guarantee_labels_active()
   *
   * Whether the shop outputs EU labels for the current visitor. Guests count as B2C until
   * their customer group is listed as B2B.
   *
   * @return bool
   */
  /**
   * Whether a stored snapshot may be shown, no matter who is looking.
   *
   * A snapshot exists because the customer was not in a B2B group when the order was placed.
   * That question is therefore already answered and must not be asked again: moving a customer
   * into a B2B group afterwards would otherwise take the guarantee out of their own order view
   * while the administration, running with group 0, still sends it.
   *
   * @return bool
   */
  function guarantee_labels_order_active() {
    return (defined('MODULE_GUARANTEE_LABELS_STATUS') && MODULE_GUARANTEE_LABELS_STATUS == 'true');
  }

  function guarantee_labels_active($customers_status = null) {
    if (!guarantee_labels_order_active()) {
      return false;
    }

    if (!defined('MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS') || trim(MODULE_GUARANTEE_LABELS_B2B_CUSTOMERS_STATUS) === '') {
      return true;
    }

    // the administration runs in the session of the admin, an order belongs to its customer
    if ($customers_status !== null) {
      $status = (int)$customers_status;
    } else {
      $status = isset($_SESSION['customers_status']['customers_status_id']) ? (int)$_SESSION['customers_status']['customers_status_id'] : 0;
    }
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
  function guarantee_labels_candidate($product, $uprid = '') {
    if (!isset($product['products_garan_duration']) || $product['products_garan_duration'] === null) {
      return false;
    }

    if (empty($product['manufacturers_id']) || !isset($product['products_manufacturers_model']) || trim((string)$product['products_manufacturers_model']) === '') {
      return false;
    }

    // Where a selection exists it decides, otherwise the article does. A listing shows the
    // article before anything is chosen, a cart position shows exactly one combination.
    $physical = ($uprid === '')
              ? guarantee_labels_product_physical(isset($product['products_id']) ? $product['products_id'] : 0)
              : guarantee_labels_position_physical($uprid);

    if (!$physical) {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    return $renderer->qualifies($product['products_garan_duration']);
  }

  /**
   * guarantee_labels_position_physical()
   *
   * Whether one chosen combination is physical goods. An article with a download variant and a
   * physical one counts as mixed as a whole, but a customer who picks only the download bought
   * digital content and gets neither label nor snapshot.
   *
   * The cart identifies a position as "<products_id>{option}value...", so the chosen attributes
   * are in the id. The rule is the one of shopping_cart::get_content_type().
   *
   * @param string $uprid the cart id of the position
   * @return bool
   */
  function guarantee_labels_position_physical($uprid) {
    static $known = array();

    $uprid = (string)$uprid;

    if (isset($known[$uprid])) {
      return $known[$uprid];
    }

    require_once(DIR_FS_INC.'xtc_get_prid.inc.php');

    $products_id = (int)xtc_get_prid($uprid);

    // no attribute chosen, so the article answers for the position
    if (!preg_match_all('/\{[0-9]+\}([0-9]+)/', $uprid, $matches)) {
      $known[$uprid] = guarantee_labels_product_physical($products_id);
      return $known[$uprid];
    }

    if ($products_id < 1 || !defined('DOWNLOAD_ENABLED') || DOWNLOAD_ENABLED != 'true') {
      $known[$uprid] = true;
      return true;
    }

    // Option and value together identify a choice. options_values_id is a shared list, so the
    // same value id can sit under two options; collapsing on the value alone would count one
    // choice where the customer made two and call a mixed position digital.
    preg_match_all('/\{([0-9]+)\}([0-9]+)/', $uprid, $pairs);

    $chosen = array();
    $values = array();

    foreach ($pairs[1] as $index => $option) {
      $chosen[(int)$option.':'.(int)$pairs[2][$index]] = true;
      $values[(int)$pairs[2][$index]] = (int)$pairs[2][$index];
    }

    $download_query = xtc_db_query("SELECT COUNT(*) AS total
                                      FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                      JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                           ON pa.products_attributes_id = pad.products_attributes_id
                                     WHERE pa.products_id = '".$products_id."'
                                       AND pa.options_values_id IN ('".implode("', '", $values)."')");
    $download = xtc_db_fetch_array($download_query);
    $downloads = (int)$download['total'];

    if ($downloads < 1) {
      $known[$uprid] = true;
      return true;
    }

    // with multiple downloads allowed one chosen download makes the whole position virtual
    if (defined('DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED') && DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED == 'true') {
      $known[$uprid] = false;
      return false;
    }

    // counted over the chosen combinations, not over the distinct value ids
    $known[$uprid] = (count($chosen) > $downloads);

    return $known[$uprid];
  }

  /**
   * guarantee_labels_product_physical()
   *
   * The commercial guarantee of durability belongs to goods. Digital content and digital
   * services fall under a different set of rules and know no such guarantee, so the label may
   * not stand at a purely virtual article: it would name a promise that cannot exist there.
   *
   * An article with download and ordinary attributes carries goods as well and keeps its label.
   *
   * With downloads switched off the shop answers without a query, which is the default.
   *
   * @param int $products_id
   * @return bool
   */
  function guarantee_labels_product_physical($products_id) {
    static $known = array();

    $products_id = (int)$products_id;

    if ($products_id < 1 || !defined('DOWNLOAD_ENABLED') || DOWNLOAD_ENABLED != 'true') {
      return true;
    }

    if (isset($known[$products_id])) {
      return $known[$products_id];
    }

    // An article is asked before anything is chosen, so the question is whether it can be bought
    // as goods at all: it is digital only when it has nothing but downloads. One download variant
    // next to a physical one makes it mixed, and the list has to show the label.
    //
    // xtcPrice::get_content_type_product() is deliberately not asked. It answers the question of
    // the cart and folds DOWNLOAD_MULTIPLE_ATTRIBUTES_ALLOWED into the article, where one download
    // variant already makes the whole article virtual. For a chosen combination that is right and
    // guarantee_labels_position_physical() applies it; for an article it would drop the label of
    // a mixed one.
    $attributes_query = xtc_db_query("SELECT COUNT(*) AS total,
                                             COUNT(pad.products_attributes_id) AS downloads
                                        FROM ".TABLE_PRODUCTS_ATTRIBUTES." pa
                                   LEFT JOIN ".TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD." pad
                                             ON pa.products_attributes_id = pad.products_attributes_id
                                       WHERE pa.products_id = '".$products_id."'");
    $attributes = xtc_db_fetch_array($attributes_query);

    $known[$products_id] = ((int)$attributes['downloads'] < 1)
                        || ((int)$attributes['total'] > (int)$attributes['downloads']);

    return $known[$products_id];
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
    static $rounds = 0;
    static $bulk = null;

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
      $rounds++;

      // An article listing calls buildDataArray() per row, so the block is never in scope and
      // each new manufacturer would cost its own query. The second round says the caller is
      // walking a list: from there the active manufacturers are read once. The limit keeps a
      // shop with a very large manufacturer table on the one-by-one path.
      if ($rounds > 1 && $bulk === null) {
        $bulk = false;
        $all_query = xtc_db_query("SELECT manufacturers_id,
                                          manufacturers_name
                                     FROM ".TABLE_MANUFACTURERS."
                                    WHERE manufacturers_status = '1'
                                    LIMIT ".(GUARANTEE_LABELS_MANUFACTURER_LIMIT + 1));
        $loaded = array();

        while ($all_query !== false && $manufacturers = xtc_db_fetch_array($all_query)) {
          $loaded[(int)$manufacturers['manufacturers_id']] = $manufacturers['manufacturers_name'];
        }

        // A failed query looks like an empty shop, and marking every id as unknown would keep the
        // labels off for the rest of the request. Only a list that really came back counts.
        if ($all_query !== false
            && count($loaded) > 0
            && count($loaded) <= GUARANTEE_LABELS_MANUFACTURER_LIMIT
            )
        {
          $bulk = true;

          foreach ($loaded as $manufacturers_id => $manufacturers_name) {
            $known[$manufacturers_id] = $manufacturers_name;
          }
        }

        // an id the complete list does not name is inactive or gone, no query can add it
        foreach ($missing as $manufacturers_id) {
          if ($bulk === true && !array_key_exists($manufacturers_id, $known)) {
            $known[$manufacturers_id] = false;
          }

          if (array_key_exists($manufacturers_id, $known)) {
            if ($known[$manufacturers_id] !== false) {
              $names[$manufacturers_id] = $known[$manufacturers_id];
            }

            unset($missing[$manufacturers_id]);
          }
        }
      }
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
   * @param string $uprid the cart id when a combination was chosen
   * @return mixed array with hash, colour.svg and nested.svg, false when no label applies
   */
  function guarantee_labels_product_label($product, $names, $uprid = '') {
    // the same question the caller asked, with the same answer: without the cart id this would
    // fall back to the article and undo the decision made for the chosen combination
    if (!guarantee_labels_candidate($product, $uprid)) {
      return false;
    }

    $manufacturers_id = (int)$product['manufacturers_id'];

    if (!isset($names[$manufacturers_id])) {
      return false;
    }

    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_renderer.php');

    $renderer = new guarantee_labels_renderer();

    $label = $renderer->label($names[$manufacturers_id], $product['products_manufacturers_model'], $product['products_garan_duration']);

    // A name that does not fit, a missing template, a cache directory that cannot be written:
    // an admin action has to be able to say so. A failing cache write does not stop the label,
    // the renderer keeps its error and the caller still reports it.
    if ($renderer->has_errors()) {
      require_once(DIR_FS_INC.'guarantee_labels_snapshot.inc.php');
      guarantee_labels_snapshot_log('render', isset($product['products_id']) ? $product['products_id'] : 0, $renderer->get_errors());
    }

    if ($label === false) {
      return false;
    }

    // The text alternative of the graphic is built from the same values, but in the encoding of
    // the shop and not the one of the svg. Page, database and mail of a latin1 shop are latin1;
    // only the graphic is always utf-8, which the renderer converts for internally. The hash is
    // built from the converted value, so both kinds of shop produce the same label.
    $label['manufacturer'] = $names[$manufacturers_id];
    $label['model'] = $product['products_manufacturers_model'];
    $label['duration'] = $renderer->duration_text($renderer->normalize_duration($product['products_garan_duration']));

    return $label;
  }

  /**
   * guarantee_labels_texts_ready()
   *
   * Whether every text an output needs is really defined.
   *
   * A language package can be installed without the file of this module, or with an older one.
   * Reading an undefined constant is a fatal error in PHP 8, so a shop would answer with a
   * broken page instead of a page without the notice. Every output asks here first.
   *
   * @param array $constants
   * @return bool
   */
  /**
   * The constants a language needs before the notice may be shown or archived.
   *
   * Diagnosis, checkout and snapshot all ask here. Asked separately they drifted apart: the
   * diagnosis called a language complete that the checkout then refused, and the checkout could
   * stay silent while the order still got a snapshot.
   *
   * The label constants are not part of it. The label is language neutral and falls back to its
   * own wording, so a missing one must not decide anything.
   *
   * @return array
   */
  function guarantee_labels_notice_constants() {
    return array(
      'TEXT_GUARANTEE_NOTICE_TITLE',
      'TEXT_GUARANTEE_NOTICE_TEXT',
      'TEXT_GUARANTEE_NOTICE_MAIL',
      'TEXT_GUARANTEE_NOTICE_LINK',
      'TEXT_GUARANTEE_NOTICE_URL',
      'TEXT_GUARANTEE_NOTICE_OPEN',
      'TEXT_GUARANTEE_NOTICE_ALT',
      // a mixed cart is not a special case of the language, it is one of the shop
      'TEXT_GUARANTEE_NOTICE_MIXED',
    );
  }

  function guarantee_labels_texts_ready($constants) {
    foreach ($constants as $constant) {
      if (guarantee_labels_text($constant) === '') {
        return false;
      }
    }

    return true;
  }

  /**
   * One language constant with a fallback. A missing or empty constant must not reach sprintf()
   * or the output, so every read of a module text goes through here.
   *
   * @param string $constant
   * @param string $fallback
   * @return string
   */
  function guarantee_labels_text($constant, $fallback = '') {
    if (!defined($constant)) {
      return $fallback;
    }

    $value = trim((string)constant($constant));

    return ($value === '') ? $fallback : $value;
  }

  /**
   * guarantee_labels_attribute()
   *
   * Prepares a language constant for an html attribute. The language packages write umlauts as
   * entities, so escaping them again would turn the ampersand into &amp; and the attribute would
   * show the entity itself. The text is decoded first and then escaped once, through the shop
   * helpers, so both steps use the charset the shop actually runs on.
   *
   * @param string $text
   * @return string
   */
  function guarantee_labels_attribute($text) {
    return encode_htmlspecialchars(decode_htmlentities($text, ENT_QUOTES), ENT_QUOTES);
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

    // The label itself is language neutral. A shop language without the module texts still gets
    // the graphic; only the wording around it falls back. GARAN is the official name of the
    // label and needs no translation.
    $title = guarantee_labels_text('TEXT_GUARANTEE_LABEL_TITLE', 'GARAN');
    $open = guarantee_labels_text('TEXT_GUARANTEE_LABEL_OPEN', $title);
    $close = guarantee_labels_text('TEXT_GUARANTEE_LABEL_CLOSE', '&times;');
    $reload = guarantee_labels_text('TEXT_GUARANTEE_LABEL_RELOAD', '');

    $link = '';

    // both parts are needed, a link without a text would be invisible
    if (guarantee_labels_text('TEXT_GUARANTEE_LABEL_URL') !== ''
        && guarantee_labels_text('TEXT_GUARANTEE_LABEL_LINK') !== ''
        )
    {
      $link = '<a class="guarantee-label__link" href="'.guarantee_labels_attribute(TEXT_GUARANTEE_LABEL_URL).'" target="_blank" rel="noopener">'.TEXT_GUARANTEE_LABEL_LINK.'</a>';
    }

    // The full label carries the GARAN title in every EU language as outlined paths and weighs
    // around 290 kB. Only the compact one goes into the page; the full one is fetched from the
    // cache on the first open. It is inserted into the dom, so it uses the fonts loaded once.
    // The renderer just read or wrote the cache and says whether the full label is in it. Asking
    // guarantee_labels_cache_url() here would read and hash the same 294 kB a second time; an
    // older caller without the flag still gets the check.
    $cached = array_key_exists('cached', $label)
            ? ($label['cached'] === true)
            : (guarantee_labels_cache_url($label['hash']) !== '');

    $source = $cached
            ? (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '').'cache/guarantee_labels/'.$label['hash'].'/colour.svg'
            : '';
    $full = ($source === '') ? guarantee_labels_inline_svg($label['colour.svg']) : '';

    // the content is opened in the lightbox of the template when there is one, so the label
    // behaves like every other overlay of the shop; the dialog is the fallback without it
    static $counter = 0;
    $id = 'guarantee-label-content-'.(++$counter);

    $duration = isset($label['duration']) ? $label['duration'] : '';
    $manufacturer = isset($label['manufacturer']) ? $label['manufacturer'] : '';
    $model = isset($label['model']) ? $label['model'] : '';

    $alt_compact = (guarantee_labels_text('TEXT_GUARANTEE_LABEL_ALT_COMPACT') !== '')
                 ? sprintf(TEXT_GUARANTEE_LABEL_ALT_COMPACT, $duration)
                 : trim($title.' '.$duration);
    $alt_full = (guarantee_labels_text('TEXT_GUARANTEE_LABEL_ALT') !== '')
              ? sprintf(TEXT_GUARANTEE_LABEL_ALT, $duration, $manufacturer, $model)
              : trim($title.' '.$duration.' '.$manufacturer.' '.$model);

    return '<div class="guarantee-label">'.
             '<button type="button" class="guarantee-label__compact" data-guarantee-label-content="'.$id.'" data-guarantee-label-title="'.guarantee_labels_attribute($title).'" title="'.guarantee_labels_attribute($open).'" aria-label="'.guarantee_labels_attribute($alt_compact.' '.$open).'">'.
               guarantee_labels_inline_svg($label['nested.svg']).
             '</button>'.
             '<dialog class="guarantee-label__dialog" aria-label="'.guarantee_labels_attribute($title).'">'.
               '<div class="guarantee-label__content" id="'.$id.'">'.
                 '<div class="guarantee-label__full"'.($source !== '' ? ' data-guarantee-label-src="'.encode_htmlspecialchars($source).'"'.(($reload !== '') ? ' data-guarantee-label-error="'.guarantee_labels_attribute($reload).'"' : '') : '').'>'.
                   '<div class="guarantee-label__graphic" role="img" aria-label="'.guarantee_labels_attribute($alt_full).'">'.$full.'</div>'.
                   $link.
                 '</div>'.
               '</div>'.
               '<button type="button" class="guarantee-label__close">'.$close.'</button>'.
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
  function guarantee_labels_cache_url($hash, $name = 'colour.svg') {
    if (guarantee_labels_cache_intact($hash, $name) === false) {
      return '';
    }

    return (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '').'cache/guarantee_labels/'.$hash.'/'.$name;
  }

  /**
   * A cached file is only worth linking to when it still matches its checksum. Pointing the page
   * at a damaged file would serve the damage instead of falling back to the inline graphic.
   *
   * @param string $hash the cache directory
   * @param string $name the file inside it
   * @return bool
   */
  function guarantee_labels_cache_intact($hash, $name) {
    require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

    $archive = new guarantee_labels_archive();

    return ($archive->cache_read($hash, array($name)) !== false);
  }

  /**
   * The address an archived notice graphic is served from.
   *
   * @param string $hash
   * @return string empty when the cached copy is missing
   */
  function guarantee_labels_notice_cache_url($hash) {
    return guarantee_labels_cache_url($hash, 'notice.svg');
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
    $role = ($alt === '') ? ' aria-hidden="true"' : ' role="img" aria-label="'.encode_htmlspecialchars($alt).'"';

    return preg_replace('/<svg\b/', '<svg'.$role, $svg, 1);
  }

  /**
   * guarantee_labels_physical()
   *
   * Digital content and services are not covered by the labelling duty. The shop knows the
   * content type of the cart, everything that is not purely virtual counts as physical here.
   *
   * @param mixed $content_type physical, virtual, mixed, or false while downloads are off
   * @return bool
   */
  function guarantee_labels_physical($content_type) {
    return !in_array($content_type, array('virtual', 'virtual_weight'), true);
  }

  /**
   * guarantee_labels_notice_parts()
   *
   * The parts of the notice about the legal guarantee that has to stand before the order
   * button, so a template can place them in its own layout.
   *
   * The graphic is an A4 sheet of about 640 kB that consists of outlined paths only. It carries
   * no live text and is therefore referenced as an image, fetched when the overlay opens.
   *
   * @param mixed $content_type the content type of the cart, virtual for downloads only
   * @return mixed array of title and body, false when the notice does not apply
   */
  function guarantee_labels_notice_parts($content_type = false) {
    if (!guarantee_labels_active() || !guarantee_labels_physical($content_type)) {
      return false;
    }

    $language = isset($_SESSION['language']) ? $_SESSION['language'] : '';
    $file = 'lang/'.$language.'/notice.svg';

    // a language package brings its own graphic and its own texts, without either there is
    // nothing to show; a missing constant would end the request instead of the notice
    if ($language === '' || !guarantee_labels_texts_ready(guarantee_labels_notice_constants())) {
      return false;
    }

    // a missing graphic costs the illustration, never the notice itself
    $source = is_file(DIR_FS_CATALOG.$file)
            ? encode_htmlspecialchars((defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '').$file)
            : '';
    $alt = guarantee_labels_attribute(TEXT_GUARANTEE_NOTICE_ALT);

    $link = '';

    if (defined('TEXT_GUARANTEE_NOTICE_URL') && trim(TEXT_GUARANTEE_NOTICE_URL) !== '') {
      $link = '<a class="guarantee-notice__link" href="'.guarantee_labels_attribute(TEXT_GUARANTEE_NOTICE_URL).'" target="_blank" rel="noopener">'.TEXT_GUARANTEE_NOTICE_LINK.'</a>';
    }

    $mixed = ($content_type === 'mixed') ? '<p class="guarantee-notice__mixed">'.TEXT_GUARANTEE_NOTICE_MIXED.'</p>' : '';

    static $counter = 0;
    $id = 'guarantee-notice-content-'.(++$counter);

    // The sheet is a full A4 page. The checkout carries the wording and a control, the graphic
    // itself opens in the lightbox and is fetched on the first click, so the page stays light.
    // The overlay classes of the label are reused, both graphics therefore open the same way.
    return guarantee_labels_notice_block(TEXT_GUARANTEE_NOTICE_TITLE, TEXT_GUARANTEE_NOTICE_TEXT, $link, $mixed, $source, $alt);
  }

  /**
   * guarantee_labels_notice_block()
   *
   * Builds title and body of the notice from explicit values, so the checkout can show the
   * current wording and an order view the one that was archived with it.
   *
   * @return array
   */
  function guarantee_labels_notice_block($title, $text, $link, $mixed, $source, $alt) {
    static $counter = 0;
    $id = 'guarantee-notice-content-'.(++$counter);

    // the chrome around the archived text must not cost the notice its output
    $open = guarantee_labels_text('TEXT_GUARANTEE_NOTICE_OPEN', 'GARAN');
    $close = guarantee_labels_text('TEXT_GUARANTEE_LABEL_CLOSE', '&times;');
    $reload = guarantee_labels_text('TEXT_GUARANTEE_LABEL_RELOAD', '');

    // The notice is the statement, the graphic only illustrates it. Without a usable source the
    // text and the link still go out; an unwritable cache directory must not take a legally
    // required notice off the page. The button would open an empty dialogue, so it stays away.
    $graphic = ($source === '')
             ? ''
             : '<button type="button" class="guarantee-label__compact guarantee-notice__open" data-guarantee-label-content="'.$id.'" data-guarantee-label-title="'.guarantee_labels_attribute($title).'">'.
                 $open.
               '</button>'.
               '<dialog class="guarantee-label__dialog" aria-label="'.guarantee_labels_attribute($title).'">'.
                 '<div class="guarantee-label__content" id="'.$id.'">'.
                   '<div class="guarantee-label__full guarantee-notice__full" data-guarantee-label-img="'.$source.'" data-guarantee-label-alt="'.$alt.'"'.(($reload !== '') ? ' data-guarantee-label-error="'.guarantee_labels_attribute($reload).'"' : '').'>'.
                     '<div class="guarantee-label__graphic"></div>'.
                   '</div>'.
                 '</div>'.
                 '<button type="button" class="guarantee-label__close">'.$close.'</button>'.
               '</dialog>';

    return array(
      'title' => $title,
      'body' => '<p class="guarantee-notice__text">'.$text.'</p>'.
                $mixed.
                $graphic.
                $link,
    );
  }

  /**
   * guarantee_labels_notice()
   *
   * The complete block with its own frame and heading, for a template that places the notice
   * as one piece. A template with its own box layout uses guarantee_labels_notice_parts().
   *
   * @param mixed $content_type the content type of the cart, virtual for downloads only
   * @return string
   */
  function guarantee_labels_notice($content_type = false) {
    return guarantee_labels_notice_wrap(guarantee_labels_notice_parts($content_type));
  }

  /**
   * Puts frame and heading around the parts of the notice.
   *
   * @param mixed $parts the result of guarantee_labels_notice_parts()
   * @return string
   */
  function guarantee_labels_notice_wrap($parts) {
    if ($parts === false) {
      return '';
    }

    return '<div class="guarantee-notice">'.
             '<p class="guarantee-notice__title">'.$parts['title'].'</p>'.
             $parts['body'].
           '</div>';
  }
