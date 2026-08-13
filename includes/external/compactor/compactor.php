<?php
/**
 * @author Oliver Lillie (aka buggedcom) <publicmail@buggedcom.co.uk>
 *
 * @license BSD
 * @copyright Copyright (c) 2008 Oliver Lillie <http://www.buggedcom.co.uk>
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
 * is furnished to do so, subject to the following conditions:  The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @name Compactor
 * @version 0.7.0
 * @abstract Compacts HTML and acts as the central facade for CSS and JavaScript minification.
 */

class Compactor
{
    /**
     * Files or source strings to combine and minify.
     *
     * @var array
     */
    public $data = array();

    /**
     * @var array
     */
    private $_options = array(
        'line_break' => PHP_EOL,
        'preserved_tags' => array('head', 'textarea', 'pre', 'script', 'style', 'code'),
        'preserved_boundry' => '@@PRESERVEDTAG@@',
        'strip_comments' => true,
        'keep_conditional_comments' => true,
        'compress_horizontal' => true,
        'compress_vertical' => true,
        'compress_scripts' => false,
        'compress_css' => true,
        'script_line_breaks' => true,
        'force_script_line_breaks' => false,
        // A configured callback fully replaces minifyJavascript()'s own
        // compression for inline <script> content, matching the pre-refactor
        // behavior that older templates and modules may still rely on.
        'script_compression_callback' => false,
        'script_compression_callback_args' => array(),
    );

    /**
     * Options that older releases accepted but this facade can no longer
     * honor because HTML comment stripping is not part of the vendored
     * minifier. setOption() must not throw for it: existing modules or
     * custom templates still constructing a Compactor with e.g.
     * 'strip_php_comments' (the previous combine_files.inc.php did exactly
     * that) would otherwise fail with a fatal error on every page. A
     * non-fatal E_USER_DEPRECATED notice documents the behavior change
     * without breaking the request.
     *
     * @var string[]
     */
    private $_removed_options = array(
        'strip_php_comments',
    );

    /**
     * @var array
     */
    private $_preserved_blocks = array();

    /**
     * @var string
     */
    private $_preserved_boundary = '';

    /**
     * @var array
     */
    private $_script_markers = array();

