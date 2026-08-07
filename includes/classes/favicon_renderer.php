<?php
/* -----------------------------------------------------------------------------------------
   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

final class FaviconRenderer
{
  // Generated metadata is refreshed after one day.
  private const METADATA_REFRESH_INTERVAL_SECONDS = 86400; // 24 Hours

  private string $catalog_directory;
  private string $encoded_title;
  private Closure $link_generator;

  public function __construct(string $catalog_directory, string $encoded_title, callable $link_generator)
  {
    $this->catalog_directory = rtrim(str_replace('\\', '/', $catalog_directory), '/').'/';
    $this->encoded_title = $encoded_title;
    $this->link_generator = Closure::fromCallable($link_generator);
  }

  public static function fromGlobals(string $request_type): self
  {
    return new self(
      DIR_FS_CATALOG,
      encode_htmlspecialchars(TITLE),
      static fn (string $path): string => xtc_href_link($path, '', $request_type, false)
    );
  }

  public function render(): string
  {
    if (Template::findPath('favicons/') === null) {
      return $this->renderFallbackFavicon();
    }

    $groups = $this->groupEffectiveFiles();

    return $this->renderFavicons($groups['favicon'])
      .$this->renderAppleTouchIcons($groups['apple_touch_icon'])
      .$this->renderSafariPinnedTabs($groups['safari_pinned_tab'])
      .$this->renderWindowsTiles($groups['mstile'])
      .$this->renderWebAppManifest($groups['android_touch_icon']);
  }

  private function renderFallbackFavicon(): string
  {
    if (Template::findPath('favicon.ico') === null) {
      return '';
    }

    return '<link rel="shortcut icon" href="'.$this->link('templates/'.Template::resolve('favicon.ico')).'" />'."\n";
  }

  private function groupEffectiveFiles(): array
  {
    $groups = [
      'favicon' => [],
      'apple_touch_icon' => [],
      'safari_pinned_tab' => [],
      'mstile' => [],
      'android_touch_icon' => [],
    ];

    foreach (Template::files('favicons/') as $path) {
      $name = basename($path);
      $group = $this->groupFor($name);
      if ($group === null) {
        continue;
      }

      $groups[$group][] = [
        'name' => $name,
        'extension' => pathinfo($path, PATHINFO_EXTENSION),
        'url' => $this->link('templates/'.Template::resolve('favicons/'.$name)),
      ];
    }

    return $groups;
  }

  private function groupFor(string $name): ?string
  {
    if (str_starts_with($name, 'favicon')) {
      return 'favicon';
    }
    if (str_starts_with($name, 'apple-touch-icon')) {
      return 'apple_touch_icon';
    }
    if (str_starts_with($name, 'safari-pinned-tab')) {
      return 'safari_pinned_tab';
    }
    if (str_starts_with($name, 'mstile')) {
      return 'mstile';
    }
    if (str_starts_with($name, 'android-chrome') || str_starts_with($name, 'web-app-manifest')) {
      return 'android_touch_icon';
    }

    return null;
  }

  private function renderFavicons(array $favicons): string
  {
    $markup = '';
    foreach ($favicons as $favicon) {
      preg_match('/(\d+)x(\d+)/', $favicon['name'], $match);
      if ($favicon['extension'] === 'ico') {
        $markup .= '<link rel="shortcut icon" href="'.$favicon['url'].'" />'."\n";
        continue;
      }

      $type = 'image/'.$favicon['extension'].($favicon['extension'] === 'svg' ? '+xml' : '');
      $sizes = isset($match[0]) && $match[0] !== '' ? ' sizes="'.$match[0].'"' : '';
      $markup .= '<link rel="icon" type="'.$type.'"'.$sizes.' href="'.$favicon['url'].'" />'."\n";
    }

    return $markup;
  }

  private function renderAppleTouchIcons(array $icons): string
  {
    $markup = '';
    foreach ($icons as $icon) {
      preg_match('/(\d+)x(\d+)/', $icon['name'], $match);
      $sizes = isset($match[0]) && $match[0] !== '' ? ' sizes="'.$match[0].'"' : '';
      $markup .= '<link rel="apple-touch-icon"'.$sizes.' href="'.$icon['url'].'" />'."\n";
    }
    if (count($icons) > 0) {
      $markup .= '<meta name="apple-mobile-web-app-title" content="'.$this->encoded_title.'" />'."\n";
    }

    return $markup;
  }

  private function renderSafariPinnedTabs(array $icons): string
  {
    $markup = '';
    foreach ($icons as $icon) {
      preg_match('/(\d+)x(\d+)/', $icon['name'], $match);
      $sizes = isset($match[0]) && $match[0] !== '' ? ' sizes="'.$match[0].'"' : '';
      $markup .= '<link rel="mask-icon"'.$sizes.' href="'.$icon['url'].'" color="#888888" />'."\n";
    }

    return $markup;
  }

  private function renderWindowsTiles(array $tiles): string
  {
    if (count($tiles) === 0) {
      return '';
    }

    $browserconfig = '<?xml version="1.0" encoding="utf-8"?><browserconfig><msapplication><tile>';
    foreach ($tiles as $tile) {
      preg_match('/(\d+)x(\d+)/', $tile['name'], $match);
      if (!isset($match[0]) || $match[0] === '') {
        continue;
      }

      $element = $match[1] > $match[2] ? 'wide' : 'square';
      $browserconfig .= '<'.$element.$match[0].'logo src="'.$tile['url'].'"/>';
    }
    $browserconfig .= '<TileColor>#ffffff</TileColor></tile></msapplication></browserconfig>';

    $relative_path = $this->writeMetadata('favicons/browserconfig.xml', $browserconfig);
    $markup = '<meta name="msapplication-TileColor" content="#ffffff" />'."\n";
    $markup .= '<meta name="theme-color" content="#ffffff" />'."\n";
    if ($relative_path !== null) {
      $markup .= '<meta name="msapplication-config" content="'.$this->link($relative_path).'" />'."\n";
    }

    return $markup;
  }

  private function renderWebAppManifest(array $icons): string
  {
    if (count($icons) === 0) {
      return '';
    }

    $manifest = [
      'name' => $this->encoded_title,
      'short_name' => $this->encoded_title,
      'icons' => [],
      'theme_color' => '#ffffff',
      'background_color' => '#ffffff',
      'display' => 'standalone',
    ];

    foreach ($icons as $icon) {
      preg_match('/(\d+)x(\d+)/', $icon['name'], $match);
      if (!isset($match[0]) || $match[0] === '') {
        continue;
      }

      $manifest['icons'][] = [
        'src' => $icon['url'],
        'sizes' => $match[0],
        'type' => 'image/'.$icon['extension'],
      ];
    }

    if (count($manifest['icons']) === 0) {
      return '';
    }

    $contents = json_encode($manifest);
    if ($contents === false) {
      return '';
    }

    $relative_path = $this->writeMetadata('favicons/site.webmanifest', $contents);

    return $relative_path === null
      ? ''
      : '<link rel="manifest" href="'.$this->link($relative_path).'" />'."\n";
  }

  private function writeMetadata(string $logical_name, string $contents): ?string
  {
    if (Template::findPath($logical_name) === null) {
      return null;
    }

    $active_template = Template::chain()[0];
    $relative_path = 'templates/'.$active_template.'/'.$logical_name;
    $file_path = $this->catalog_directory.$relative_path;
    if (!$this->ensureDirectory(dirname($file_path))) {
      return null;
    }

    if ($this->needsRefresh($file_path) && file_put_contents($file_path, $contents, LOCK_EX) === false) {
      return null;
    }

    return is_file($file_path) ? $relative_path : null;
  }

  private function ensureDirectory(string $directory): bool
  {
    return is_dir($directory)
      || mkdir($directory, 0777, true)
      || is_dir($directory);
  }

  private function needsRefresh(string $file_path): bool
  {
    if (!is_file($file_path)) {
      return is_writable(dirname($file_path));
    }
    if (!is_writable($file_path)) {
      return false;
    }

    $modified_at = filemtime($file_path);

    return $modified_at !== false
      && ($modified_at < time() - self::METADATA_REFRESH_INTERVAL_SECONDS || filesize($file_path) === 0);
  }

  private function link(string $path): string
  {
    return ($this->link_generator)($path);
  }
}
