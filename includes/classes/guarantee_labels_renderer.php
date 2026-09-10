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
  require_once(DIR_FS_CATALOG.'inc/guarantee_labels_log.inc.php');
  // the path is spelled out because DIR_WS_CLASSES is absolute in the storefront and relative
  // in the administration, and this class runs in both
  require_once(DIR_FS_CATALOG.'includes/classes/guarantee_labels_archive.php');

  /**
   * Builds the GARAN label from the official EU templates.
   *
   * Only the three editable fields of the templates are replaced, everything else stays
   * untouched. Both variants are always produced together and share one hash, so the compact
   * and the full label of an article can never drift apart.
   */
  class guarantee_labels_renderer {

    // Bump this version when rendering changes so current labels get new cache keys.
    // Existing order snapshots keep their stored hashes and archived graphics.
    const RENDERER_VERSION = '1.01';

    const TOKEN_DURATION = 'XX';
    const TOKEN_MANUFACTURER = 'Brand/Trademark';
    const TOKEN_MODEL = 'Model identifier';

    // the measured text is rejected this much before the editable area really ends
    const WIDTH_TOLERANCE = 2;

    // At the official 95 x 100 mm size these SVG units correspond to points.
    // Share the row from x 6.32 to 262.97; never shrink either editable text below 9 pt.
    const TEXT_FONT_SIZE = 9;
    const TEXT_ROW_WIDTH = 256.65;
    const TEXT_COLUMN_GAP = 8;

    // The original model element starts at x 196.75. Anchor its tspan at the right margin.
    const MODEL_RIGHT_OFFSET = '66.22';

    // imagettfbbox() renders at 96 dpi while an svg user unit is one pixel, so a measured
    // width has to be scaled before it can be compared with the template layout
    const UNIT_SCALE = 0.75;

    // GD rounds glyph advances at small sizes. Measure at a larger size, then scale
    // back to avoid underestimating long identifiers. The rendered font stays at 9 pt.
    const MEASUREMENT_SCALE = 64;

    var $base_dir;
    var $asset_dir;
    var $font_dir;
    var $archive;
    var $errors;

    function __construct() {
      $this->base_dir = DIR_FS_CATALOG.'images/guarantee_labels/';
      $this->asset_dir = $this->base_dir.'assets/';
      $this->font_dir = $this->base_dir.'fonts/';
      $this->archive = new guarantee_labels_archive();
      $this->errors = array();
    }

    /**
     * The editable areas of the official templates.
     *
     * font, font_size and max_width are read from the shipped templates: the colour label sets
     * the duration in Inter-ExtraBold at 80 and both text fields in Inter-Regular at 9, all in
     * svg user units. Manufacturer and model share the row between x 6.32 and 262.97:
     * the name starts at the left and the identifier ends at the right. fits_texts() checks
     * their combined width and spacing, so a short name leaves more room for the identifier.
     */
    function areas() {
      return array(
        'duration' => array('token' => self::TOKEN_DURATION, 'font' => 'Inter-ExtraBold.ttf', 'font_size' => 80, 'max_width' => 190.43),
        'manufacturer' => array('token' => self::TOKEN_MANUFACTURER, 'font' => 'Inter-Regular.ttf', 'font_size' => self::TEXT_FONT_SIZE, 'max_width' => self::TEXT_ROW_WIDTH),
        'model' => array('token' => self::TOKEN_MODEL, 'font' => 'Inter-Regular.ttf', 'font_size' => self::TEXT_FONT_SIZE, 'max_width' => self::TEXT_ROW_WIDTH),
      );
    }

    /**
     * The compact variant only carries the duration, the full label carries all three fields.
     */
    function templates() {
      return array(
        'colour.svg' => array('file' => $this->asset_dir.'garan_label_colour.svg', 'areas' => array('duration', 'manufacturer', 'model')),
        'nested.svg' => array('file' => $this->asset_dir.'garan_label_nested.svg', 'areas' => array('duration')),
      );
    }

    function fonts() {
      return array(
        'Inter-Regular.ttf' => $this->font_dir.'Inter-Regular.ttf',
        'Inter-ExtraBold.ttf' => $this->font_dir.'Inter-ExtraBold.ttf',
      );
    }

    /**
     * The files the browser needs for the same label the renderer measured with the ttf fonts.
     *
     * A missing woff2 lets the browser substitute a font, and the drawing drifts away from the
     * width this class checked. The style sheet and the script belong here as well: without them
     * the label stays unstyled and the overlay cannot open.
     *
     * @return array name => path
     */
    function browser_assets() {
      return array(
        'Inter-Regular.woff2' => $this->font_dir.'Inter-Regular.woff2',
        'Inter-ExtraBold.woff2' => $this->font_dir.'Inter-ExtraBold.woff2',
        'guarantee_labels.css' => $this->base_dir.'guarantee_labels.css',
        'guarantee_labels.js' => $this->base_dir.'guarantee_labels.js',
      );
    }

    // ---------------------------------------------------------- environment --

    /**
     * GD with FreeType is a hard requirement, the label may neither be scaled down nor cut off
     * and both need a real text measurement.
     */
    function is_available() {
      return (function_exists('imagettfbbox') && function_exists('gd_info'));
    }

    /**
     * @return array reasons why the module cannot render, empty when everything is in place
     */
    function missing_requirements() {
      $missing = array();

      if (!$this->is_available()) {
        $missing[] = 'gd_freetype';
      }

      foreach ($this->templates() as $name => $template) {
        if (!is_file($template['file'])) {
          $missing[] = 'template:'.basename($template['file']);
        }
      }

      foreach ($this->fonts() as $name => $file) {
        if (!is_file($file)) {
          $missing[] = 'font:'.$name;
        }
      }

      // the browser draws the same label, an incomplete package lets it drift from the measurement
      foreach ($this->browser_assets() as $name => $file) {
        if (!is_file($file)) {
          $missing[] = 'asset:'.$name;
        }
      }

      foreach ($this->areas() as $name => $area) {
        if ($area['font_size'] <= 0 || $area['max_width'] <= 0
            || (in_array($name, array('manufacturer', 'model'), true) && $area['font_size'] < 9)) {
          $missing[] = 'metrics:'.$name;
        }
      }

      return $missing;
    }

    function is_ready() {
      return (count($this->missing_requirements()) < 1);
    }

    // --------------------------------------------------------------- values --

    /**
     * Accepts comma and point and returns the canonical database value.
     *
     * Every duration a manufacturer states is stored, down to half a year, so the catalogue
     * mirrors what a supplier delivered instead of dropping what does not qualify. Whether a
     * label is built from it is a separate question, see qualifies().
     *
     * @return mixed string with one decimal, false when the value is not a valid duration
     */
    function normalize_duration($value) {
      $value = trim(str_replace(',', '.', (string)$value));

      if ($value === '' || !preg_match('/^[0-9]+(\.[0-9])?$/', $value)) {
        return false;
      }

      $value = number_format((float)$value, 1, '.', '');

      // a zero duration is no statement, and only whole or half years exist
      if ((float)$value < 0.5 || !in_array(substr($value, -1), array('0', '5'))) {
        return false;
      }

      // the practical guidelines show full numbers up to two digits, half years included
      if ((float)$value > 99.5) {
        return false;
      }

      return $value;
    }

    /**
     * Only a guarantee longer than the legal two years may carry a label. A guarantee of
     * exactly two years gives the customer nothing on top, so the implementing regulation
     * reserves the label for longer periods.
     *
     * @return bool
     */
    function qualifies($duration) {
      $duration = $this->normalize_duration($duration);

      return ($duration !== false && (float)$duration > 2.0);
    }

    /**
     * The value as it appears in the label. The practical guidelines require whole years
     * without a decimal part and a comma in front of the half year.
     */
    function duration_text($duration) {
      return (substr($duration, -1) == '0') ? (string)(int)$duration : str_replace('.', ',', $duration);
    }

    // ----------------------------------------------------------------- fits --

    /**
     * One product value in the encoding the svg and the measurement expect.
     *
     * The charset of the shop is named instead of detected. mb_detect_encoding() tries
     * ISO-8859-1 before ISO-8859-15 and would turn a euro sign into a currency sign, which the
     * label would then carry for good.
     *
     * Called once where catalogue data enters the renderer, never a second time on a value that
     * has already passed through: some ISO-8859-15 byte sequences are valid utf-8 as well, so a
     * second pass cannot tell the two apart and would corrupt the text.
     *
     * @param string $value
     * @return string
     */
    function to_utf8($value) {
      $charset = (defined('DB_SERVER_CHARSET') && strpos(DB_SERVER_CHARSET, 'utf8') === false)
               ? 'ISO-8859-15'
               : 'UTF-8';

      return encode_utf8((string)$value, $charset, true);
    }

    /**
     * One catalogue value prepared for a width check, so a caller outside the renderer converts through
     * the same boundary label() uses.
     *
     * @param string $value
     * @return string
     */
    function measurable($value) {
      return $this->to_utf8($value);
    }

    /**
     * Measures the real text width with the prescribed font instead of counting characters.
     *
     * @return mixed width in SVG units, false when the font or metrics cannot be used
     */
    function text_width($area_name, $text) {
      // $text has to be utf-8 already, see to_utf8(). label() converts before it asks, and a
      // caller from outside converts through measurable() first.
      $areas = $this->areas();

      if (!isset($areas[$area_name])) {
        return false;
      }

      $area = $areas[$area_name];
      $font = $this->font_dir.$area['font'];

      if (!$this->is_available() || !is_file($font) || $area['font_size'] <= 0 || $area['max_width'] <= 0
          || (in_array($area_name, array('manufacturer', 'model'), true) && $area['font_size'] < 9)) {
        return false;
      }

      $box = @imagettfbbox($area['font_size'] * self::MEASUREMENT_SCALE, 0, $font, (string)$text);

      if ($box === false) {
        return false;
      }

      return abs($box[2] - $box[0]) * self::UNIT_SCALE / self::MEASUREMENT_SCALE;
    }

    /**
     * Whether one UTF-8 value fits by itself. Use fits_texts() for the shared header row.
     */
    function fits($area_name, $text) {
      $width = $this->text_width($area_name, $text);
      if ($width === false) {
        return false;
      }

      $areas = $this->areas();
      return ($width <= ($areas[$area_name]['max_width'] - self::WIDTH_TOLERANCE));
    }

    /**
     * Checks both UTF-8 header texts with the same metrics used for their SVG output.
     * A long identifier can use the space left by a short manufacturer name. If their
     * combined width is too large, reject it instead of clipping or shrinking either text.
     */
    function fits_texts($manufacturer, $model) {
      $manufacturer_width = $this->text_width('manufacturer', $manufacturer);
      $model_width = $this->text_width('model', $model);

      if ($manufacturer_width === false || $model_width === false) {
        return false;
      }

      $areas = $this->areas();
      $row_width = min(self::TEXT_ROW_WIDTH, $areas['manufacturer']['max_width'], $areas['model']['max_width']);

      return ($manufacturer_width + $model_width + self::TEXT_COLUMN_GAP <= $row_width - 2 * self::WIDTH_TOLERANCE);
    }

    // ----------------------------------------------------------------- hash --

    /**
     * sha256 over the label values plus the version of both templates, the fonts and the
     * renderer. Every field carries its length, so a different split cannot produce the same
     * input stream.
     */
    function garan_hash($manufacturer, $model, $duration) {
      $fields = array(
        (string)$manufacturer,
        (string)$model,
        (string)$duration,
        $this->asset_hash(),
        self::RENDERER_VERSION,
      );

      $stream = '';
      foreach ($fields as $field) {
        $stream .= strlen($field).':'.$field.'|';
      }

      return hash('sha256', $stream);
    }

    /**
     * Templates and fonts enter the hash with the sha256 of their real content, so a new
     * official file version automatically produces new labels.
     */
    function asset_hash() {
      static $hash;

      if (isset($hash)) {
        return $hash;
      }

      $stream = '';

      // everything a label is built from, in the browser as well: a changed woff2 or style sheet
      // has to produce a new hash, otherwise a stale cache copy keeps being served
      $files = array_merge($this->fonts(), $this->browser_assets());
      foreach ($this->templates() as $template) {
        $files[] = $template['file'];
      }

      // Size and modification time, not the content: hashing them costs a stat where hashing the
      // files costs 1.4 MB of reads on every page that shows a single label. Replacing a font or
      // a template changes both, and a change that keeps size and second is not one a shop makes
      // by accident. RENDERER_VERSION covers a deliberate change of the drawing itself.
      foreach ($files as $file) {
        $stream .= is_file($file) ? filesize($file).':'.filemtime($file) : '-';
        $stream .= '|';
      }

      $hash = hash('sha256', self::RENDERER_VERSION.'|'.$stream);

      return $hash;
    }

    // --------------------------------------------------------------- render --

    /**
     * Returns both label variants, from the cache when possible.
     *
     * @return mixed array with hash, colour.svg and nested.svg, false when nothing can be built
     */
    function label($manufacturer, $model, $duration) {
      $duration = $this->normalize_duration($duration);

      if ($duration === false || !$this->qualifies($duration)) {
        return false;
      }

      // The svg is utf-8 and so is imagettfbbox(). A shop on latin1 would otherwise hand in a
      // byte sequence that the xml escaping rejects, which drops the value from the label
      // without any error. Converted once here, so measuring, hashing and rendering agree.
      $manufacturer = $this->to_utf8($manufacturer);
      $model = $this->to_utf8($model);

      if (trim($manufacturer) === '' || trim($model) === '') {
        return false;
      }

      if (!$this->is_ready()) {
        $this->fail('the renderer is not ready: '.implode(', ', $this->missing_requirements()));
        return false;
      }

      // The article administration checks this early to give a useful message, but it is not
      // the only way into the columns: a renamed manufacturer, an import or a foreign system
      // never revalidates the articles behind it. The name and identifier must fit together
      // at 9 pt, even when a cached graphic is already present.
      if ($this->fits_texts($manufacturer, $model) === false) {
        $this->fail('the manufacturer and model do not fit their shared row at 9 pt: '.$manufacturer.' / '.$model);
        return false;
      }

      $hash = $this->garan_hash($manufacturer, $model, $duration);
      $names = array_keys($this->templates());

      $files = $this->archive->cache_read($hash, $names);

      // The caller has to know whether the cache holds the full label: asking again means reading
      // and hashing the 294 kB colour.svg a second time for an answer this call already has.
      $cached = ($files !== false);

      if ($files === false) {
        $files = $this->render($manufacturer, $model, $duration);

        if ($files === false) {
          return false;
        }

        // A failing cache write must not stop the current request, and the return value is
        // deliberately not read: the archive keeps its error, get_errors() merges it, and the
        // caller reports it after a successful label. This is the one unchecked write.
        $cached = ($this->archive->cache_write($hash, $files) !== false);
      }

      return array_merge(array('hash' => $hash, 'cached' => $cached), $files);
    }

    /**
     * Builds both variants from the official templates without touching the cache.
     *
     * @return mixed array of file name and content, false on a template mismatch
     */
    function render($manufacturer, $model, $duration) {
      // Direct callers must obey the same layout limit as label() and the save/import checks.
      if ($this->fits_texts($manufacturer, $model) === false) {
        $this->fail('the manufacturer and model do not fit their shared row at 9 pt: '.$manufacturer.' / '.$model);
        return false;
      }

      $values = array(
        'duration' => $this->duration_text($duration),
        'manufacturer' => (string)$manufacturer,
        'model' => (string)$model,
      );

      $areas = $this->areas();
      $files = array();

      foreach ($this->templates() as $name => $template) {
        $svg = @file_get_contents($template['file']);

        if ($svg === false || $svg === '') {
          $this->fail('template cannot be read: '.$template['file']);
          return false;
        }

        // All areas in one pass over the untouched template. Replacing them one after another
        // would let a value written earlier look like the token of a later field, and a
        // manufacturer named after one of them would drop the label with a confusing reason.
        $tokens = array();

        foreach ($template['areas'] as $area_name) {
          $tokens[$areas[$area_name]['token']] = $values[$area_name];
        }

        $svg = $this->replace_areas($svg, $tokens, $name);

        if ($svg === false) {
          return false;
        }

        $files[$name] = $svg;
      }

      return $files;
    }

    /**
     * Fills every editable area of one template in a single pass.
     *
     * The official templates split a field over several tspans to carry the kerning of the
     * placeholder, so a token cannot be matched as plain text. The whole content of the text
     * element whose text equals the token is replaced instead. Its class and vertical position
     * remain intact. The model tspan ends at the right margin of the same row; both header
     * fields retain their 9 pt size. Placeholder kerning does not apply to the new value.
     *
     * Each token has to appear exactly once, otherwise the template is not the expected official
     * file and nothing is rendered.
     *
     * @param string $svg the untouched template
     * @param array $tokens token => value
     * @param string $template the template name, for the error message
     * @return mixed the filled svg, false when a token is missing or appears more than once
     */
    function replace_areas($svg, $tokens, $template) {
      $counts = array_fill_keys(array_keys($tokens), 0);

      $result = preg_replace_callback('/(<text\b[^>]*>)(.*?)(<\/text>)/s', function ($match) use ($tokens, &$counts) {
        $content = trim(decode_htmlentities(strip_tags($match[2]), ENT_QUOTES | ENT_XML1));

        if (!array_key_exists($content, $tokens)) {
          return $match[0];
        }

        $counts[$content]++;

        $attributes = 'x="0" y="0"';
        if ($content === self::TOKEN_MODEL) {
          $attributes = 'x="'.self::MODEL_RIGHT_OFFSET.'" y="0" text-anchor="end"';
        }
        if ($content === self::TOKEN_MANUFACTURER || $content === self::TOKEN_MODEL) {
          // FreeType/GD does not apply this font's GPOS kerning, whereas browsers do.
          // Disable it in the editable row so long sequences keep the measured spacing.
          $attributes .= ' style="font-size:'.self::TEXT_FONT_SIZE.'px;font-kerning:none"';
        }

        // product data must never be able to inject own svg or html
        return $match[1].
               '<tspan '.$attributes.'>'.encode_htmlspecialchars($tokens[$content], ENT_QUOTES | ENT_XML1, 'UTF-8').'</tspan>'.
               $match[3];
      }, $svg);

      foreach ($counts as $token => $count) {
        if ($count !== 1) {
          $this->fail('field "'.$token.'" appears '.(int)$count.' times in '.$template.', expected exactly once');
          return false;
        }
      }

      return $result;
    }

    // --------------------------------------------------------------- errors --

    function has_errors() {
      return (count($this->errors) > 0 || $this->archive->has_errors());
    }

    function get_errors() {
      return array_merge($this->errors, $this->archive->get_errors());
    }

    function fail($reason) {
      $this->errors[] = $reason;

      guarantee_labels_log('error', 'guarantee labels renderer failed: {reason}', array('reason' => $reason));
    }

  }
