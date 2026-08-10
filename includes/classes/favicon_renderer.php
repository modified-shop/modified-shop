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

    private const RESOURCE_GROUPS = [
        'favicon' => [
            'prefixes' => ['favicon'],
            'renderer' => 'renderFavicons',
        ],
        'apple_touch_icon' => [
            'prefixes' => ['apple-touch-icon'],
            'renderer' => 'renderAppleTouchIcons',
        ],
        'safari_pinned_tab' => [
            'prefixes' => ['safari-pinned-tab'],
            'renderer' => 'renderSafariPinnedTabs',
        ],
        'mstile' => [
            'prefixes' => ['mstile'],
            'renderer' => 'renderWindowsTiles',
        ],
        'android_touch_icon' => [
            'prefixes' => ['android-chrome', 'web-app-manifest'],
            'renderer' => 'renderWebAppManifest',
        ],
    ];

    private string $catalog_directory;
    private string $encoded_title;
    private Closure $link_generator;

    public function __construct(string $catalog_directory, string $encoded_title, callable $link_generator)
    {
        $this->catalog_directory = rtrim(str_replace('\\', '/', $catalog_directory), '/') . '/';
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
        $markup = '';

        foreach (self::RESOURCE_GROUPS as $group_name => $definition) {
            $renderer = $definition['renderer'];
            $markup .= $this->{$renderer}($groups[$group_name]);
        }

        return $markup;
    }

    // Renderers

    private function renderFallbackFavicon(): string
    {
        if (Template::findPath('favicon.ico') === null) {
            return '';
        }

        return '<link rel="shortcut icon" href="' . $this->link('templates/' . Template::resolve('favicon.ico')) . '" />' . "\n";
    }

    private function renderFavicons(array $favicons): string
    {
        $markup = '';
        foreach ($favicons as $favicon) {
            if ($favicon['extension'] === 'ico') {
                $markup .= '<link rel="shortcut icon" href="' . $favicon['url'] . '" />' . "\n";
                continue;
            }

            $type = 'image/' . $favicon['extension'];
            if ($favicon['extension'] === 'svg') {
                $type .= '+xml';
            }

            $sizes = $this->sizeAttribute($favicon['name']);
            $markup .= '<link rel="icon" type="' . $type . '"' . $sizes . ' href="' . $favicon['url'] . '" />' . "\n";
        }

        return $markup;
    }

    private function renderAppleTouchIcons(array $icons): string
    {
        $markup = '';
        foreach ($icons as $icon) {
            $sizes = $this->sizeAttribute($icon['name']);
            $markup .= '<link rel="apple-touch-icon"' . $sizes . ' href="' . $icon['url'] . '" />' . "\n";
        }
        if (count($icons) > 0) {
            $markup .= '<meta name="apple-mobile-web-app-title" content="' . $this->encoded_title . '" />' . "\n";
        }

        return $markup;
    }

    private function renderSafariPinnedTabs(array $icons): string
    {
        $markup = '';
        foreach ($icons as $icon) {
            $sizes = $this->sizeAttribute($icon['name']);
            $markup .= '<link rel="mask-icon"' . $sizes . ' href="' . $icon['url'] . '" color="#888888" />' . "\n";
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
            $element = $this->windowsTileElementName($tile['name']);
            if ($element === null) {
                continue;
            }

            $browserconfig .= '<' . $element . ' src="' . $tile['url'] . '"/>';
        }
        $browserconfig .= '<TileColor>#ffffff</TileColor></tile></msapplication></browserconfig>';

        $allow_inherited_fallback = $this->canUseInheritedMetadata(
            'favicons/browserconfig.xml',
            $tiles
        );
        $relative_path = $this->writeMetadata(
            'favicons/browserconfig.xml',
            $browserconfig,
            $allow_inherited_fallback
        );
        $markup = '<meta name="msapplication-TileColor" content="#ffffff" />' . "\n";
        $markup .= '<meta name="theme-color" content="#ffffff" />' . "\n";
        if ($relative_path !== null) {
            $markup .= '<meta name="msapplication-config" content="' . $this->link($relative_path) . '" />' . "\n";
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
            $dimensions = $this->dimensionsFromName($icon['name']);
            if ($dimensions === null) {
                continue;
            }

            $manifest['icons'][] = [
              'src' => $icon['url'],
              'sizes' => $dimensions['size'],
              'type' => 'image/' . $icon['extension'],
            ];
        }

        if (count($manifest['icons']) === 0) {
            return '';
        }

        $contents = json_encode($manifest);
        if ($contents === false) {
            return '';
        }

        $allow_inherited_fallback = $this->canUseInheritedMetadata(
            'favicons/site.webmanifest',
            $icons
        );
        $relative_path = $this->writeMetadata(
            'favicons/site.webmanifest',
            $contents,
            $allow_inherited_fallback
        );

        if ($relative_path === null) {
            return '';
        }

        return '<link rel="manifest" href="' . $this->link($relative_path) . '" />' . "\n";
    }

    // Resource discovery

    private function groupEffectiveFiles(): array
    {
        $groups = array_fill_keys(array_keys(self::RESOURCE_GROUPS), []);

        foreach (Template::files('favicons/') as $path) {
            $name = basename($path);
            $group = $this->groupFor($name);
            if ($group === null) {
                continue;
            }

            $resolved_template_path = Template::resolve('favicons/' . $name);

            $groups[$group][] = [
                'name' => $name,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'url' => $this->link('templates/' . $resolved_template_path),
                'source_template' => $this->sourceTemplateFromResolvedPath($resolved_template_path),
            ];
        }

        return $groups;
    }

    private function groupFor(string $name): ?string
    {
        foreach (self::RESOURCE_GROUPS as $group_name => $definition) {
            foreach ($definition['prefixes'] as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    return $group_name;
                }
            }
        }

        return null;
    }

    /**
     * Checks whether inherited metadata can describe all effective resource files.
     */
    private function canUseInheritedMetadata(string $logical_name, array $files): bool
    {
        if (Template::findPath($logical_name) === null) {
            return false;
        }

        $chain_positions = array_flip(Template::chain());
        $metadata_source = $this->sourceTemplateFromResolvedPath(Template::resolve($logical_name));
        if (!isset($chain_positions[$metadata_source])) {
            return false;
        }

        $metadata_position = $chain_positions[$metadata_source];
        if ($metadata_position === 0) {
            return false;
        }

        foreach ($files as $file) {
            $file_source = $file['source_template'];
            if (!isset($chain_positions[$file_source])) {
                return false;
            }
            if ($chain_positions[$file_source] < $metadata_position) {
                return false;
            }
        }

        return true;
    }

    private function sourceTemplateFromResolvedPath(string $resolved_path): string
    {
        $parts = explode('/', $resolved_path, 2);

        return $parts[0];
    }

    // Metadata persistence

    private function writeMetadata(string $logical_name, string $contents, bool $allow_inherited_fallback): ?string
    {
        if (Template::findPath($logical_name) === null) {
            return null;
        }

        $resolved_relative_path = 'templates/' . Template::resolve($logical_name);
        $active_template = Template::chain()[0];
        $relative_path = 'templates/' . $active_template . '/' . $logical_name;
        $file_path = $this->catalog_directory . $relative_path;
        if (!$this->ensureDirectory(dirname($file_path))) {
            if ($allow_inherited_fallback) {
                return $resolved_relative_path;
            }

            return null;
        }

        if ($this->needsRefresh($file_path)) {
            $bytes_written = @file_put_contents($file_path, $contents, LOCK_EX);
            if ($bytes_written === false) {
                if (is_file($file_path)) {
                    return $relative_path;
                }
                if ($allow_inherited_fallback) {
                    return $resolved_relative_path;
                }

                return null;
            }
        }

        if (is_file($file_path)) {
            return $relative_path;
        }
        if ($allow_inherited_fallback) {
            return $resolved_relative_path;
        }

        return null;
    }

    private function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }
        if (@mkdir($directory, 0777, true)) {
            return true;
        }

        return is_dir($directory);
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
        if ($modified_at === false) {
            return false;
        }
        if (filesize($file_path) === 0) {
            return true;
        }

        return $modified_at < time() - self::METADATA_REFRESH_INTERVAL_SECONDS;
    }

    // File name metadata

    private function sizeAttribute(string $name): string
    {
        $dimensions = $this->dimensionsFromName($name);
        if ($dimensions === null) {
            return '';
        }

        return ' sizes="' . $dimensions['size'] . '"';
    }

    private function windowsTileElementName(string $name): ?string
    {
        $dimensions = $this->dimensionsFromName($name);
        if ($dimensions === null) {
            return null;
        }

        $element = 'square';
        if ($dimensions['width'] > $dimensions['height']) {
            $element = 'wide';
        }

        return $element . $dimensions['size'] . 'logo';
    }

    /**
     * @return array{size: string, width: int, height: int}|null
     */
    private function dimensionsFromName(string $name): ?array
    {
        $matches = [];
        if (preg_match('/(\d+)x(\d+)/', $name, $matches) !== 1) {
            return null;
        }

        return [
            'size' => $matches[0],
            'width' => (int) $matches[1],
            'height' => (int) $matches[2],
        ];
    }

    // Link generation

    private function link(string $path): string
    {
        return ($this->link_generator)($path);
    }
}
