<?php
/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class Shopgate_Helper_String
{
    /**
     * Removes all disallowed HTML tags from a given string.
     *
     * By default the following are allowed:
     *
     * "ADDRESS", "AREA", "A", "BASE", "BASEFONT", "BIG", "BLOCKQUOTE", "BODY", "BR",
     * "B", "CAPTION", "CENTER", "CITE", "CODE", "DD", "DFN", "DIR", "DIV", "DL", "DT",
     * "EM", "FONT", "FORM", "H1", "H2", "H3", "H4", "H5", "H6", "HEAD", "HR", "HTML",
     * "ISINDEX", "I", "KBD", "LINK", "LI", "MAP", "MENU", "META", "OL", "OPTION", "PARAM", "PRE",
     * "IMG", "INPUT", "P", "SAMP", "SELECT", "SMALL", "STRIKE", "STRONG", "STYLE", "SUB", "SUP",
     * "TABLE", "TD", "TEXTAREA", "TH", "TITLE", "TR", "TT", "UL", "U", "VAR"
     *
     *
     * @param string   $string                The input string to be filtered.
     * @param string[] $removeTags            The tags to be removed.
     * @param string[] $additionalAllowedTags Additional tags to be allowed.
     *
     * @return string The sanitized string.
     */
    public function removeTagsFromString($string, $removeTags = array(), $additionalAllowedTags = array())
    {
        // all tags available
        $allowedTags = array(
            "ADDRESS",
            "AREA",
            "A",
            "BASE",
            "BASEFONT",
            "BIG",
            "BLOCKQUOTE",
            "BODY",
            "BR",
            "B",
            "CAPTION",
            "CENTER",
            "CITE",
            "CODE",
            "DD",
            "DFN",
            "DIR",
            "DIV",
            "DL",
            "DT",
            "EM",
            "FONT",
            "FORM",
            "H1",
            "H2",
            "H3",
            "H4",
            "H5",
            "H6",
            "HEAD",
            "HR",
            "HTML",
            "IMG",
            "INPUT",
            "ISINDEX",
            "I",
            "KBD",
            "LINK",
            "LI",
            "MAP",
            "MENU",
            "META",
            "OL",
            "OPTION",
            "PARAM",
            "PRE",
            "P",
            "SAMP",
            "SELECT",
            "SMALL",
            "STRIKE",
            "STRONG",
            "STYLE",
            "SUB",
            "SUP",
            "TABLE",
            "TD",
            "TEXTAREA",
            "TH",
            "TITLE",
            "TR",
            "TT",
            "UL",
            "U",
            "VAR",
        );

        foreach ($allowedTags as &$t) {
            $t = strtolower($t);
        }
        foreach ($removeTags as &$t) {
            $t = strtolower($t);
        }
        foreach ($additionalAllowedTags as &$t) {
            $t = strtolower($t);
        }

        // some tags must be removed completely (including content)
        $string = preg_replace('#<script([^>]*?)>(.*?)</script>#is', '', $string);
        $string = preg_replace('#<style([^>]*?)>(.*?)</style>#is', '', $string);
        $string = preg_replace('#<link([^>]*?)>(.*?)</link>#is', '', $string);

        $string = preg_replace('#<script([^>]*?)/>#is', '', $string);
        $string = preg_replace('#<style([^>]*?)/>#is', '', $string);
        $string = preg_replace('#<link([^>]*?)/>#is', '', $string);

        // add the additional allowed tags to the list
        $allowedTags = array_merge($allowedTags, $additionalAllowedTags);

        // strip the disallowed tags from the list
        $allowedTags = array_diff($allowedTags, $removeTags);

        // add HTML brackets
        foreach ($allowedTags as &$t) {
            $t = "<$t>";
        }

        // let PHP sanitize the string and return it
        return strip_tags($string, implode(",", $allowedTags));
    }
}
