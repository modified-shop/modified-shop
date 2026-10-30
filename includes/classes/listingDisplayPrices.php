<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

class listingDisplayPrices {

  var $rows = array();
  var $product_ids = array();
  var $prices = array();
  var $price_filtered = false;

  /**
   * Returns validated bounds in the customer's display currency.
   */
  static function getBounds($from = null, $to = null) {
    $bounds = array('from' => null, 'to' => null);
    foreach (array('from' => $from, 'to' => $to) as $key => $value) {
      if (is_scalar($value) && !is_bool($value)) {
        $value = str_replace(',', '.', trim((string)$value));
        if ($value !== '' && is_numeric($value)) {
          $bounds[$key] = (float)$value;
        }
      }
    }
    return $bounds;
  }

  /**
   * Recognises a primary price column only in the outer ORDER BY clause.
   */
  static function getPriceDirection($query) {
    $masked = preg_replace_callback(
      '/\'(?:\\\\.|\'\'|[^\'\\\\])*\'|"(?:\\\\.|""|[^"\\\\])*"|\/\*.*?\*\/|(?:--[ \t]|#)[^\n]*/s',
      function ($match) {
        return str_repeat(' ', strlen($match[0]));
      },
      $query
    );
    if ($masked === null) {
      return null;
    }
    $depth = 0;
    $order = null;
    for ($i = 0, $n = strlen($masked); $i < $n; $i++) {
      if ($masked[$i] === '`') {
        for ($i++; $i < $n; $i++) {
          if ($masked[$i] === '`') {
            if ($i + 1 < $n && $masked[$i + 1] === '`') {
              $i++;
            } else {
              break;
            }
          }
        }
      } elseif ($masked[$i] === '(') {
        $depth++;
      } elseif ($masked[$i] === ')') {
        $depth--;
      } elseif ($depth == 0 && ($i == 0 || !preg_match('/[a-z0-9_]/i', $masked[$i - 1]))
                && preg_match('/\GORDER\s+BY\b/i', $masked, $match, 0, $i))
      {
        $order = $i + strlen($match[0]);
      }
    }
    if ($order !== null
        && preg_match('/^\s*(?:`?price`?|`?p`?\s*\.\s*`?products_price`?|`?s`?\s*\.\s*`?specials_new_products_price`?)(?:\s+(ASC|DESC))?(?=\s*(?:,|$))/i', substr($masked, $order), $match))
    {
      return (isset($match[1]) && strtoupper($match[1]) === 'DESC') ? 'DESC' : 'ASC';
    }
    return null;
  }

  static function isRequired($query, $price, $bounds = array()) {
    return ($price->cStatus['customers_status_show_price'] != '0'
            && (isset($bounds['from']) || isset($bounds['to']) || self::getPriceDirection($query) !== null));
  }

  /**
   * Quotes only the complete result of the existing non-price candidate query.
   * Filtering and sorting precede pagination; SQL retains every other restriction.
   */
  function __construct($query, $price, $product, $bounds = array()) {
    $candidates = array();
    $result = xtDBquery($query);
    while ($row = xtc_db_fetch_array($result, true)) {
      $pID = (int)$row['products_id'];
      if (!isset($candidates[$pID])) {
        $candidates[$pID] = $row;
      }
    }
    if (is_object($product) && is_callable(array($product, 'preloadAttributesCount'))) {
      $product->preloadAttributesCount(array_keys($candidates));
    }
    $price->preloadAttributeMinimum(array_keys($candidates));
    $this->price_filtered = (isset($bounds['from']) || isset($bounds['to']));
    $direction = self::getPriceDirection($query);
    $quoted_prices = array();
    foreach ($candidates as $pID => $row) {
      $quote = $price->xtcGetPrice($pID, true, 1, $row['products_tax_class_id'], $row['products_price'], 1, 0, true);
      $display_price = round((float)$quote['plain'], $price->get_decimal_places());
      if ((isset($bounds['from']) && $display_price < $bounds['from'])
          || (isset($bounds['to']) && $display_price > $bounds['to']))
      {
        continue;
      }
      $quoted_prices[$pID] = $display_price;
      $this->prices[$pID] = $quote;
      $this->rows[] = $row;
    }
    if ($direction !== null) {
      usort($this->rows, function ($a, $b) use ($quoted_prices, $direction) {
        $aID = (int)$a['products_id'];
        $bID = (int)$b['products_id'];
        if ($quoted_prices[$aID] == $quoted_prices[$bID]) {
          return $aID <=> $bID;
        }
        $comparison = $quoted_prices[$aID] <=> $quoted_prices[$bID];
        return ($direction === 'DESC') ? -$comparison : $comparison;
      });
    }
    foreach ($this->rows as $row) {
      $this->product_ids[] = (int)$row['products_id'];
    }
  }
}