    /**
     * [tag names, class names, id names] carrying a "white-space: pre*"
     * declaration in an embedded <style> block of the document currently
     * being squeezed, lowercased. Populated per squeeze() call; a linked,
     * external stylesheet cannot be resolved this way.
     *
     * @var array{0: string[], 1: string[], 2: string[]}
     */
    private $_whitespace_sensitive_selectors = array(array(), array(), array());

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOption($options);
    }

    /**
     * @param array|string $varname
     * @param mixed $varvalue
     */
    public function setOption($varname, $varvalue = null)
    {
        if (is_array($varname)) {
            foreach ($varname as $name => $value) {
                $this->_warnRemovedOption($name);
                if (array_key_exists($name, $this->_options)) {
                    $this->_options[$name] = $value;
                }
            }

            return;
        }

        $this->_warnRemovedOption($varname);
        if (array_key_exists($varname, $this->_options)) {
            $this->_options[$varname] = $varvalue;
        }
    }

    /**
     * @param string $name
     */
    private function _warnRemovedOption($name)
    {
        if (in_array($name, $this->_removed_options, true)) {
            trigger_error(
                'The Compactor option "' . $name . '" was removed and is no longer honored. '
                . 'JavaScript and CSS compression is now delegated to the vendored minifier.',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * Return the newest modification time of the facade and its vendored
     * minifier implementation for bundle cache invalidation.
     *
     * @return int
     */
    public static function getImplementationTime()
    {
        static $implementation_time;

        if ($implementation_time !== null) {
            return $implementation_time;
        }

        $implementation_time = (int)@filemtime(__FILE__);
        $vendor_directory = dirname(__DIR__).'/MatthiasMullie';
        if (!is_dir($vendor_directory)) {
            return $implementation_time;
        }
        $implementation_time = max($implementation_time, (int)@filemtime($vendor_directory));

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($vendor_directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $file) {
                $implementation_time = max($implementation_time, $file->getMTime());
            }
        } catch (Throwable $exception) {
            // The facade time still invalidates bundles when the vendor tree cannot be inspected.
        }

        return $implementation_time;
    }

    /**
     * Add a file or source string for CSS or JavaScript minification.
     *
     * @param string $data
     * @return $this
     */
    public function add($data)
    {
        $this->data[] = $data;

        return $this;
    }

    /**
     * Combine and minify the previously added CSS or JavaScript sources.
     *
     * @param string $path
     * @return bool
     */
    public function save($path)
    {
        try {
            $minifier = $this->_createMinifier((bool)$this->_options['compress_css']);
            foreach ($this->data as $data) {
                $minifier->add($data);
            }

            return $this->_saveAtomically($path, $minifier->execute($path));
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * Minify JavaScript source. The original source is returned on failure.
     *
     * A configured 'script_compression_callback' takes over entirely, exactly
     * as it did before JavaScript compression was delegated to the vendored
     * minifier: the callback's return value is used as-is, unguarded, so a
     * configured callback that throws behaves the same as it always did.
     *
     * @param string $javascript
     * @return string
     */
    public function minifyJavascript($javascript)
    {
        if ($this->_options['script_compression_callback'] !== false) {
            $callback_args = $this->_options['script_compression_callback_args'];
            if (!is_array($callback_args)) {
                $callback_args = array($callback_args);
            }
            array_unshift($callback_args, $javascript);

            return call_user_func_array($this->_options['script_compression_callback'], $callback_args);
        }

        try {
            // Rendered script content must never be sniffed as a local file path.
            $minifier = $this->_createMinifier(false, false);

            return $minifier->add($javascript)->minify();
        } catch (Throwable $exception) {
            return $javascript;
        }
    }

    /**
     * Minify CSS source. The original source is returned on failure.
     *
     * @param string $css
     * @return string
     */
    public function minifyCss($css)
    {
        try {
            // Rendered style content must never be sniffed as a local file path.
            $minifier = $this->_createMinifier(true, false);

            return $minifier->add($css)->minify();
        } catch (Throwable $exception) {
            return $css;
        }
    }

    /**
     * Compact HTML without changing formatting-sensitive blocks.
     *
     * @param string|null $html
     * @return string
     */
    public function squeeze($html = null)
    {
        $html = is_string($html) ? $html : '';
        $this->_preserved_blocks = array();
        $this->_preserved_boundary = '';
        $this->_script_markers = array();

        $html = $this->_unifyLineBreaks($html);
        if ($this->_options['compress_scripts'] || $this->_options['compress_css']) {
            $html = $this->_compressScriptAndStyleTags($html);
        }

        $compress_whitespace = $this->_options['compress_horizontal'] || $this->_options['compress_vertical'];
        if ($compress_whitespace) {
            // Must run before _extractPreservedBlocks() replaces <style>
            // content with a marker below.
            $this->_whitespace_sensitive_selectors = $this->_collectStyleBasedWhitespaceSelectors($html);
        }

        $html = $this->_extractPreservedBlocks($html);
        if ($compress_whitespace) {
            $html = $this->_extractWhitespaceSensitiveBlocks($html);
        }
        if ($this->_options['strip_comments']) {
            $html = $this->_stripHTMLComments($html);
        }
        if ($compress_whitespace) {
            $html = $this->_compressTagWhitespace(
                $html,
                $this->_options['compress_horizontal'],
                $this->_options['compress_vertical']
            );
            $html = $this->_compressTextWhitespace(
                $html,
                $this->_options['compress_horizontal'],
                $this->_options['compress_vertical']
            );
        }

        $html = $this->_reinstatePreservedBlocks($html);
        $this->_preserved_blocks = array();
        $this->_preserved_boundary = '';
        $this->_script_markers = array();
        $this->_whitespace_sensitive_selectors = array(array(), array(), array());

        return $html;
    }

    /**
     * @param string $html
     * @return string
     */
    private function _stripHTMLComments($html)
    {
        $keep_conditionals = ($this->_options['keep_conditional_comments']
            && isset($_SERVER['HTTP_USER_AGENT'])
            && preg_match('/msie\s(.*).*(win)/i', $_SERVER['HTTP_USER_AGENT'])
        );

        return preg_replace_callback(
            '#<(?:/?[A-Za-z]|![A-Za-z]|\?)(?:[^>"\']|"[^"]*"|\'[^\']*\')*>|<!--[\s\S]*?-->#',
            function ($matches) use ($keep_conditionals) {
                if (substr($matches[0], 0, 4) != '<!--') {
                    return $matches[0];
                }

                if ($keep_conditionals && preg_match('/^<!--\[if\b[\s\S]*<!\[endif\]-->$/i', $matches[0])) {
                    return $matches[0];
                }

                return '';
            },
            $html
        );
    }

    /**
     * @param string $html
     * @return string
     */
    private function _extractPreservedBlocks($html)
    {
        // Set up unconditionally: _extractWhitespaceSensitiveBlocks() relies
        // on this boundary too, even when preserved_tags is empty.
        $boundary = (is_string($this->_options['preserved_boundry']) && $this->_options['preserved_boundry'] != '')
            ? $this->_options['preserved_boundry']
            : '@@PRESERVEDTAG@@';
        while (strpos($html, $boundary) !== false) {
            $boundary .= '_';
        }
        $this->_preserved_boundary = $boundary;

        $tags = array_map(function ($tag) {
            return preg_quote($tag, '!');
        }, $this->_options['preserved_tags']);
        if (count($tags) == 0) {
            return $html;
        }

        $head_key = array_search('head', array_map('strtolower', $tags));
        if ($head_key !== false) {
            $html = $this->_extractHeadBlock($html);
            unset($tags[$head_key]);
        }
        if (count($tags) == 0) {
            return $html;
        }

        $pattern = '!([ \\t\\r\\n]*)(<(?P<preserved_tag>'.implode('|', $tags).')(?=[\\s/>])(?:[^>"\']|"[^"]*"|\'[^\']*\')*>.*?</(?P=preserved_tag)\\s*>)!is';

        return preg_replace_callback($pattern, function ($matches) {
            $marker = $this->_preserved_boundary.count($this->_preserved_blocks).'@@';
            $this->_preserved_blocks[$marker] = $matches[2];
            if (strtolower($matches['preserved_tag']) == 'script') {
                $this->_script_markers[$marker] = ($matches[1] != '');
            }

            return $matches[1].$marker;
        }, $html);
    }

    /**
     * Preserve the complete head element. Its closing tag must be found with
     * awareness of HTML comments, tag attributes and raw-text elements because
     * those can legally contain the literal string "</head>".
     *
     * @param string $html
     * @return string
     */
    private function _extractHeadBlock($html)
    {
        $head_start = false;
        $offset = 0;
        $length = strlen($html);
        $raw_text_tags = array('iframe', 'noembed', 'noframes', 'noscript', 'script', 'style', 'textarea', 'title', 'xmp');

        while ($offset < $length && ($tag_start = strpos($html, '<', $offset)) !== false) {
            if (substr($html, $tag_start, 4) === '<!--') {
                $comment_end = strpos($html, '-->', $tag_start + 4);
                if ($comment_end === false) {
                    break;
                }
                $offset = $comment_end + 3;
                continue;
            }

            if (!preg_match(
                '#\\G<(?:/?[A-Za-z]|![A-Za-z]|\\?)(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#s',
                $html,
                $tag_match,
                0,
                $tag_start
            )) {
                $offset = $tag_start + 1;
                continue;
            }

            $tag = $tag_match[0];
            $tag_end = $tag_start + strlen($tag);
            if (!preg_match('#^<(/?)([A-Za-z][A-Za-z0-9:-]*)#', $tag, $name_match)) {
                $offset = $tag_end;
                continue;
            }

            $closing = ($name_match[1] === '/');
            $name = strtolower($name_match[2]);
            if ($head_start === false) {
                if (!$closing && $name === 'head') {
                    $head_start = $tag_start;
                }
                $offset = $tag_end;
                continue;
            }

            if ($closing && $name === 'head') {
                $marker = $this->_preserved_boundary.count($this->_preserved_blocks).'@@';
                $this->_preserved_blocks[$marker] = substr($html, $head_start, $tag_end - $head_start);

                return substr($html, 0, $head_start).$marker.substr($html, $tag_end);
            }

            if (!$closing && in_array($name, $raw_text_tags)) {
                $closing_pattern = '#</'.preg_quote($name, '#')
                    .'(?=[\\s/>])(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#is';
                if (!preg_match($closing_pattern, $html, $closing_match, PREG_OFFSET_CAPTURE, $tag_end)) {
                    break;
                }
                $offset = $closing_match[0][1] + strlen($closing_match[0][0]);
                continue;
            }

            $offset = $tag_end;
        }

        return $html;
    }

    /**
     * @var string[]
     */
    private $_void_tags = array(
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'source', 'track', 'wbr',
    );

    /**
     * Find simple selectors ("tag", ".class", "#id" - no combinators or
     * pseudo-classes) declaring "white-space: pre*" in an embedded <style>
     * block, so elements they match can be protected the same way as an
     * inline style. A later rule for the same selector setting
     * "normal"/"nowrap" removes it again, since it's common for a reset to
     * follow a component's own style block later in the document; this is
     * not full cascade/specificity resolution.
     *
     * @param string $html
     * @return array{0: string[], 1: string[], 2: string[]} [tags, classes, ids]
     */
    private function _collectStyleBasedWhitespaceSelectors($html)
    {
        $tags = array();
        $classes = array();
        $ids = array();

        if (!preg_match_all('#<style\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>(.*?)</style\s*>#is', $html, $style_matches)) {
            return array($tags, $classes, $ids);
        }

        foreach ($style_matches[1] as $css) {
            if (!preg_match_all('#([^{}]+)\{([^{}]*)\}#s', $css, $rule_matches, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($rule_matches as $rule) {
                $is_sensitive = (bool)preg_match('/white-space\s*:\s*(?:pre|pre-wrap|pre-line|break-spaces)\b/i', $rule[2]);
                $is_reset = (bool)preg_match('/white-space\s*:\s*(?:normal|nowrap)\b/i', $rule[2]);
                if (!$is_sensitive && !$is_reset) {
                    continue;
                }

                foreach (explode(',', $rule[1]) as $selector) {
                    $selector = strtolower(trim($selector));
                    if (!preg_match('/^([.#]?)([a-z][a-z0-9_-]*)$/', $selector, $selector_match)) {
                        continue;
                    }

                    $target = &$tags;
                    if ($selector_match[1] === '.') {
                        $target = &$classes;
                    } elseif ($selector_match[1] === '#') {
                        $target = &$ids;
                    }

                    if ($is_sensitive) {
                        $target[$selector_match[2]] = true;
                    } else {
                        unset($target[$selector_match[2]]);
                    }
                    unset($target);
                }
            }
        }

        return array(array_keys($tags), array_keys($classes), array_keys($ids));
    }

    /**
     * @param string $name Lowercase tag name
     * @param string $attributes Raw attribute text of an opening tag
     * @return bool
     */
    private function _isWhitespaceSensitiveTag($name, $attributes)
    {
        if (preg_match('/\bxml:space\s*=\s*(["\'])preserve\1/i', $attributes)) {
            return true;
        }

        if (preg_match('/\bstyle\s*=\s*(["\'])(.*?)\1/is', $attributes, $style_match)
            && preg_match('/(?:^|;)\s*white-space\s*:\s*(?:pre|pre-wrap|pre-line|break-spaces)\b/i', $style_match[2])
        ) {
            return true;
        }

        list($sensitive_tags, $sensitive_classes, $sensitive_ids) = $this->_whitespace_sensitive_selectors;
        if ($sensitive_tags && in_array($name, $sensitive_tags, true)) {
            return true;
        }

        if ($sensitive_classes && preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/is', $attributes, $class_match)) {
            foreach (preg_split('/\s+/', trim($class_match[2])) as $class) {
                if ($class !== '' && in_array(strtolower($class), $sensitive_classes, true)) {
                    return true;
                }
            }
        }

        if ($sensitive_ids && preg_match('/\bid\s*=\s*(["\'])(.*?)\1/is', $attributes, $id_match)) {
            return in_array(strtolower(trim($id_match[2])), $sensitive_ids, true);
        }

        return false;
    }

    /**
     * Unlike preserved_tags, a whitespace-sensitive element can be any tag
     * and can nest arbitrarily, so its closing tag is found by tracking
     * depth rather than a simple non-greedy match.
     *
     * @param string $html
     * @return string
     */
    private function _extractWhitespaceSensitiveBlocks($html)
    {
        if ($this->_preserved_boundary === '') {
            return $html;
        }

        $raw_text_tags = array('iframe', 'noembed', 'noframes', 'noscript', 'script', 'style', 'textarea', 'title', 'xmp');
        $offset = 0;
        $length = strlen($html);
        $result = '';

        while ($offset < $length && ($tag_start = strpos($html, '<', $offset)) !== false) {
            $result .= substr($html, $offset, $tag_start - $offset);

            if (substr($html, $tag_start, 4) === '<!--') {
                $comment_end = strpos($html, '-->', $tag_start + 4);
                if ($comment_end === false) {
                    $result .= substr($html, $tag_start);
                    $offset = $length;
                    break;
                }
                $result .= substr($html, $tag_start, $comment_end + 3 - $tag_start);
                $offset = $comment_end + 3;
                continue;
            }

            if (!preg_match(
                '#\\G<(?P<closing>/?)(?P<name>[A-Za-z][A-Za-z0-9:-]*)(?P<attrs>(?:[^>"\']|"[^"]*"|\'[^\']*\')*)>#s',
                $html,
                $tag_match,
                0,
                $tag_start
            )) {
                $result .= substr($html, $tag_start, 1);
                $offset = $tag_start + 1;
                continue;
            }

            $tag = $tag_match[0];
            $tag_end = $tag_start + strlen($tag);
            $name = strtolower($tag_match['name']);
            $self_closing = (substr($tag, -2, 1) === '/');

            if (
                $tag_match['closing'] === ''
                && !$self_closing
                && !in_array($name, $this->_void_tags, true)
                && $this->_isWhitespaceSensitiveTag($name, $tag_match['attrs'])
            ) {
                $end = $this->_findMatchingClosingTag($html, $tag_end, $name, $raw_text_tags);
                if ($end !== false) {
                    $marker = $this->_preserved_boundary.count($this->_preserved_blocks).'@@';
                    $this->_preserved_blocks[$marker] = substr($html, $tag_start, $end - $tag_start);
                    $result .= $marker;
                    $offset = $end;
                    continue;
                }
            }

            $result .= $tag;
            $offset = $tag_end;
        }

        $result .= substr($html, $offset);

        return $result;
    }

    /**
     * @param string $html
     * @param int $offset Position right after the opening tag
     * @param string $name Lowercase tag name to match
     * @param string[] $raw_text_tags
     * @return int|false Position right after the matching closing tag, or false if none is found
     */
    private function _findMatchingClosingTag($html, $offset, $name, $raw_text_tags)
    {
        $length = strlen($html);
        $depth = 1;

        while ($offset < $length && ($tag_start = strpos($html, '<', $offset)) !== false) {
            if (substr($html, $tag_start, 4) === '<!--') {
                $comment_end = strpos($html, '-->', $tag_start + 4);
                if ($comment_end === false) {
                    return false;
                }
                $offset = $comment_end + 3;
                continue;
            }

            if (!preg_match(
                '#\\G<(?P<closing>/?)(?P<name>[A-Za-z][A-Za-z0-9:-]*)(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#s',
                $html,
                $tag_match,
                0,
                $tag_start
            )) {
                $offset = $tag_start + 1;
                continue;
            }

            $tag_end = $tag_start + strlen($tag_match[0]);
            $tag_name = strtolower($tag_match['name']);
            $closing = ($tag_match['closing'] === '/');
            $self_closing = (substr($tag_match[0], -2, 1) === '/');

            if ($tag_name === $name) {
                if ($closing) {
                    $depth--;
                    if ($depth === 0) {
                        return $tag_end;
                    }
                } elseif (!$self_closing && !in_array($tag_name, $this->_void_tags, true)) {
                    $depth++;
                }
                $offset = $tag_end;
                continue;
            }

            if (!$closing && in_array($tag_name, $raw_text_tags, true)) {
                $closing_pattern = '#</'.preg_quote($tag_name, '#')
                    .'(?=[\\s/>])(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#is';
                if (!preg_match($closing_pattern, $html, $closing_match, PREG_OFFSET_CAPTURE, $tag_end)) {
                    return false;
                }
                $offset = $closing_match[0][1] + strlen($closing_match[0][0]);
                continue;
            }

            $offset = $tag_end;
        }

        return false;
    }

    /**
     * @param string $html
     * @return string
     */
    private function _reinstatePreservedBlocks($html)
    {
        if ($this->_options['script_line_breaks']) {
            $html = $this->_addScriptLineBreaks($html);
        }

        // A whitespace-sensitive block extracted after tag-based blocks can
        // itself contain one of those markers. strtr() substitutes all keys
        // in one simultaneous pass, so it won't resolve a marker that only
        // appears inside another marker's own stored value; loop until a
        // pass makes no further change.
        do {
            $before = $html;
            $html = strtr($html, $this->_preserved_blocks);
        } while ($html !== $before);

        return $html;
    }

    /**
     * Place every opening script tag at the beginning of a new line. Script
     * contents are still represented by markers and cannot be modified here.
     *
     * @param string $html
     * @return string
     */
    private function _addScriptLineBreaks($html)
    {
        $line_break = $this->_options['line_break'];
        $line_break_length = strlen($line_break);
        if ($line_break_length == 0) {
            return $html;
        }

        foreach ($this->_script_markers as $marker => $has_leading_whitespace) {
            if (!$has_leading_whitespace && !$this->_options['force_script_line_breaks']) {
                continue;
            }

            $position = strpos($html, $marker);
            if ($position === false || $position == 0) {
                continue;
            }

            $before = rtrim(substr($html, 0, $position), " \t");
            if (substr($before, -$line_break_length) !== $line_break) {
                $before .= $line_break;
            }
            $html = $before.substr($html, $position);
        }

        return $html;
    }

    /**
     * Collapse tabs, runs of spaces, and line breaks within tag markup
     * (outside of quoted attribute values).
     *
     * @param string $html
     * @param bool $compress_tabs
     * @param bool $compress_line_breaks
     * @return string
     */
    private function _compressTagWhitespace($html, $compress_tabs, $compress_line_breaks)
    {
        $line_break = $this->_options['line_break'];
        $line_break_length = strlen($line_break);

        return preg_replace_callback(
            '#<(?:/?[A-Za-z]|![A-Za-z]|\?)(?:[^>"\']|"[^"]*"|\'[^\']*\')*>#s',
            function ($matches) use ($compress_tabs, $compress_line_breaks, $line_break, $line_break_length) {
                $tag = $matches[0];
                $has_tabs = ($compress_tabs && strpos($tag, "\t") !== false);
                $has_multi_space = ($compress_tabs && strpos($tag, '  ') !== false);
                $has_line_breaks = ($compress_line_breaks
                    && $line_break_length > 0
                    && strpos($tag, $line_break) !== false
                );
                if (!$has_tabs && !$has_multi_space && !$has_line_breaks) {
                    return $tag;
                }

                $result = '';
                $quote = '';
                $length = strlen($tag);
                for ($i = 0; $i < $length; $i++) {
                    $character = $tag[$i];
                    if ($quote != '') {
                        $result .= $character;
                        if ($character == $quote) {
                            $quote = '';
                        }
                        continue;
                    }

                    if ($character == '"' || $character == "'") {
                        $quote = $character;
                        $result .= $character;
                        continue;
                    }

                    $is_line_break = ($compress_line_breaks
                        && $line_break_length > 0
                        && substr($tag, $i, $line_break_length) === $line_break
                    );
                    $is_multi_space = ($compress_tabs
                        && $character == ' '
                        && isset($tag[$i + 1])
                        && $tag[$i + 1] == ' '
                    );
                    if (($compress_tabs && $character == "\t") || $is_line_break || $is_multi_space) {
                        $ends_with_line_break = ($line_break_length > 0
                            && substr($result, -$line_break_length) === $line_break
                        );
                        if ($result != '' && substr($result, -1) != ' ' && !$ends_with_line_break) {
                            $result .= ' ';
                        }
                        if ($is_line_break) {
                            $i += $line_break_length - 1;
                        }
                        while ($i + 1 < $length && ($tag[$i + 1] == ' ' || $tag[$i + 1] == "\t")) {
                            $i++;
                        }
                        continue;
                    }

                    $result .= $character;
                }

                return $result;
            },
            $html
        );
    }

    /**
     * Tags that are always block-level in practice. A whitespace-only text
     * node touching such a tag on both sides carries no visual meaning and
     * can be dropped entirely, unlike whitespace next to an inline element
     * (e.g. <span>a</span> <span>b</span>), where the collapsed single space
     * is often the only remaining word separator and must be kept.
     *
     * @var string[]
     */
    private $_block_tags = array(
        'html', 'body', 'header', 'footer', 'nav', 'main', 'section', 'article', 'aside',
        'div', 'p', 'ul', 'ol', 'li', 'dl', 'dt', 'dd',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'colgroup',
        'form', 'fieldset', 'legend',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'address', 'figure', 'figcaption', 'details', 'summary', 'dialog', 'hr',
    );

    /**
     * Collapse runs of spaces and tabs, and line breaks, in text segments
     * without touching tag attributes. Whitespace is replaced by one space
     * instead of being removed, because it can separate adjacent inline
     * elements or words, unless the text node sits directly between two
     * block-level tags, where it can be dropped entirely.
     *
     * @param string $html
     * @param bool $compress_tabs
     * @param bool $compress_line_breaks
     * @return string
     */
    private function _compressTextWhitespace($html, $compress_tabs, $compress_line_breaks)
    {
        $parts = preg_split(
            '#(<(?:/?[A-Za-z]|![A-Za-z]|\?)(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)#s',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if ($parts === false) {
            return $html;
        }

        $line_break = preg_quote($this->_options['line_break'], '#');
        foreach ($parts as $index => $part) {
            // Delimiters are the tags; only process the text between them.
            if ($index % 2 != 0) {
                continue;
            }

            if (
                $part !== '' && trim($part) === ''
                && $this->_isBlockBoundaryTag($parts, $index - 1)
                && $this->_isBlockBoundaryTag($parts, $index + 1)
            ) {
                $parts[$index] = '';
                continue;
            }

            if ($compress_line_breaks && $line_break != '') {
                $part = preg_replace(
                    '#[ \t]*'.$line_break.'[ \t]*(?:'.$line_break.'[ \t]*)*#',
                    ' ',
                    $part
                );
            }
            // Runs of plain spaces/tabs on the same line are left alone: a
            // CSS rule reachable only through an external stylesheet,
            // compound selector, or !important can set white-space:pre on
            // an element this HTML-only pass has no way to see, and
            // collapsing them would silently change what renders. Only a
            // literal tab character - virtually never meaningful content -
            // is still removed.
            if ($compress_tabs) {
                $part = str_replace("\t", '', $part);
            }
            $parts[$index] = $part;
        }

        return implode('', $parts);
    }

    /**
     * @param string[] $parts
     * @param int $index
     * @return bool
     */
    private function _isBlockBoundaryTag($parts, $index)
    {
        if (!isset($parts[$index])) {
            return false;
        }

        if (!preg_match('#^<(/?)([A-Za-z][A-Za-z0-9:-]*)#', $parts[$index], $match)) {
            return false;
        }

        return in_array(strtolower($match[2]), $this->_block_tags, true);
    }

    /**
     * @param string $html
     * @return string
     */
    private function _unifyLineBreaks($html)
    {
        return preg_replace("/\015\012|\015|\012/", $this->_options['line_break'], $html);
    }

    /**
     * @param string $html
     * @return string
     */
    private function _compressScriptAndStyleTags($html)
    {
        return preg_replace_callback(
            '#(<(?P<tag>style|script)\\b(?:[^>"\']|"[^"]*"|\'[^\']*\')*>)(?P<code>.*?)(</(?P=tag)\\s*>)#is',
            function ($matches) {
                $tag = strtolower($matches['tag']);
                if ($tag == 'script') {
                    if (!$this->_options['compress_scripts'] || !$this->_isJavascriptTag($matches[1])) {
                        return $matches[0];
                    }
                    $code = $this->minifyJavascript(trim($matches['code']));
                } else {
                    if (!$this->_options['compress_css']) {
                        return $matches[0];
                    }
                    $code = $this->minifyCss(trim($matches['code']));
                }

                return $matches[1].$code.$matches[4];
            },
            $html
        );
    }

    /**
     * @param string $opening_tag
     * @return bool
     */
    private function _isJavascriptTag($opening_tag)
    {
        if (!preg_match('/\\stype\\s*=\\s*(?:(["\'])(.*?)\\1|([^\\s>]+))/is', $opening_tag, $matches)) {
            return true;
        }

        $type = (isset($matches[1]) && $matches[1] != '')
            ? $matches[2]
            : $matches[3];
        $type = strtolower(trim(explode(';', $type, 2)[0]));
        if ($type == '') {
            return true;
        }

        return in_array($type, array(
            'application/ecmascript',
            'application/javascript',
            'module',
            'text/ecmascript',
            'text/javascript',
        ));
    }

    /**
     * @param bool $css
     * @param bool $allow_file_import Whether add() may treat its argument as a
     *     file path. Must be false for rendered/inline source, which is never
     *     a path even when it happens to match a readable file on disk.
     * @return MatthiasMullie\Minify\CSS|MatthiasMullie\Minify\JS
     */
    private function _createMinifier($css, $allow_file_import = true)
    {
        require_once dirname(__DIR__).'/MatthiasMullie/autoload.php';

        if ($css) {
            $minifier = new class extends MatthiasMullie\Minify\CSS {
                /**
                 * @var bool
                 */
                public $allowFileImport = true;

                /**
                 * Keep @import statements external. Their files are not part of the
                 * combine_files() modification-time cache.
                 *
                 * @param string $source
                 * @param string $content
                 * @param array $parents
                 * @return string
                 */
                protected function combineImports($source, $content, $parents)
                {
                    return $content;
                }

                /**
                 * Fragment-only URLs, query-only URLs and URI schemes are not file paths.
                 *
                 * @param string $path
                 * @return bool
                 */
                protected function canImportByPath($path)
                {
                    if ($path === ''
                        || $path[0] === '#'
                        || $path[0] === '?'
                        || preg_match('/^[a-z][a-z0-9+.-]*:/i', $path)
                    ) {
                        return false;
                    }

                    return parent::canImportByPath($path);
                }

                /**
                 * @param string $path
                 * @return bool
                 */
                protected function canImportFile($path)
                {
                    return $this->allowFileImport && parent::canImportFile($path);
                }
            };
            $minifier->allowFileImport = $allow_file_import;
            // Combining files must not silently embed referenced assets into the bundle.
            $minifier->setImportExtensions(array());

            return $minifier;
        }

        $minifier = new class extends MatthiasMullie\Minify\JS {
            /**
             * @var bool
             */
            public $allowFileImport = true;

            /**
             * Join statement and block boundaries that are safe to terminate.
             * Other line breaks are kept because they can be significant for
             * automatic semicolon insertion or template literals. A boundary
             * immediately followed by one of the keywords below is also left
             * untouched, because each of them must attach directly to the
             * preceding construct, with no statement (not even an empty one)
             * allowed in between: "while" (do-while), "catch"/"finally" (try),
             * "else" (if), and "from" (the tail of a multiline
             * `import {...} from "...";` or `export {...} from "...";`).
             *
             * @param string $content
             * @return string
             */
            protected function stripWhitespace($content)
            {
                $content = parent::stripWhitespace($content);

                $content = str_replace(";\n", ';', $content);

                // Extracted strings, regular expressions and template literals
                // can continue an expression across a line break. Preserved
                // comments may occur at the same boundary.
                return preg_replace(
                    '/}\n(?![\'"`]|\/\*\d+\*\/|[ \t]*(?:while|catch|finally|else|from)\b)/',
                    '};',
                    $content
                );
            }

            /**
             * @param string $path
             * @return bool
             */
            protected function canImportFile($path)
            {
                return $this->allowFileImport && parent::canImportFile($path);
            }
        };
        $minifier->allowFileImport = $allow_file_import;

        return $minifier;
    }

    /**
     * @param string $path
     * @param string $content
     * @return bool
     */
    private function _saveAtomically($path, $content)
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            return false;
        }

        // Replacing a symlink would unlink it, and an existing writable bundle
        // can still be updated when the template directory itself is read-only.
        if (is_link($path) || !is_writable($directory)) {
            return $this->_saveDirectly($path, $content);
        }

        $temporary_path = tempnam($directory, '.compactor-');
        if ($temporary_path === false) {
            return $this->_saveDirectly($path, $content);
        }

        $permissions = 0644;
        if (is_file($path)) {
            $current_permissions = @fileperms($path);
            if ($current_permissions !== false) {
                $permissions = $current_permissions & 0777;
            }
        }
        if (file_put_contents($temporary_path, $content, LOCK_EX) === false
            || !@chmod($temporary_path, $permissions)
            || !@rename($temporary_path, $path)
        ) {
            if (is_file($temporary_path)) {
                @unlink($temporary_path);
            }

            return $this->_saveDirectly($path, $content);
        }

        clearstatcache(true, $path);

        return true;
    }

    /**
     * Update an existing bundle when atomic replacement is not possible.
     *
     * @param string $path
     * @param string $content
     * @return bool
     */
    private function _saveDirectly($path, $content)
    {
        if (!is_file($path) || !is_writable($path)) {
            return false;
        }

        if (file_put_contents($path, $content, LOCK_EX) === false) {
            return false;
        }

        clearstatcache(true, $path);

        return true;
    }
}
