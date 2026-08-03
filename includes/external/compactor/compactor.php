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
                if (array_key_exists($name, $this->_options)) {
                    $this->_options[$name] = $value;
                }
            }

            return;
        }

        if (array_key_exists($varname, $this->_options)) {
            $this->_options[$varname] = $varvalue;
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
        $vendor_directory = dirname(__DIR__).'/matthiasmullie';
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
     * @param string $javascript
     * @return string
     */
    public function minifyJavascript($javascript)
    {
        try {
            $minifier = $this->_createMinifier(false);

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
            $minifier = $this->_createMinifier(true);

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

        $html = $this->_extractPreservedBlocks($html);
        if ($this->_options['strip_comments']) {
            $html = $this->_stripHTMLComments($html);
        }
        if ($this->_options['compress_horizontal'] || $this->_options['compress_vertical']) {
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
        $tags = array_map(function ($tag) {
            return preg_quote($tag, '!');
        }, $this->_options['preserved_tags']);
        if (count($tags) == 0) {
            return $html;
        }

        $boundary = (is_string($this->_options['preserved_boundry']) && $this->_options['preserved_boundry'] != '')
            ? $this->_options['preserved_boundry']
            : '@@PRESERVEDTAG@@';
        while (strpos($html, $boundary) !== false) {
            $boundary .= '_';
        }
        $this->_preserved_boundary = $boundary;

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
     * @param string $html
     * @return string
     */
    private function _reinstatePreservedBlocks($html)
    {
        if ($this->_options['script_line_breaks']) {
            $html = $this->_addScriptLineBreaks($html);
        }

        return strtr($html, $this->_preserved_blocks);
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
                $has_line_breaks = ($compress_line_breaks
                    && $line_break_length > 0
                    && strpos($tag, $line_break) !== false
                );
                if (!$has_tabs && !$has_line_breaks) {
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
                    if (($compress_tabs && $character == "\t") || $is_line_break) {
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
     * Collapse tabs and line breaks in text segments without touching tag
     * attributes. Whitespace is replaced by one space instead of being removed,
     * because it can separate adjacent inline elements or words.
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

            if ($compress_line_breaks && $line_break != '') {
                $part = preg_replace(
                    '#[ \t]*'.$line_break.'[ \t]*(?:'.$line_break.'[ \t]*)*#',
                    ' ',
                    $part
                );
            }
            if ($compress_tabs) {
                $part = preg_replace('/\t+/', ' ', $part);
            }
            $parts[$index] = $part;
        }

        return implode('', $parts);
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
     * @return MatthiasMullie\Minify\CSS|MatthiasMullie\Minify\JS
     */
    private function _createMinifier($css)
    {
        require_once dirname(__DIR__).'/matthiasmullie/autoload.php';

        if ($css) {
            $minifier = new class extends MatthiasMullie\Minify\CSS {
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
            };
            // Combining files must not silently embed referenced assets into the bundle.
            $minifier->setImportExtensions(array());

            return $minifier;
        }

        return new class extends MatthiasMullie\Minify\JS {
            /**
             * Join statement and block boundaries that are safe to terminate.
             * Other line breaks are kept because they can be significant for
             * automatic semicolon insertion or template literals.
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
                return preg_replace('/}\n(?![\'"`]|\/\*\d+\*\/)/', '};', $content);
            }
        };
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
