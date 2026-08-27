<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  require_once(DIR_FS_CATALOG.'inc/guarantee_labels_log.inc.php');
  require_once(DIR_FS_CATALOG.DIR_WS_CLASSES.'guarantee_labels_archive.php');

  /**
   * Builds the GARAN label from the official EU templates.
   *
   * Only the three editable fields of the templates are replaced, everything else stays
   * untouched. Both variants are always produced together and share one hash, so the compact
   * and the full label of an article can never drift apart.
   */
  class guarantee_labels_renderer {

    // any change of the rendering itself has to invalidate existing hashes
    const RENDERER_VERSION = '1.00';

    const TOKEN_DURATION = 'XX';
    const TOKEN_MANUFACTURER = 'Brand/Trademark';
    const TOKEN_MODEL = 'Model identifier';

    // the measured text is rejected this much before the editable area really ends
    const WIDTH_TOLERANCE = 2;

    var $asset_dir;
    var $font_dir;
    var $archive;
    var $errors;

    function __construct() {
      $this->asset_dir = DIR_FS_CATALOG.'images/guarantee_labels/assets/';
      $this->font_dir = DIR_FS_CATALOG.'images/guarantee_labels/fonts/';
      $this->archive = new guarantee_labels_archive();
      $this->errors = array();
    }

    /**
     * The three editable areas of the official templates.
     *
     * font_size and max_width have to be taken from the official template once and belong to
     * the shipped asset version: font_size is the value imagettfbbox() is called with, max_width
     * is the width of the editable area measured in the same unit. Both are zero as long as the
     * official files have not been added, which keeps the module from rendering a wrong label.
     */
    function areas() {
      return array(
        'duration' => array('token' => self::TOKEN_DURATION, 'font' => 'Inter-ExtraBold.ttf', 'font_size' => 0, 'max_width' => 0),
        'manufacturer' => array('token' => self::TOKEN_MANUFACTURER, 'font' => 'Inter-SemiBold.ttf', 'font_size' => 0, 'max_width' => 0),
        'model' => array('token' => self::TOKEN_MODEL, 'font' => 'Inter-Regular.ttf', 'font_size' => 0, 'max_width' => 0),
      );
    }

    function templates() {
      return array(
        'colour.svg' => $this->asset_dir.'garan_label_colour.svg',
        'nested.svg' => $this->asset_dir.'garan_label_nested.svg',
      );
    }

    function fonts() {
      return array(
        'Inter-Regular.ttf' => $this->font_dir.'Inter-Regular.ttf',
        'Inter-SemiBold.ttf' => $this->font_dir.'Inter-SemiBold.ttf',
        'Inter-ExtraBold.ttf' => $this->font_dir.'Inter-ExtraBold.ttf',
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

      foreach ($this->templates() as $name => $file) {
        if (!is_file($file)) {
          $missing[] = 'template:'.basename($file);
        }
      }

      foreach ($this->fonts() as $name => $file) {
        if (!is_file($file)) {
          $missing[] = 'font:'.$name;
        }
      }

      foreach ($this->areas() as $name => $area) {
        if ($area['font_size'] <= 0 || $area['max_width'] <= 0) {
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
     * @return mixed string with one decimal, false when the value is not a valid duration
     */
    function normalize_duration($value) {
      $value = trim(str_replace(',', '.', (string)$value));

      if ($value === '' || !preg_match('/^[0-9]+(\.[0-9])?$/', $value)) {
        return false;
      }

      $value = number_format((float)$value, 1, '.', '');

      // only more than two years qualifies, and only whole or half years exist
      if ((float)$value <= 2.0 || !in_array(substr($value, -1), array('0', '5'))) {
        return false;
      }

      // the official template only holds two digits before the separator, one for half years
      if (substr($value, -1) == '0' && (float)$value > 99) {
        return false;
      }

      if (substr($value, -1) == '5' && (float)$value > 9.5) {
        return false;
      }

      return $value;
    }

    /**
     * The value as it appears in the label: whole years without a decimal part.
     */
    function duration_text($duration) {
      return (substr($duration, -1) == '0') ? (string)(int)$duration : $duration;
    }

    // ----------------------------------------------------------------- fits --

    /**
     * Measures the real text width with the prescribed font instead of counting characters.
     *
     * @return bool false when the value does not fit into its editable area
     */
    function fits($area_name, $text) {
      $areas = $this->areas();

      if (!isset($areas[$area_name])) {
        return false;
      }

      $area = $areas[$area_name];
      $font = $this->font_dir.$area['font'];

      if (!$this->is_available() || !is_file($font) || $area['font_size'] <= 0 || $area['max_width'] <= 0) {
        return false;
      }

      $box = @imagettfbbox($area['font_size'], 0, $font, (string)$text);

      if ($box === false) {
        return false;
      }

      return ((abs($box[2] - $box[0])) <= ($area['max_width'] - self::WIDTH_TOLERANCE));
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

      foreach (array_merge($this->templates(), $this->fonts()) as $file) {
        $stream .= is_file($file) ? hash_file('sha256', $file) : '-';
      }

      $hash = hash('sha256', $stream);

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

      if ($duration === false || trim((string)$manufacturer) === '' || trim((string)$model) === '') {
        return false;
      }

      if (!$this->is_ready()) {
        return false;
      }

      $hash = $this->garan_hash($manufacturer, $model, $duration);
      $names = array_keys($this->templates());

      $files = $this->archive->cache_read($hash, $names);

      if ($files === false) {
        $files = $this->render($manufacturer, $model, $duration);

        if ($files === false) {
          return false;
        }

        // a failing cache write must not stop the current request
        $this->archive->cache_write($hash, $files);
      }

      return array_merge(array('hash' => $hash), $files);
    }

    /**
     * Builds both variants from the official templates without touching the cache.
     *
     * @return mixed array of file name and content, false on a template mismatch
     */
    function render($manufacturer, $model, $duration) {
      $values = array(
        self::TOKEN_DURATION => $this->duration_text($duration),
        self::TOKEN_MANUFACTURER => (string)$manufacturer,
        self::TOKEN_MODEL => (string)$model,
      );

      $files = array();

      foreach ($this->templates() as $name => $file) {
        $svg = @file_get_contents($file);

        if ($svg === false || $svg === '') {
          $this->fail('template cannot be read: '.$file);
          return false;
        }

        foreach ($values as $token => $value) {
          $svg = $this->replace_token($svg, $token, $value, $name);

          if ($svg === false) {
            return false;
          }
        }

        $files[$name] = $svg;
      }

      return $files;
    }

    /**
     * Replaces one editable field. The token is only accepted as the complete text of an
     * element and has to appear exactly once, otherwise the template does not match the
     * expected official file and nothing is rendered.
     *
     * @return mixed the changed svg, false when the token does not appear exactly once
     */
    function replace_token($svg, $token, $value, $template) {
      $pattern = '/>(\s*)'.preg_quote($token, '/').'(\s*)</';
      $count = preg_match_all($pattern, $svg);

      if ($count !== 1) {
        $this->fail('token "'.$token.'" appears '.(int)$count.' times in '.$template.', expected exactly once');
        return false;
      }

      // product data must never be able to inject own svg or html
      $replacement = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');

      // a callback keeps $ and \ inside the value from being read as a back reference
      return preg_replace_callback($pattern, function ($match) use ($replacement) {
        return '>'.$match[1].$replacement.$match[2].'<';
      }, $svg, 1);
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
