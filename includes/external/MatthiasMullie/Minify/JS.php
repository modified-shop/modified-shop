<?php

/**
 * JavaScript minifier.
 *
 * Please report bugs on https://github.com/matthiasmullie/minify/issues
 *
 * @author Matthias Mullie <minify@mullie.eu>
 * @copyright Copyright (c) 2012, Matthias Mullie. All rights reserved
 * @license MIT License
 */

namespace MatthiasMullie\Minify;

/**
 * JavaScript Minifier Class.
 *
 * Please report bugs on https://github.com/matthiasmullie/minify/issues
 *
 * @author Matthias Mullie <minify@mullie.eu>
 * @author Tijs Verkoyen <minify@verkoyen.eu>
 * @copyright Copyright (c) 2012, Matthias Mullie. All rights reserved
 * @license MIT License
 */
class JS extends Minify
{
    /**
     * Var-matching regex based on http://stackoverflow.com/a/9337047/802993.
     *
     * Note that regular expressions using that bit must have the PCRE_UTF8
     * pattern modifier (/u) set.
     *
     * @internal
     *
     * @var string
     */
    const REGEX_VARIABLE = '\b[$A-Z\_a-z\xaa\xb5\xba\xc0-\xd6\xd8-\xf6\xf8-\x{02c1}\x{02c6}-\x{02d1}\x{02e0}-\x{02e4}\x{02ec}\x{02ee}\x{0370}-\x{0374}\x{0376}\x{0377}\x{037a}-\x{037d}\x{0386}\x{0388}-\x{038a}\x{038c}\x{038e}-\x{03a1}\x{03a3}-\x{03f5}\x{03f7}-\x{0481}\x{048a}-\x{0527}\x{0531}-\x{0556}\x{0559}\x{0561}-\x{0587}\x{05d0}-\x{05ea}\x{05f0}-\x{05f2}\x{0620}-\x{064a}\x{066e}\x{066f}\x{0671}-\x{06d3}\x{06d5}\x{06e5}\x{06e6}\x{06ee}\x{06ef}\x{06fa}-\x{06fc}\x{06ff}\x{0710}\x{0712}-\x{072f}\x{074d}-\x{07a5}\x{07b1}\x{07ca}-\x{07ea}\x{07f4}\x{07f5}\x{07fa}\x{0800}-\x{0815}\x{081a}\x{0824}\x{0828}\x{0840}-\x{0858}\x{08a0}\x{08a2}-\x{08ac}\x{0904}-\x{0939}\x{093d}\x{0950}\x{0958}-\x{0961}\x{0971}-\x{0977}\x{0979}-\x{097f}\x{0985}-\x{098c}\x{098f}\x{0990}\x{0993}-\x{09a8}\x{09aa}-\x{09b0}\x{09b2}\x{09b6}-\x{09b9}\x{09bd}\x{09ce}\x{09dc}\x{09dd}\x{09df}-\x{09e1}\x{09f0}\x{09f1}\x{0a05}-\x{0a0a}\x{0a0f}\x{0a10}\x{0a13}-\x{0a28}\x{0a2a}-\x{0a30}\x{0a32}\x{0a33}\x{0a35}\x{0a36}\x{0a38}\x{0a39}\x{0a59}-\x{0a5c}\x{0a5e}\x{0a72}-\x{0a74}\x{0a85}-\x{0a8d}\x{0a8f}-\x{0a91}\x{0a93}-\x{0aa8}\x{0aaa}-\x{0ab0}\x{0ab2}\x{0ab3}\x{0ab5}-\x{0ab9}\x{0abd}\x{0ad0}\x{0ae0}\x{0ae1}\x{0b05}-\x{0b0c}\x{0b0f}\x{0b10}\x{0b13}-\x{0b28}\x{0b2a}-\x{0b30}\x{0b32}\x{0b33}\x{0b35}-\x{0b39}\x{0b3d}\x{0b5c}\x{0b5d}\x{0b5f}-\x{0b61}\x{0b71}\x{0b83}\x{0b85}-\x{0b8a}\x{0b8e}-\x{0b90}\x{0b92}-\x{0b95}\x{0b99}\x{0b9a}\x{0b9c}\x{0b9e}\x{0b9f}\x{0ba3}\x{0ba4}\x{0ba8}-\x{0baa}\x{0bae}-\x{0bb9}\x{0bd0}\x{0c05}-\x{0c0c}\x{0c0e}-\x{0c10}\x{0c12}-\x{0c28}\x{0c2a}-\x{0c33}\x{0c35}-\x{0c39}\x{0c3d}\x{0c58}\x{0c59}\x{0c60}\x{0c61}\x{0c85}-\x{0c8c}\x{0c8e}-\x{0c90}\x{0c92}-\x{0ca8}\x{0caa}-\x{0cb3}\x{0cb5}-\x{0cb9}\x{0cbd}\x{0cde}\x{0ce0}\x{0ce1}\x{0cf1}\x{0cf2}\x{0d05}-\x{0d0c}\x{0d0e}-\x{0d10}\x{0d12}-\x{0d3a}\x{0d3d}\x{0d4e}\x{0d60}\x{0d61}\x{0d7a}-\x{0d7f}\x{0d85}-\x{0d96}\x{0d9a}-\x{0db1}\x{0db3}-\x{0dbb}\x{0dbd}\x{0dc0}-\x{0dc6}\x{0e01}-\x{0e30}\x{0e32}\x{0e33}\x{0e40}-\x{0e46}\x{0e81}\x{0e82}\x{0e84}\x{0e87}\x{0e88}\x{0e8a}\x{0e8d}\x{0e94}-\x{0e97}\x{0e99}-\x{0e9f}\x{0ea1}-\x{0ea3}\x{0ea5}\x{0ea7}\x{0eaa}\x{0eab}\x{0ead}-\x{0eb0}\x{0eb2}\x{0eb3}\x{0ebd}\x{0ec0}-\x{0ec4}\x{0ec6}\x{0edc}-\x{0edf}\x{0f00}\x{0f40}-\x{0f47}\x{0f49}-\x{0f6c}\x{0f88}-\x{0f8c}\x{1000}-\x{102a}\x{103f}\x{1050}-\x{1055}\x{105a}-\x{105d}\x{1061}\x{1065}\x{1066}\x{106e}-\x{1070}\x{1075}-\x{1081}\x{108e}\x{10a0}-\x{10c5}\x{10c7}\x{10cd}\x{10d0}-\x{10fa}\x{10fc}-\x{1248}\x{124a}-\x{124d}\x{1250}-\x{1256}\x{1258}\x{125a}-\x{125d}\x{1260}-\x{1288}\x{128a}-\x{128d}\x{1290}-\x{12b0}\x{12b2}-\x{12b5}\x{12b8}-\x{12be}\x{12c0}\x{12c2}-\x{12c5}\x{12c8}-\x{12d6}\x{12d8}-\x{1310}\x{1312}-\x{1315}\x{1318}-\x{135a}\x{1380}-\x{138f}\x{13a0}-\x{13f4}\x{1401}-\x{166c}\x{166f}-\x{167f}\x{1681}-\x{169a}\x{16a0}-\x{16ea}\x{16ee}-\x{16f0}\x{1700}-\x{170c}\x{170e}-\x{1711}\x{1720}-\x{1731}\x{1740}-\x{1751}\x{1760}-\x{176c}\x{176e}-\x{1770}\x{1780}-\x{17b3}\x{17d7}\x{17dc}\x{1820}-\x{1877}\x{1880}-\x{18a8}\x{18aa}\x{18b0}-\x{18f5}\x{1900}-\x{191c}\x{1950}-\x{196d}\x{1970}-\x{1974}\x{1980}-\x{19ab}\x{19c1}-\x{19c7}\x{1a00}-\x{1a16}\x{1a20}-\x{1a54}\x{1aa7}\x{1b05}-\x{1b33}\x{1b45}-\x{1b4b}\x{1b83}-\x{1ba0}\x{1bae}\x{1baf}\x{1bba}-\x{1be5}\x{1c00}-\x{1c23}\x{1c4d}-\x{1c4f}\x{1c5a}-\x{1c7d}\x{1ce9}-\x{1cec}\x{1cee}-\x{1cf1}\x{1cf5}\x{1cf6}\x{1d00}-\x{1dbf}\x{1e00}-\x{1f15}\x{1f18}-\x{1f1d}\x{1f20}-\x{1f45}\x{1f48}-\x{1f4d}\x{1f50}-\x{1f57}\x{1f59}\x{1f5b}\x{1f5d}\x{1f5f}-\x{1f7d}\x{1f80}-\x{1fb4}\x{1fb6}-\x{1fbc}\x{1fbe}\x{1fc2}-\x{1fc4}\x{1fc6}-\x{1fcc}\x{1fd0}-\x{1fd3}\x{1fd6}-\x{1fdb}\x{1fe0}-\x{1fec}\x{1ff2}-\x{1ff4}\x{1ff6}-\x{1ffc}\x{2071}\x{207f}\x{2090}-\x{209c}\x{2102}\x{2107}\x{210a}-\x{2113}\x{2115}\x{2119}-\x{211d}\x{2124}\x{2126}\x{2128}\x{212a}-\x{212d}\x{212f}-\x{2139}\x{213c}-\x{213f}\x{2145}-\x{2149}\x{214e}\x{2160}-\x{2188}\x{2c00}-\x{2c2e}\x{2c30}-\x{2c5e}\x{2c60}-\x{2ce4}\x{2ceb}-\x{2cee}\x{2cf2}\x{2cf3}\x{2d00}-\x{2d25}\x{2d27}\x{2d2d}\x{2d30}-\x{2d67}\x{2d6f}\x{2d80}-\x{2d96}\x{2da0}-\x{2da6}\x{2da8}-\x{2dae}\x{2db0}-\x{2db6}\x{2db8}-\x{2dbe}\x{2dc0}-\x{2dc6}\x{2dc8}-\x{2dce}\x{2dd0}-\x{2dd6}\x{2dd8}-\x{2dde}\x{2e2f}\x{3005}-\x{3007}\x{3021}-\x{3029}\x{3031}-\x{3035}\x{3038}-\x{303c}\x{3041}-\x{3096}\x{309d}-\x{309f}\x{30a1}-\x{30fa}\x{30fc}-\x{30ff}\x{3105}-\x{312d}\x{3131}-\x{318e}\x{31a0}-\x{31ba}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}\x{4e00}-\x{9fcc}\x{a000}-\x{a48c}\x{a4d0}-\x{a4fd}\x{a500}-\x{a60c}\x{a610}-\x{a61f}\x{a62a}\x{a62b}\x{a640}-\x{a66e}\x{a67f}-\x{a697}\x{a6a0}-\x{a6ef}\x{a717}-\x{a71f}\x{a722}-\x{a788}\x{a78b}-\x{a78e}\x{a790}-\x{a793}\x{a7a0}-\x{a7aa}\x{a7f8}-\x{a801}\x{a803}-\x{a805}\x{a807}-\x{a80a}\x{a80c}-\x{a822}\x{a840}-\x{a873}\x{a882}-\x{a8b3}\x{a8f2}-\x{a8f7}\x{a8fb}\x{a90a}-\x{a925}\x{a930}-\x{a946}\x{a960}-\x{a97c}\x{a984}-\x{a9b2}\x{a9cf}\x{aa00}-\x{aa28}\x{aa40}-\x{aa42}\x{aa44}-\x{aa4b}\x{aa60}-\x{aa76}\x{aa7a}\x{aa80}-\x{aaaf}\x{aab1}\x{aab5}\x{aab6}\x{aab9}-\x{aabd}\x{aac0}\x{aac2}\x{aadb}-\x{aadd}\x{aae0}-\x{aaea}\x{aaf2}-\x{aaf4}\x{ab01}-\x{ab06}\x{ab09}-\x{ab0e}\x{ab11}-\x{ab16}\x{ab20}-\x{ab26}\x{ab28}-\x{ab2e}\x{abc0}-\x{abe2}\x{ac00}-\x{d7a3}\x{d7b0}-\x{d7c6}\x{d7cb}-\x{d7fb}\x{f900}-\x{fa6d}\x{fa70}-\x{fad9}\x{fb00}-\x{fb06}\x{fb13}-\x{fb17}\x{fb1d}\x{fb1f}-\x{fb28}\x{fb2a}-\x{fb36}\x{fb38}-\x{fb3c}\x{fb3e}\x{fb40}\x{fb41}\x{fb43}\x{fb44}\x{fb46}-\x{fbb1}\x{fbd3}-\x{fd3d}\x{fd50}-\x{fd8f}\x{fd92}-\x{fdc7}\x{fdf0}-\x{fdfb}\x{fe70}-\x{fe74}\x{fe76}-\x{fefc}\x{ff21}-\x{ff3a}\x{ff41}-\x{ff5a}\x{ff66}-\x{ffbe}\x{ffc2}-\x{ffc7}\x{ffca}-\x{ffcf}\x{ffd2}-\x{ffd7}\x{ffda}-\x{ffdc}][$A-Z\_a-z\xaa\xb5\xba\xc0-\xd6\xd8-\xf6\xf8-\x{02c1}\x{02c6}-\x{02d1}\x{02e0}-\x{02e4}\x{02ec}\x{02ee}\x{0370}-\x{0374}\x{0376}\x{0377}\x{037a}-\x{037d}\x{0386}\x{0388}-\x{038a}\x{038c}\x{038e}-\x{03a1}\x{03a3}-\x{03f5}\x{03f7}-\x{0481}\x{048a}-\x{0527}\x{0531}-\x{0556}\x{0559}\x{0561}-\x{0587}\x{05d0}-\x{05ea}\x{05f0}-\x{05f2}\x{0620}-\x{064a}\x{066e}\x{066f}\x{0671}-\x{06d3}\x{06d5}\x{06e5}\x{06e6}\x{06ee}\x{06ef}\x{06fa}-\x{06fc}\x{06ff}\x{0710}\x{0712}-\x{072f}\x{074d}-\x{07a5}\x{07b1}\x{07ca}-\x{07ea}\x{07f4}\x{07f5}\x{07fa}\x{0800}-\x{0815}\x{081a}\x{0824}\x{0828}\x{0840}-\x{0858}\x{08a0}\x{08a2}-\x{08ac}\x{0904}-\x{0939}\x{093d}\x{0950}\x{0958}-\x{0961}\x{0971}-\x{0977}\x{0979}-\x{097f}\x{0985}-\x{098c}\x{098f}\x{0990}\x{0993}-\x{09a8}\x{09aa}-\x{09b0}\x{09b2}\x{09b6}-\x{09b9}\x{09bd}\x{09ce}\x{09dc}\x{09dd}\x{09df}-\x{09e1}\x{09f0}\x{09f1}\x{0a05}-\x{0a0a}\x{0a0f}\x{0a10}\x{0a13}-\x{0a28}\x{0a2a}-\x{0a30}\x{0a32}\x{0a33}\x{0a35}\x{0a36}\x{0a38}\x{0a39}\x{0a59}-\x{0a5c}\x{0a5e}\x{0a72}-\x{0a74}\x{0a85}-\x{0a8d}\x{0a8f}-\x{0a91}\x{0a93}-\x{0aa8}\x{0aaa}-\x{0ab0}\x{0ab2}\x{0ab3}\x{0ab5}-\x{0ab9}\x{0abd}\x{0ad0}\x{0ae0}\x{0ae1}\x{0b05}-\x{0b0c}\x{0b0f}\x{0b10}\x{0b13}-\x{0b28}\x{0b2a}-\x{0b30}\x{0b32}\x{0b33}\x{0b35}-\x{0b39}\x{0b3d}\x{0b5c}\x{0b5d}\x{0b5f}-\x{0b61}\x{0b71}\x{0b83}\x{0b85}-\x{0b8a}\x{0b8e}-\x{0b90}\x{0b92}-\x{0b95}\x{0b99}\x{0b9a}\x{0b9c}\x{0b9e}\x{0b9f}\x{0ba3}\x{0ba4}\x{0ba8}-\x{0baa}\x{0bae}-\x{0bb9}\x{0bd0}\x{0c05}-\x{0c0c}\x{0c0e}-\x{0c10}\x{0c12}-\x{0c28}\x{0c2a}-\x{0c33}\x{0c35}-\x{0c39}\x{0c3d}\x{0c58}\x{0c59}\x{0c60}\x{0c61}\x{0c85}-\x{0c8c}\x{0c8e}-\x{0c90}\x{0c92}-\x{0ca8}\x{0caa}-\x{0cb3}\x{0cb5}-\x{0cb9}\x{0cbd}\x{0cde}\x{0ce0}\x{0ce1}\x{0cf1}\x{0cf2}\x{0d05}-\x{0d0c}\x{0d0e}-\x{0d10}\x{0d12}-\x{0d3a}\x{0d3d}\x{0d4e}\x{0d60}\x{0d61}\x{0d7a}-\x{0d7f}\x{0d85}-\x{0d96}\x{0d9a}-\x{0db1}\x{0db3}-\x{0dbb}\x{0dbd}\x{0dc0}-\x{0dc6}\x{0e01}-\x{0e30}\x{0e32}\x{0e33}\x{0e40}-\x{0e46}\x{0e81}\x{0e82}\x{0e84}\x{0e87}\x{0e88}\x{0e8a}\x{0e8d}\x{0e94}-\x{0e97}\x{0e99}-\x{0e9f}\x{0ea1}-\x{0ea3}\x{0ea5}\x{0ea7}\x{0eaa}\x{0eab}\x{0ead}-\x{0eb0}\x{0eb2}\x{0eb3}\x{0ebd}\x{0ec0}-\x{0ec4}\x{0ec6}\x{0edc}-\x{0edf}\x{0f00}\x{0f40}-\x{0f47}\x{0f49}-\x{0f6c}\x{0f88}-\x{0f8c}\x{1000}-\x{102a}\x{103f}\x{1050}-\x{1055}\x{105a}-\x{105d}\x{1061}\x{1065}\x{1066}\x{106e}-\x{1070}\x{1075}-\x{1081}\x{108e}\x{10a0}-\x{10c5}\x{10c7}\x{10cd}\x{10d0}-\x{10fa}\x{10fc}-\x{1248}\x{124a}-\x{124d}\x{1250}-\x{1256}\x{1258}\x{125a}-\x{125d}\x{1260}-\x{1288}\x{128a}-\x{128d}\x{1290}-\x{12b0}\x{12b2}-\x{12b5}\x{12b8}-\x{12be}\x{12c0}\x{12c2}-\x{12c5}\x{12c8}-\x{12d6}\x{12d8}-\x{1310}\x{1312}-\x{1315}\x{1318}-\x{135a}\x{1380}-\x{138f}\x{13a0}-\x{13f4}\x{1401}-\x{166c}\x{166f}-\x{167f}\x{1681}-\x{169a}\x{16a0}-\x{16ea}\x{16ee}-\x{16f0}\x{1700}-\x{170c}\x{170e}-\x{1711}\x{1720}-\x{1731}\x{1740}-\x{1751}\x{1760}-\x{176c}\x{176e}-\x{1770}\x{1780}-\x{17b3}\x{17d7}\x{17dc}\x{1820}-\x{1877}\x{1880}-\x{18a8}\x{18aa}\x{18b0}-\x{18f5}\x{1900}-\x{191c}\x{1950}-\x{196d}\x{1970}-\x{1974}\x{1980}-\x{19ab}\x{19c1}-\x{19c7}\x{1a00}-\x{1a16}\x{1a20}-\x{1a54}\x{1aa7}\x{1b05}-\x{1b33}\x{1b45}-\x{1b4b}\x{1b83}-\x{1ba0}\x{1bae}\x{1baf}\x{1bba}-\x{1be5}\x{1c00}-\x{1c23}\x{1c4d}-\x{1c4f}\x{1c5a}-\x{1c7d}\x{1ce9}-\x{1cec}\x{1cee}-\x{1cf1}\x{1cf5}\x{1cf6}\x{1d00}-\x{1dbf}\x{1e00}-\x{1f15}\x{1f18}-\x{1f1d}\x{1f20}-\x{1f45}\x{1f48}-\x{1f4d}\x{1f50}-\x{1f57}\x{1f59}\x{1f5b}\x{1f5d}\x{1f5f}-\x{1f7d}\x{1f80}-\x{1fb4}\x{1fb6}-\x{1fbc}\x{1fbe}\x{1fc2}-\x{1fc4}\x{1fc6}-\x{1fcc}\x{1fd0}-\x{1fd3}\x{1fd6}-\x{1fdb}\x{1fe0}-\x{1fec}\x{1ff2}-\x{1ff4}\x{1ff6}-\x{1ffc}\x{2071}\x{207f}\x{2090}-\x{209c}\x{2102}\x{2107}\x{210a}-\x{2113}\x{2115}\x{2119}-\x{211d}\x{2124}\x{2126}\x{2128}\x{212a}-\x{212d}\x{212f}-\x{2139}\x{213c}-\x{213f}\x{2145}-\x{2149}\x{214e}\x{2160}-\x{2188}\x{2c00}-\x{2c2e}\x{2c30}-\x{2c5e}\x{2c60}-\x{2ce4}\x{2ceb}-\x{2cee}\x{2cf2}\x{2cf3}\x{2d00}-\x{2d25}\x{2d27}\x{2d2d}\x{2d30}-\x{2d67}\x{2d6f}\x{2d80}-\x{2d96}\x{2da0}-\x{2da6}\x{2da8}-\x{2dae}\x{2db0}-\x{2db6}\x{2db8}-\x{2dbe}\x{2dc0}-\x{2dc6}\x{2dc8}-\x{2dce}\x{2dd0}-\x{2dd6}\x{2dd8}-\x{2dde}\x{2e2f}\x{3005}-\x{3007}\x{3021}-\x{3029}\x{3031}-\x{3035}\x{3038}-\x{303c}\x{3041}-\x{3096}\x{309d}-\x{309f}\x{30a1}-\x{30fa}\x{30fc}-\x{30ff}\x{3105}-\x{312d}\x{3131}-\x{318e}\x{31a0}-\x{31ba}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}\x{4e00}-\x{9fcc}\x{a000}-\x{a48c}\x{a4d0}-\x{a4fd}\x{a500}-\x{a60c}\x{a610}-\x{a61f}\x{a62a}\x{a62b}\x{a640}-\x{a66e}\x{a67f}-\x{a697}\x{a6a0}-\x{a6ef}\x{a717}-\x{a71f}\x{a722}-\x{a788}\x{a78b}-\x{a78e}\x{a790}-\x{a793}\x{a7a0}-\x{a7aa}\x{a7f8}-\x{a801}\x{a803}-\x{a805}\x{a807}-\x{a80a}\x{a80c}-\x{a822}\x{a840}-\x{a873}\x{a882}-\x{a8b3}\x{a8f2}-\x{a8f7}\x{a8fb}\x{a90a}-\x{a925}\x{a930}-\x{a946}\x{a960}-\x{a97c}\x{a984}-\x{a9b2}\x{a9cf}\x{aa00}-\x{aa28}\x{aa40}-\x{aa42}\x{aa44}-\x{aa4b}\x{aa60}-\x{aa76}\x{aa7a}\x{aa80}-\x{aaaf}\x{aab1}\x{aab5}\x{aab6}\x{aab9}-\x{aabd}\x{aac0}\x{aac2}\x{aadb}-\x{aadd}\x{aae0}-\x{aaea}\x{aaf2}-\x{aaf4}\x{ab01}-\x{ab06}\x{ab09}-\x{ab0e}\x{ab11}-\x{ab16}\x{ab20}-\x{ab26}\x{ab28}-\x{ab2e}\x{abc0}-\x{abe2}\x{ac00}-\x{d7a3}\x{d7b0}-\x{d7c6}\x{d7cb}-\x{d7fb}\x{f900}-\x{fa6d}\x{fa70}-\x{fad9}\x{fb00}-\x{fb06}\x{fb13}-\x{fb17}\x{fb1d}\x{fb1f}-\x{fb28}\x{fb2a}-\x{fb36}\x{fb38}-\x{fb3c}\x{fb3e}\x{fb40}\x{fb41}\x{fb43}\x{fb44}\x{fb46}-\x{fbb1}\x{fbd3}-\x{fd3d}\x{fd50}-\x{fd8f}\x{fd92}-\x{fdc7}\x{fdf0}-\x{fdfb}\x{fe70}-\x{fe74}\x{fe76}-\x{fefc}\x{ff21}-\x{ff3a}\x{ff41}-\x{ff5a}\x{ff66}-\x{ffbe}\x{ffc2}-\x{ffc7}\x{ffca}-\x{ffcf}\x{ffd2}-\x{ffd7}\x{ffda}-\x{ffdc}0-9\x{0300}-\x{036f}\x{0483}-\x{0487}\x{0591}-\x{05bd}\x{05bf}\x{05c1}\x{05c2}\x{05c4}\x{05c5}\x{05c7}\x{0610}-\x{061a}\x{064b}-\x{0669}\x{0670}\x{06d6}-\x{06dc}\x{06df}-\x{06e4}\x{06e7}\x{06e8}\x{06ea}-\x{06ed}\x{06f0}-\x{06f9}\x{0711}\x{0730}-\x{074a}\x{07a6}-\x{07b0}\x{07c0}-\x{07c9}\x{07eb}-\x{07f3}\x{0816}-\x{0819}\x{081b}-\x{0823}\x{0825}-\x{0827}\x{0829}-\x{082d}\x{0859}-\x{085b}\x{08e4}-\x{08fe}\x{0900}-\x{0903}\x{093a}-\x{093c}\x{093e}-\x{094f}\x{0951}-\x{0957}\x{0962}\x{0963}\x{0966}-\x{096f}\x{0981}-\x{0983}\x{09bc}\x{09be}-\x{09c4}\x{09c7}\x{09c8}\x{09cb}-\x{09cd}\x{09d7}\x{09e2}\x{09e3}\x{09e6}-\x{09ef}\x{0a01}-\x{0a03}\x{0a3c}\x{0a3e}-\x{0a42}\x{0a47}\x{0a48}\x{0a4b}-\x{0a4d}\x{0a51}\x{0a66}-\x{0a71}\x{0a75}\x{0a81}-\x{0a83}\x{0abc}\x{0abe}-\x{0ac5}\x{0ac7}-\x{0ac9}\x{0acb}-\x{0acd}\x{0ae2}\x{0ae3}\x{0ae6}-\x{0aef}\x{0b01}-\x{0b03}\x{0b3c}\x{0b3e}-\x{0b44}\x{0b47}\x{0b48}\x{0b4b}-\x{0b4d}\x{0b56}\x{0b57}\x{0b62}\x{0b63}\x{0b66}-\x{0b6f}\x{0b82}\x{0bbe}-\x{0bc2}\x{0bc6}-\x{0bc8}\x{0bca}-\x{0bcd}\x{0bd7}\x{0be6}-\x{0bef}\x{0c01}-\x{0c03}\x{0c3e}-\x{0c44}\x{0c46}-\x{0c48}\x{0c4a}-\x{0c4d}\x{0c55}\x{0c56}\x{0c62}\x{0c63}\x{0c66}-\x{0c6f}\x{0c82}\x{0c83}\x{0cbc}\x{0cbe}-\x{0cc4}\x{0cc6}-\x{0cc8}\x{0cca}-\x{0ccd}\x{0cd5}\x{0cd6}\x{0ce2}\x{0ce3}\x{0ce6}-\x{0cef}\x{0d02}\x{0d03}\x{0d3e}-\x{0d44}\x{0d46}-\x{0d48}\x{0d4a}-\x{0d4d}\x{0d57}\x{0d62}\x{0d63}\x{0d66}-\x{0d6f}\x{0d82}\x{0d83}\x{0dca}\x{0dcf}-\x{0dd4}\x{0dd6}\x{0dd8}-\x{0ddf}\x{0df2}\x{0df3}\x{0e31}\x{0e34}-\x{0e3a}\x{0e47}-\x{0e4e}\x{0e50}-\x{0e59}\x{0eb1}\x{0eb4}-\x{0eb9}\x{0ebb}\x{0ebc}\x{0ec8}-\x{0ecd}\x{0ed0}-\x{0ed9}\x{0f18}\x{0f19}\x{0f20}-\x{0f29}\x{0f35}\x{0f37}\x{0f39}\x{0f3e}\x{0f3f}\x{0f71}-\x{0f84}\x{0f86}\x{0f87}\x{0f8d}-\x{0f97}\x{0f99}-\x{0fbc}\x{0fc6}\x{102b}-\x{103e}\x{1040}-\x{1049}\x{1056}-\x{1059}\x{105e}-\x{1060}\x{1062}-\x{1064}\x{1067}-\x{106d}\x{1071}-\x{1074}\x{1082}-\x{108d}\x{108f}-\x{109d}\x{135d}-\x{135f}\x{1712}-\x{1714}\x{1732}-\x{1734}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17d3}\x{17dd}\x{17e0}-\x{17e9}\x{180b}-\x{180d}\x{1810}-\x{1819}\x{18a9}\x{1920}-\x{192b}\x{1930}-\x{193b}\x{1946}-\x{194f}\x{19b0}-\x{19c0}\x{19c8}\x{19c9}\x{19d0}-\x{19d9}\x{1a17}-\x{1a1b}\x{1a55}-\x{1a5e}\x{1a60}-\x{1a7c}\x{1a7f}-\x{1a89}\x{1a90}-\x{1a99}\x{1b00}-\x{1b04}\x{1b34}-\x{1b44}\x{1b50}-\x{1b59}\x{1b6b}-\x{1b73}\x{1b80}-\x{1b82}\x{1ba1}-\x{1bad}\x{1bb0}-\x{1bb9}\x{1be6}-\x{1bf3}\x{1c24}-\x{1c37}\x{1c40}-\x{1c49}\x{1c50}-\x{1c59}\x{1cd0}-\x{1cd2}\x{1cd4}-\x{1ce8}\x{1ced}\x{1cf2}-\x{1cf4}\x{1dc0}-\x{1de6}\x{1dfc}-\x{1dff}\x{200c}\x{200d}\x{203f}\x{2040}\x{2054}\x{20d0}-\x{20dc}\x{20e1}\x{20e5}-\x{20f0}\x{2cef}-\x{2cf1}\x{2d7f}\x{2de0}-\x{2dff}\x{302a}-\x{302f}\x{3099}\x{309a}\x{a620}-\x{a629}\x{a66f}\x{a674}-\x{a67d}\x{a69f}\x{a6f0}\x{a6f1}\x{a802}\x{a806}\x{a80b}\x{a823}-\x{a827}\x{a880}\x{a881}\x{a8b4}-\x{a8c4}\x{a8d0}-\x{a8d9}\x{a8e0}-\x{a8f1}\x{a900}-\x{a909}\x{a926}-\x{a92d}\x{a947}-\x{a953}\x{a980}-\x{a983}\x{a9b3}-\x{a9c0}\x{a9d0}-\x{a9d9}\x{aa29}-\x{aa36}\x{aa43}\x{aa4c}\x{aa4d}\x{aa50}-\x{aa59}\x{aa7b}\x{aab0}\x{aab2}-\x{aab4}\x{aab7}\x{aab8}\x{aabe}\x{aabf}\x{aac1}\x{aaeb}-\x{aaef}\x{aaf5}\x{aaf6}\x{abe3}-\x{abea}\x{abec}\x{abed}\x{abf0}-\x{abf9}\x{fb1e}\x{fe00}-\x{fe0f}\x{fe20}-\x{fe26}\x{fe33}\x{fe34}\x{fe4d}-\x{fe4f}\x{ff10}-\x{ff19}\x{ff3f}]*\b';

    /**
     * Full list of JavaScript reserved words.
     * Will be loaded from /data/js/keywords_reserved.txt.
     *
     * @see https://mathiasbynens.be/notes/reserved-keywords
     *
     * @var string[]
     */
    protected $keywordsReserved = array();

    /**
     * List of JavaScript reserved words that accept a <variable, value, ...>
     * after them. Some end of lines are not the end of a statement, like with
     * these keywords.
     *
     * E.g.: we shouldn't insert a ; after this else
     * else
     *     console.log('this is quite fine')
     *
     * Will be loaded from /data/js/keywords_before.txt
     *
     * @var string[]
     */
    protected $keywordsBefore = array();

    /**
     * List of JavaScript reserved words that accept a <variable, value, ...>
     * before them. Some end of lines are not the end of a statement, like when
     * continued by one of these keywords on the newline.
     *
     * E.g.: we shouldn't insert a ; before this instanceof
     * variable
     *     instanceof String
     *
     * Will be loaded from /data/js/keywords_after.txt
     *
     * @var string[]
     */
    protected $keywordsAfter = array();

    /**
     * List of all JavaScript operators.
     *
     * Will be loaded from /data/js/operators.txt
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Expressions_and_Operators
     *
     * @var string[]
     */
    protected $operators = array();

    /**
     * List of JavaScript operators that accept a <variable, value, ...> after
     * them. Some end of lines are not the end of a statement, like with these
     * operators.
     *
     * Note: Most operators are fine, we've only removed ++ and --.
     * ++ & -- have to be joined with the value they're in-/decrementing.
     *
     * Will be loaded from /data/js/operators_before.txt
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Expressions_and_Operators
     *
     * @var string[]
     */
    protected $operatorsBefore = array();

    /**
     * List of JavaScript operators that accept a <variable, value, ...> before
     * them. Some end of lines are not the end of a statement, like when
     * continued by one of these operators on the newline.
     *
     * Note: Most operators are fine, we've only removed ), ], ++, --, ! and ~.
     * There can't be a newline separating ! or ~ and whatever it is negating.
     * ++ & -- have to be joined with the value they're in-/decrementing.
     * ) & ] are "special" in that they have lots or usecases. () for example
     * is used for function calls, for grouping, in if () and for (), ...
     *
     * Will be loaded from /data/js/operators_after.txt
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Expressions_and_Operators
     *
     * @var string[]
     */
    protected $operatorsAfter = array();

    public function __construct()
    {
        call_user_func_array(array('\\MatthiasMullie\Minify\\Minify', '__construct'), func_get_args());

        // Adjusted from the original '/../data/js/' for this project's flat vendor layout (no src/ subdirectory).
        $dataDir = __DIR__ . '/data/js/';
        $options = FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES;
        $this->keywordsReserved = file($dataDir . 'keywords_reserved.txt', $options);
        $this->keywordsBefore = file($dataDir . 'keywords_before.txt', $options);
        $this->keywordsAfter = file($dataDir . 'keywords_after.txt', $options);
        $this->operators = file($dataDir . 'operators.txt', $options);
        $this->operatorsBefore = file($dataDir . 'operators_before.txt', $options);
        $this->operatorsAfter = file($dataDir . 'operators_after.txt', $options);
    }

    /**
     * Minify the data.
     * Perform JS optimizations.
     *
     * @param string[optional] $path Path to write the data to
     *
     * @return string The minified data
     */
    public function execute($path = null)
    {
        $content = '';

        /*
         * Let's first take out strings, comments and regular expressions.
         * All of these can contain JS code-like characters, and we should make
         * sure any further magic ignores anything inside of these.
         *
         * Consider this example, where we should not strip any whitespace:
         * var str = "a   test";
         *
         * Comments will be removed altogether, strings and regular expressions
         * will be replaced by placeholder text, which we'll restore later.
         */
        // Backticks are handled separately, as their own pre-pass: a
        // template literal's `${...}` can contain another complete, nested
        // template literal (with its own `${...}`, to any depth), so the
        // plain "up to the next matching quote" approach extractStrings()
        // uses for '/" would stop at the wrong backtick. It has to run as a
        // plain PHP scan rather than a registerPattern() callback, because
        // that callback only ever sees what its own regex matched, with no
        // way to look ahead for a nested template's true end.
        $this->extractStrings('\'"');
        $this->stripComments();
        $this->extractRegex();

        // loop files
        foreach ($this->data as $source => $js) {
            $js = $this->extractTemplateLiterals($js);

            // take out strings, comments & regex (for which we've registered
            // the regexes just a few lines earlier)
            $js = $this->replace($js);

            // A regex right after any block statement's "}" is ambiguous
            // the same way one after any other bare ")" is - but unlike
            // extractRegex()'s condition-agnostic "only followed by
            // .test()/.exec()/etc." check for that general case, a block's
            // own expression statement needs no particular follow-up at all
            // to be valid. Runs after replace(): with strings/comments/
            // other regexes already placeholdered by then, classifying
            // each "{" is a plain forward scan.
            $js = $this->protectBlockStatementRegex($js);

            $js = $this->propertyNotation($js);
            $js = $this->stripWhitespace($js);

            // combine js: separating the scripts by a ;
            $content .= $js . ';';
        }

        // clean up leftover `;`s from the combination of multiple scripts
        $content = ltrim($content, ';');
        $content = (string) substr($content, 0, -1);

        /*
         * Earlier, we extracted strings & regular expressions and replaced them
         * with placeholder text. This will restore them.
         */
        $content = $this->restoreExtractedData($content);

        return $content;
    }

    /**
     * Strip comments from source code.
     */
    protected function stripComments()
    {
        $this->stripMultilineComments();

        // Single-line comments, stopped explicitly at any ECMAScript
        // LineTerminator (LF, CR, U+2028, U+2029): "." with "$/m" only
        // recognizes "\n", so a comment containing U+2028/U+2029 would
        // otherwise keep matching straight through it and swallow
        // arbitrarily more of the following, real code as "comment". U+2028
        // and U+2029 are matched by their fixed UTF-8 byte sequences rather
        // than "/u" mode, which would error out on non-UTF-8 source.
        $this->registerPattern('/\/\/(?:(?!\r|\n|\xE2\x80\xA8|\xE2\x80\xA9).)*+/', '');
    }

    /**
     * Find and placeholder every template literal in $content, recursively
     * accounting for a nested template literal (and its own
     * strings/comments/braces/regexes) inside a `${...}` interpolation, to
     * any depth.
     *
     * This runs as a plain character scan rather than a single PCRE pattern
     * (as an earlier version of this did): disambiguating a bare "/" inside
     * an interpolation as division or the start of a regex literal needs
     * the same kind of "what came before it" context extractRegex() tracks
     * for top-level code, including its "regex after a bare ')' only if
     * followed by .test(/.exec(/etc." special case - and PCRE lookbehind
     * has no way to express an unbounded version of that (a bounded one,
     * tried initially, silently misjudges anything past the bound, and
     * bounded-but-nonzero-width lookbehind isn't even accepted by the PCRE2
     * version PHP 8.0-8.3 - still within this project's supported range -
     * ship with).
     *
     * @param string $content
     * @return string
     */
    protected function extractTemplateLiterals($content)
    {
        $length = strlen($content);
        $result = '';
        $offset = 0;
        $last_char = '';
        $last_word = '';

        while ($offset < $length) {
            $character = $content[$offset];

            if ($character === '`') {
                $end = $this->findTemplateLiteralEnd($content, $offset + 1, $length);
                $text = substr($content, $offset, $end - $offset);
                $count = count($this->extracted);
                $placeholder = '`' . $count . '`';
                $this->extracted[$placeholder] = $text;
                $result .= $placeholder;
                $offset = $end;
                $last_char = '`';
                $last_word = '';
                continue;
            }

            $new_offset = $this->skipJsToken($content, $offset, $length, $last_char, $last_word);
            $result .= substr($content, $offset, $new_offset - $offset);
            $offset = $new_offset;
        }

        return $result;
    }

    /**
     * @param string $content
     * @param int $offset Position right after the opening backtick
     * @param int $length
     * @return int Position right after the matching closing backtick
     */
    private function findTemplateLiteralEnd($content, $offset, $length)
    {
        while ($offset < $length) {
            $character = $content[$offset];

            if ($character === '\\') {
                $offset += 2;
                continue;
            }

            if ($character === '`') {
                return $offset + 1;
            }

            if ($character === '$' && $offset + 1 < $length && $content[$offset + 1] === '{') {
                $offset = $this->skipTemplateExpression($content, $offset + 2, $length);
                continue;
            }

            $offset++;
        }

        return $length;
    }

    /**
     * @param string $content
     * @param int $offset Position right after "${"
     * @param int $length
     * @return int Position right after the matching "}"
     */
    private function skipTemplateExpression($content, $offset, $length)
    {
        $depth = 0;
        $last_char = '{';
        $last_word = '';

        while ($offset < $length) {
            $character = $content[$offset];

            if ($character === '`') {
                $offset = $this->findTemplateLiteralEnd($content, $offset + 1, $length);
                $last_char = '`';
                $last_word = '';
                continue;
            }

            if ($character === '{') {
                $depth++;
                $offset++;
                $last_char = '{';
                $last_word = '';
                continue;
            }

            if ($character === '}') {
                if ($depth === 0) {
                    return $offset + 1;
                }
                $depth--;
                $offset++;
                $last_char = '}';
                $last_word = '';
                continue;
            }

            $offset = $this->skipJsToken($content, $offset, $length, $last_char, $last_word);
        }

        return $length;
    }

    /**
     * Skip one whitespace run, string, comment, regex literal, or ordinary
     * character/identifier, tracking the last significant character/word
     * seen so a subsequent "/" can be told apart from division.
     *
     * @param string $content
     * @param int $offset
     * @param int $length
     * @param string $last_char
     * @param string $last_word
     * @return int Position right after the skipped token
     */
    private function skipJsToken($content, $offset, $length, &$last_char, &$last_word)
    {
        $character = $content[$offset];

        $whitespace_length = $this->matchJsWhitespace($content, $offset, $length);
        if ($whitespace_length > 0) {
            return $offset + $whitespace_length;
        }

        if ($character === "'" || $character === '"') {
            $quote = $character;
            $offset++;
            while ($offset < $length && $content[$offset] !== $quote) {
                $offset += ($content[$offset] === '\\') ? 2 : 1;
            }
            $offset++;
            $last_char = $quote;
            $last_word = '';

            return $offset;
        }

        if ($character === '/' && $offset + 1 < $length && $content[$offset + 1] === '/') {
            $offset += 2;
            while ($offset < $length
                && $content[$offset] !== "\n"
                && $content[$offset] !== "\r"
                && substr($content, $offset, 3) !== "\xE2\x80\xA8"
                && substr($content, $offset, 3) !== "\xE2\x80\xA9"
            ) {
                $offset++;
            }

            return $offset;
        }

        if ($character === '/' && $offset + 1 < $length && $content[$offset + 1] === '*') {
            $end = strpos($content, '*/', $offset + 2);

            return ($end === false) ? $length : $end + 2;
        }

        if ($character === '/') {
            $regex_end = $this->tryMatchTemplateRegex($content, $offset, $length, $last_char, $last_word);
            $last_char = '/';
            $last_word = '';

            return ($regex_end !== false) ? $regex_end : $offset + 1;
        }

        $identifier_char_length = $this->matchJsIdentifierChar($content, $offset, $length);
        if ($identifier_char_length > 0) {
            $word_start = $offset;
            while ($identifier_char_length > 0) {
                $offset += $identifier_char_length;
                $identifier_char_length = ($offset < $length) ? $this->matchJsIdentifierChar($content, $offset, $length) : 0;
            }
            $word = substr($content, $word_start, $offset - $word_start);

            // "break"/"continue" can only be followed by nothing, or - on
            // the same line only, since a LineTerminator here forces ASI
            // right at the keyword instead - a label identifier; nothing
            // else can follow within the same statement. Consuming that
            // label as part of this same token (rather than letting it
            // overwrite $last_word with the label text on the next call)
            // keeps a "/" right after it reading as regex-eligible too:
            // "break outer\n/re/" is a fresh statement exactly like bare
            // "break\n/re/" is.
            if ($word === 'break' || $word === 'continue') {
                $label_offset = $offset;
                while ($label_offset < $length && ($content[$label_offset] === ' ' || $content[$label_offset] === "\t")) {
                    $label_offset++;
                }
                $label_char_length = ($label_offset < $length) ? $this->matchJsIdentifierChar($content, $label_offset, $length) : 0;
                if ($label_char_length > 0 && !($content[$label_offset] >= '0' && $content[$label_offset] <= '9')) {
                    $label_end = $label_offset;
                    while ($label_char_length > 0) {
                        $label_end += $label_char_length;
                        $label_char_length = ($label_end < $length) ? $this->matchJsIdentifierChar($content, $label_end, $length) : 0;
                    }
                    $offset = $label_end;
                }
            }

            $last_word = $word;
            $last_char = '';

            return $offset;
        }

        // "++"/"--" (prefix or postfix) is not the same as a standalone "+"/
        // "-": neither can be followed directly by a regex in valid JS (postfix
        // just produced a value, so what follows divides it instead; prefix
        // needs an assignable operand next, never a regex literal), unlike a
        // lone "+"/"-", which is a normal operator a regex can follow. Track
        // it as a non-triggering word so a following "/" reads as division.
        if (($character === '+' || $character === '-') && $offset + 1 < $length && $content[$offset + 1] === $character) {
            $last_word = $character . $character;
            $last_char = '';

            return $offset + 2;
        }

        // "=>": tracked in addition to $last_char (still set below exactly
        // as any other "=" would be, so the existing "before a regex"
        // character check is unaffected) so protectBlockStatementRegex()
        // can tell this was specifically the arrow token, not just a bare
        // "=" or ">" - its own body is unconditionally a block, never an
        // object literal (which needs "=> ({...})" instead), unlike every
        // other position that character check treats as value-expected.
        if ($character === '=' && $offset + 1 < $length && $content[$offset + 1] === '>') {
            $last_char = '>';
            $last_word = '=>';

            return $offset + 2;
        }

        $last_char = $character;
        $last_word = '';

        return $offset + 1;
    }

    /**
     * Byte length of one identifier character at $offset, or 0 if there
     * isn't one - covering ASCII word characters, a raw multi-byte UTF-8
     * identifier character (Unicode letters like "π" are common in real
     * source; matchJsWhitespace() already claims the Unicode whitespace/
     * separator code points it recognizes before this is ever reached, so
     * treating any other non-ASCII byte as "part of the word" - rather
     * than needing a full ID_Start/ID_Continue table - is wrong only for
     * the rare case of non-ASCII punctuation used somewhere an identifier
     * character is expected, which isn't standard JS to begin with), and
     * a "\uXXXX"/"\u{X...}" Unicode escape (identifiers may spell any of
     * their characters this way - "π" in an identifier position
     * means the same thing as a literal "π").
     *
     * @param string $content
     * @param int $offset
     * @param int $length
     * @return int
     */
    private function matchJsIdentifierChar($content, $offset, $length)
    {
        $character = $content[$offset];

        if (($character >= 'a' && $character <= 'z')
            || ($character >= 'A' && $character <= 'Z')
            || ($character >= '0' && $character <= '9')
            || $character === '_' || $character === '$'
        ) {
            return 1;
        }

        if ($character >= "\x80") {
            return 1;
        }

        if ($character === '\\' && $offset + 1 < $length && $content[$offset + 1] === 'u') {
            if ($offset + 2 < $length && $content[$offset + 2] === '{') {
                $end = strpos($content, '}', $offset + 3);
                if ($end !== false && $end > $offset + 3) {
                    return $end + 1 - $offset;
                }

                return 0;
            }

            if ($offset + 5 < $length && ctype_xdigit(substr($content, $offset + 2, 4))) {
                return 6;
            }
        }

        return 0;
    }

    /**
     * ECMAScript WhiteSpace/LineTerminator beyond plain space/tab/LF/CR:
     * NBSP (U+00A0), the other Unicode Zs space separators, LS/PS
     * (U+2028/U+2029), and ZWNBSP (U+FEFF). Left unrecognized, one of these
     * between an operator/keyword and a following "/" would be treated as
     * an ordinary character, overwriting the "last significant token" and
     * making a regex there look like division instead.
     *
     * @param string $content
     * @param int $offset
     * @param int $length
     * @return int Byte length of the whitespace character at $offset, or 0 if there isn't one
     */
    private function matchJsWhitespace($content, $offset, $length)
    {
        $character = $content[$offset];

        if ($character === ' ' || $character === "\t" || $character === "\n" || $character === "\r"
            || $character === "\x0B" || $character === "\x0C"
        ) {
            return 1;
        }

        if ($character === "\xC2" && $offset + 1 < $length && $content[$offset + 1] === "\xA0") {
            return 2;
        }

        if ($character === "\xE1" && substr($content, $offset, 3) === "\xE1\x9A\x80") {
            return 3;
        }

        if ($character === "\xE2" && $offset + 2 < $length) {
            $third = $content[$offset + 2];
            if ($content[$offset + 1] === "\x80"
                && (($third >= "\x80" && $third <= "\x8A") || $third === "\xA8" || $third === "\xA9" || $third === "\xAF")
            ) {
                return 3;
            }
            if ($content[$offset + 1] === "\x81" && $third === "\x9F") {
                return 3;
            }
        }

        if ($character === "\xE3" && substr($content, $offset, 3) === "\xE3\x80\x80") {
            return 3;
        }

        if ($character === "\xEF" && substr($content, $offset, 3) === "\xEF\xBB\xBF") {
            return 3;
        }

        return 0;
    }

    /**
     * The canonical list of keywords after which an expression (rather
     * than a value having just been produced) is expected: the shared
     * source both extractRegex()'s "before" pattern and
     * canPrecedeExpression()'s procedural check are built from, so the
     * two can't drift apart into two different lists of the same thing.
     *
     * "break"/"continue"/"debugger" are unconditional triggers, unlike
     * the rest of this list, which reads as division just as often as
     * regex: none of these three ever takes an expression operand at
     * all (break/continue take only an optional label, debugger takes
     * nothing), so any of them directly followed by "/" isn't valid JS
     * to begin with - the only way that combination occurs in real code
     * is across an ASI line break, where "/" necessarily starts a new
     * statement.
     *
     * @return string[]
     */
    private function regexTriggerKeywords()
    {
        static $keywords = array(
            'do', 'in', 'new', 'else', 'throw', 'yield', 'delete', 'return', 'typeof',
            'case', 'await', 'void', 'default', 'instanceof', 'of',
            'break', 'continue', 'debugger',
        );

        return $keywords;
    }

    /**
     * The subset of regexTriggerKeywords() that precedes a STATEMENT (or,
     * for break/continue/debugger, nothing at all - a complete statement
     * on its own) rather than an EXPRESSION: "do"/"else" introduce their
     * own body as a statement, and "break"/"continue"/"debugger" never
     * take an expression operand (only break/continue's optional label).
     * A "/" right after any of these is still safely regex-eligible (a
     * statement can itself be an expression-statement starting with a
     * regex), but a "{" right after one of these is never an object
     * literal, for the same reason a statement-position "{" never is -
     * unlike the rest of regexTriggerKeywords(), which precedes a genuine
     * expression, where "{" legitimately can be an object/class literal.
     *
     * @return string[]
     */
    private function statementOnlyKeywords()
    {
        static $keywords = array('do', 'else', 'break', 'continue', 'debugger');

        return $keywords;
    }

    /**
     * Whether an expression - rather than a value having just been
     * produced - is expected right after $last_char/$last_word: the
     * "before" context extractRegex()/tryMatchTemplateRegex() use to
     * decide whether a "/" opens a regex (division otherwise).
     *
     * Not used for "{" classification (see precedesValueBrace() instead):
     * a handful of these same triggers - "{", ";", ":" among the
     * characters, "do"/"else"/"break"/"continue"/"debugger" among the
     * keywords - correctly mean "an expression may follow" for "/", since
     * a fresh statement can itself be an expression-statement, but do NOT
     * mean "the following '{' is a value": ECMAScript's grammar reserves
     * a statement-position "{" for a block unconditionally, regardless of
     * whether the statement in question could also have been an
     * expression.
     *
     * @param string $last_char
     * @param string $last_word
     * @return bool
     */
    private function canPrecedeExpression($last_char, $last_word)
    {
        static $beforeChars = '=:,;+-*/%^~<>?({[&|!';

        $atStart = ($last_char === '' && $last_word === '');

        return $atStart
            || ($last_char !== '' && strpos($beforeChars, $last_char) !== false)
            || ($last_word !== '' && in_array($last_word, $this->regexTriggerKeywords(), true));
    }

    /**
     * Whether a "{" right after $last_char/$last_word is a value (object/
     * class literal or destructuring pattern) rather than a block
     * statement - protectBlockStatementRegex()'s classification, kept
     * separate from canPrecedeExpression() because the two questions
     * aren't the same one (see that method's docblock).
     *
     * $beforeChars here drops "{", ";", and ":" from canPrecedeExpression()'s
     * set: right after any of these, ECMAScript still expects a STATEMENT
     * (not necessarily an expression), and a statement-position "{" is
     * unconditionally a block - covering a block nested directly inside
     * another block ("{{ }...}"), a standalone block following a prior
     * statement ("foo(); {}..."), and a block as a switch-case's or a
     * labeled statement's own body ("case 1: {}...", "label: {}...").
     * Programstart/right-after-a-block (empty $last_char/$last_word) is
     * excluded the same way, for the same reason.
     *
     * This necessarily leaves one narrow case unresolved: ":" is also how
     * a ternary or an object literal's key introduces its (genuinely
     * value-expected) branch/value, e.g. "cond ? {} : {}" or "{a: {}}" -
     * excluding ":" here means those inner braces are misclassified as
     * blocks too. That's harmless unless something that depends on the
     * resulting state - a "/" - directly follows with nothing else
     * (itself resetting the state first) in between, which in practice
     * only happens for a value produced by a ternary/object-key branch
     * immediately divided or compared right there with no intervening
     * token - a vanishingly rare construct left as a residual gap rather
     * than adding the ternary-pairing/object-key-vs-label state tracking
     * it would take to fully disambiguate ":".
     *
     * @param string $last_char
     * @param string $last_word
     * @return bool
     */
    private function precedesValueBrace($last_char, $last_word)
    {
        static $beforeChars = '=,+-*/%^~<>?([&|!';

        if ($last_char === '' && $last_word === '') {
            return false;
        }
        if ($last_word === '=>' || in_array($last_word, $this->statementOnlyKeywords(), true)) {
            return false;
        }

        return ($last_char !== '' && strpos($beforeChars, $last_char) !== false)
            || ($last_word !== '' && in_array($last_word, $this->regexTriggerKeywords(), true));
    }

    /**
     * Mirrors extractRegex()'s "before" context (operator/opening
     * bracket/keyword, or a bare ")" followed by a RegExp property/method
     * access) to decide whether "/" opens a regex literal here: a value
     * (identifier, number, string, ")", "]", ...) immediately before it
     * means division instead, and division can't immediately follow any of
     * these contexts in valid JS, so a false-positive match here - one that
     * could swallow past an unrelated "}" that should have closed the
     * enclosing "${...}" - isn't possible for that part of the check. "}"
     * itself isn't in $beforeChars: an object/class literal used as a value
     * ("{a:1} / 2") ends in "}" exactly like a block statement does, and
     * $last_char is just the one character, with no way to tell which kind
     * of "}" this was. The regex body itself is still allowed to contain
     * "{"/"}" (e.g. a quantifier like "{2,4}", or the literal "}" in /}/)
     * without affecting the interpolation's own brace depth, same as
     * extractRegex()'s - only the "before" context is affected.
     *
     * @param string $content
     * @param int $offset
     * @param int $length
     * @param string $last_char
     * @param string $last_word
     * @return int|false Position right after the regex literal, or false if "/" isn't one here
     */
    private function tryMatchTemplateRegex($content, $offset, $length, $last_char, $last_word)
    {
        static $methods = array(
            'constructor', 'flags', 'global', 'ignoreCase', 'multiline', 'source', 'sticky', 'unicode',
            'compile(', 'exec(', 'test(', 'toSource(', 'toString(',
        );

        $canPrecedeRegex = $this->canPrecedeExpression($last_char, $last_word);
        $afterCloseParen = ($last_char === ')');

        if (!$canPrecedeRegex && !$afterCloseParen) {
            return false;
        }

        if (!preg_match(
            '/\\/(?!\\*)(?:[^\\[\\/\\\\\n\r]++|(?:\\\\.)++|(?:\\[(?:[^\\]\\\\\n\r]++|(?:\\\\.)++)++\\])++)++\\/[dgimsuvy]*/A',
            $content,
            $matches,
            0,
            $offset
        )) {
            return false;
        }

        $end = $offset + strlen($matches[0]);

        if ($afterCloseParen && !$canPrecedeRegex) {
            $quoted = array_map(function ($method) {
                return preg_quote($method, '/');
            }, $methods);
            if (!preg_match('/^\s*\.(?:' . implode('|', $quoted) . ')/', substr($content, $end))) {
                return false;
            }
        }

        return $end;
    }

    /**
     * A "{" can only be a genuine object/class literal or destructuring
     * pattern where an expression is expected - precedesValueBrace()'s
     * check. Everywhere else - the start of a statement - ECMAScript's
     * own grammar reserves "{" there for a block exclusively (an
     * ExpressionStatement can never begin with one), so a block's
     * matching "}" is always followed by a fresh statement, where a regex
     * is valid the same way it is right after any other value-producing
     * ")" - covering not just if/while/for/with/else, but every block:
     * function/method/class bodies, try/catch/finally, switch, and
     * standalone blocks alike, without enumerating each one. The same
     * fresh-statement reasoning also covers a restricted-production
     * keyword (and, for break/continue, its label - skipJsToken() already
     * folds that into $last_word) directly followed by "/": since
     * $last_word tracks that correctly on its own, this only needs to act
     * on it - not track it separately.
     *
     * A "(" gets the same "condition vs. ordinary" distinction for the
     * same reason: if/while/with/for('s own, not a nested one)/for-await
     * are the only statements whose body can be a bare, non-block
     * statement directly - "if (x) /re/;" is valid without braces - so
     * their condition's matching ")" must be treated as a fresh statement
     * boundary too, same as a block's "}", rather than the ordinary
     * "value produced, ambiguous without a qualifying follow-up" ")"
     * tryMatchTemplateRegex() otherwise applies (built for the general
     * "some function call or grouping just closed" case, where that
     * caution is warranted - a bare ")" alone doesn't reveal which kind
     * this was). switch/catch aren't included: both require an explicit
     * "{...}" body, so there's no direct-regex-body case for their own
     * condition/parameter list to protect.
     *
     * Two corrections on top of precedesValueBrace()'s own "before" check
     * are handled by that method itself: "do"/"else"/"break"/"continue"/
     * "debugger" always introduce their own body as a statement (or, for
     * the latter three, are already a complete statement with no
     * expression operand at all), and an arrow function's "() => {...}"
     * is unconditionally its body block - never an object literal, which
     * needs "=> ({...})" instead - even though all of these would
     * otherwise read as value-expected via the character/keyword check
     * alone.
     *
     * Extraction happens inline, immediately upon recognizing a regex,
     * rather than in a separate pass over collected positions: unlike
     * skipJsToken()'s own "/" handling (shared with callers that only
     * need to skip past a regex without extracting it, copying its raw
     * text through as-is), every regex found here must actually be
     * placeholdered, since this is the only pass that will ever see these
     * particular positions as expression-start context. Must run after
     * strings/comments/other regexes are already placeholdered: it
     * doesn't re-derive their skipping logic beyond what skipJsToken()
     * already provides for anything other than "{"/"}"/"("/")"/"/".
     *
     * @param string $content
     * @return string
     */
    protected function protectBlockStatementRegex($content)
    {
        static $conditionKeywords = array('if', 'while', 'with', 'for');

        $length = strlen($content);
        $offset = 0;
        $last_char = '';
        $last_word = '';
        $word_before_last = '';
        $brace_kinds = array();
        $paren_kinds = array();

        while ($offset < $length) {
            $character = $content[$offset];

            if ($character === '{') {
                $is_value = $this->precedesValueBrace($last_char, $last_word);
                $brace_kinds[] = $is_value ? 'value' : 'block';
                $offset++;
                $last_char = '{';
                $last_word = '';
                continue;
            }

            if ($character === '}') {
                $kind = array_pop($brace_kinds);
                $offset++;
                // Right after a block statement is a fresh statement
                // boundary, not "a value was just produced" - empty
                // $last_char/$last_word simulate that, matching how
                // canPrecedeExpression() treats start-of-expression.
                $last_char = ($kind === 'block') ? '' : '}';
                $last_word = '';
                continue;
            }

            if ($character === '(') {
                $is_condition = in_array($last_word, $conditionKeywords, true)
                    || ($last_word === 'await' && $word_before_last === 'for');
                $paren_kinds[] = $is_condition ? 'condition' : 'other';
                $offset++;
                $last_char = '(';
                $last_word = '';
                continue;
            }

            if ($character === ')') {
                $kind = array_pop($paren_kinds);
                $offset++;
                $last_char = ($kind === 'condition') ? '' : ')';
                $last_word = '';
                continue;
            }

            if ($character === '/' && !($offset + 1 < $length && ($content[$offset + 1] === '/' || $content[$offset + 1] === '*'))) {
                $regex_end = $this->tryMatchTemplateRegex($content, $offset, $length, $last_char, $last_word);
                if ($regex_end !== false) {
                    $count = count($this->extracted);
                    $placeholder = '"' . $count . '"';
                    $this->extracted[$placeholder] = substr($content, $offset, $regex_end - $offset);
                    $content = substr_replace($content, $placeholder, $offset, $regex_end - $offset);
                    $length = strlen($content);
                    $offset += strlen($placeholder);
                    $last_char = '"';
                    $last_word = '';
                    continue;
                }
            }

            if ($this->matchJsIdentifierChar($content, $offset, $length) > 0) {
                $word_before_last = $last_word;
            }
            $offset = $this->skipJsToken($content, $offset, $length, $last_char, $last_word);
        }

        return $content;
    }

    /**
     * JS can have /-delimited regular expressions, like: /ab+c/.match(string).
     *
     * The content inside the regex can contain characters that may be confused
     * for JS code: e.g. it could contain whitespace it needs to match & we
     * don't want to strip whitespace in there.
     *
     * The regex can be pretty simple: we don't have to care about comments,
     * (which also use slashes) because stripComments() will have stripped those
     * already.
     *
     * This method will replace all string content with simple REGEX#
     * placeholder text, so we've rid all regular expressions from characters
     * that may be misinterpreted. Original regex content will be saved in
     * $this->extracted and after doing all other minifying, we can restore the
     * original content via restoreRegex()
     */
    protected function extractRegex()
    {
        // PHP only supports $this inside anonymous functions since 5.4
        $minifier = $this;
        $callback = function ($match) use ($minifier) {
            $count = count($minifier->extracted);
            $placeholder = '"' . $count . '"';
            $minifier->extracted[$placeholder] = $match[0];

            return $placeholder;
        };

        // match all chars except `/` and `\`
        // `\` is allowed though, along with whatever char follows (which is the
        // one being escaped)
        // this should allow all chars, except for an unescaped `/` (= the one
        // closing the regex)
        // then also ignore bare `/` inside `[]`, where they don't need to be
        // escaped: anything inside `[]` can be ignored safely
        $pattern = '\\/(?!\*)(?:[^\\[\\/\\\\\n\r]++|(?:\\\\.)++|(?:\\[(?:[^\\]\\\\\n\r]++|(?:\\\\.)++)++\\])++)++\\/[dgimsuvy]*';

        // A "/" can only start a regex where an expression is expected: right
        // after an operator/opening-bracket punctuator or one of these
        // keywords. A value (identifier, number, string, ")", "]", ...)
        // immediately before it means division instead. "=>" and "<="/">="/
        // "=="/"===" etc. are covered via their last character ("<"/">"/"=").
        // "+"/"-" only count as a trigger when the run of consecutive "+"s
        // (or "-"s) ending here has odd length: JS lexes greedy pairs, so
        // "a++" is one postfix token (division follows), but "a+++" is
        // postfix "++" followed by a separate, standalone "+" (a regex can
        // follow that one) - looking at just the single last character
        // can't tell those apart, and neither can excluding every run
        // longer than one, since that also wrongly excludes "+++". Bounded
        // to runs up to 7 characters, far beyond anything realistic code
        // would have. "}" is excluded entirely: an object/class literal
        // used as a value ("{a:1} / 2") ends in "}" exactly like a block
        // statement does, and telling those apart would need tracking
        // every "{"'s own kind, not just the single character before "/".
        // "break"/"continue"/"debugger" are unconditional triggers - see
        // regexTriggerKeywords()'s docblock for why - unlike the rest of
        // this list, which reads as division just as often as regex.
        $keywords = $this->regexTriggerKeywords();
        $before = '(^|(?<!\+)\+(?:\+\+){0,3}(?!\+)|(?<!-)-(?:--){0,3}(?!-)|[=:,;\*\/%\^~<>\?\(\{\[&\|!]|' . implode('|', $keywords) . ')\s*';
        $propertiesAndMethods = array(
            // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp#Properties_2
            'constructor',
            'flags',
            'global',
            'ignoreCase',
            'multiline',
            'source',
            'sticky',
            'unicode',
            // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp#Methods_2
            'compile(',
            'exec(',
            'test(',
            'toSource(',
            'toString(',
        );
        $delimiters = array_fill(0, count($propertiesAndMethods), '/');
        $propertiesAndMethods = array_map('preg_quote', $propertiesAndMethods, $delimiters);
        // No "after" restriction: division can't immediately follow $before's
        // context (it always needs a left operand), so once that context is
        // confirmed, whatever comes after the regex - "]", "?", "==", the end
        // of an array/ternary/comparison, or anything else - doesn't change
        // that this is a regex literal. An incomplete positive list here
        // previously left regexes in those positions unrecognized.
        $this->registerPattern('/' . $before . '\K' . $pattern . '/', $callback);

        // regular expressions following a `)` are rather annoying to detect...
        // quite often, `/` after `)` is a division operator & if it happens to
        // be followed by another one (or a comment), it is likely to be
        // confused for a regular expression
        // however, it's perfectly possible for a regex to follow a `)`: after
        // a single-line `if()`, `while()`, ... statement, for example
        // since, when they occur like that, they're always the start of a
        // statement, there's only a limited amount of ways they can be useful:
        // by calling the regex methods directly
        // if a regex following `)` is not followed by `.<property or method>`,
        // it's quite likely not a regex
        $before = '\)\s*';
        $after = '(?=\s*\.(' . implode('|', $propertiesAndMethods) . '))';
        $this->registerPattern('/' . $before . '\K' . $pattern . $after . '/', $callback);

        // 1 more edge case: a regex can be followed by a lot more operators or
        // keywords if there's a newline (ASI) in between, where the operator
        // actually starts a new statement
        // (https://github.com/matthiasmullie/minify/issues/56)
        $operators = $this->getOperatorsForRegex($this->operatorsBefore, '/');
        $operators += $this->getOperatorsForRegex($this->keywordsReserved, '/');
        $after = '(?=\s*\n\s*(' . implode('|', $operators) . '))';
        $this->registerPattern('/' . $pattern . $after . '/', $callback);
    }

    /**
     * Strip whitespace.
     *
     * We won't strip *all* whitespace, but as much as possible. The thing that
     * we'll preserve are newlines we're unsure about.
     * JavaScript doesn't require statements to be terminated with a semicolon.
     * It will automatically fix missing semicolons with ASI (automatic semi-
     * colon insertion) at the end of line causing errors (without semicolon.)
     *
     * Because it's sometimes hard to tell if a newline is part of a statement
     * that should be terminated or not, we'll just leave some of them alone.
     *
     * @param string $content The content to strip the whitespace for
     *
     * @return string
     */
    protected function stripWhitespace($content)
    {
        // uniform line endings, make them all line feed
        $content = str_replace(array("\r\n", "\r"), "\n", $content);

        // collapse all non-line feed whitespace into a single space
        $content = preg_replace('/[^\S\n]+/', ' ', $content);

        // strip leading & trailing whitespace
        $content = str_replace(array(" \n", "\n "), "\n", $content);

        // collapse consecutive line feeds into just 1
        $content = preg_replace('/\n+/', "\n", $content);

        $operatorsBefore = $this->getOperatorsForRegex($this->operatorsBefore, '/');
        $operatorsAfter = $this->getOperatorsForRegex($this->operatorsAfter, '/');
        $operators = $this->getOperatorsForRegex($this->operators, '/');
        $keywordsBefore = $this->getKeywordsForRegex($this->keywordsBefore, '/');
        $keywordsAfter = $this->getKeywordsForRegex($this->keywordsAfter, '/');

        // strip whitespace that ends in (or next line begin with) an operator
        // that allows statements to be broken up over multiple lines
        unset($operatorsBefore['+'], $operatorsBefore['-'], $operatorsAfter['+'], $operatorsAfter['-']);
        $content = preg_replace(
            array(
                '/(' . implode('|', $operatorsBefore) . ')\s+/',
                '/\s+(' . implode('|', $operatorsAfter) . ')/',
            ),
            '\\1',
            $content
        );

        // make sure + and - can't be mistaken for, or joined into ++ and --
        $content = preg_replace(
            array(
                '/(?<![\+\-])\s*([\+\-])(?![\+\-])/',
                '/(?<![\+\-])([\+\-])\s*(?![\+\-])/',
            ),
            '\\1',
            $content
        );

        // collapse whitespace around reserved words into single space
        $content = preg_replace('/(^|[;\}\s])\K(' . implode('|', $keywordsBefore) . ')\s+/', '\\2 ', $content);
        $content = preg_replace('/\s+(' . implode('|', $keywordsAfter) . ')(?=([;\{\s]|$))/', ' \\1', $content);

        /*
         * We didn't strip whitespace after a couple of operators because they
         * could be used in different contexts and we can't be sure it's ok to
         * strip the newlines. However, we can safely strip any non-line feed
         * whitespace that follows them.
         */
        $operatorsDiffBefore = array_diff($operators, $operatorsBefore);
        $operatorsDiffAfter = array_diff($operators, $operatorsAfter);
        $content = preg_replace('/(' . implode('|', $operatorsDiffBefore) . ')[^\S\n]+/', '\\1', $content);
        $content = preg_replace('/[^\S\n]+(' . implode('|', $operatorsDiffAfter) . ')/', '\\1', $content);

        /*
         * Whitespace after `return` can be omitted in a few occasions
         * (such as when followed by a string or regex) - but only
         * non-newline whitespace: `return` is a restricted production,
         * so a real line break here already forces ASI (`return;`,
         * with whatever follows becoming an unrelated statement) and
         * must be left alone, or collapsing it would silently change
         * which value gets returned.
         * Same for whitespace in between `)` and `{`, or between `{` and some
         * keywords.
         */
        $content = preg_replace('/\breturn[^\S\n]+(["\'\/\+\-])/', 'return$1', $content);
        $content = preg_replace('/\)\s+\{/', '){', $content);
        $content = preg_replace('/}\n(else|catch|finally)\b/', '}$1', $content);

        /*
         * Get rid of double semicolons, except where they can be used like:
         * "for(v=1,_=b;;)", "for(v=1;;v++)" or "for(;;ja||(ja=true))".
         * I'll safeguard these double semicolons inside for-loops by
         * temporarily replacing them with an invalid condition: they won't have
         * a double semicolon and will be easy to spot to restore afterwards.
         */
        $content = preg_replace('/\bfor\(([^;]*);;([^;]*)\)/', 'for(\\1;-;\\2)', $content);
        $content = preg_replace('/;+/', ';', $content);
        $content = preg_replace('/\bfor\(([^;]*);-;([^;]*)\)/', 'for(\\1;;\\2)', $content);

        /*
         * Next, we'll be removing all semicolons where ASI kicks in.
         * for-loops however, can have an empty body (ending in only a
         * semicolon), like: `for(i=1;i<3;i++);`, of `for(i in list);`
         * Here, nothing happens during the loop; it's just used to keep
         * increasing `i`. With that ; omitted, the next line would be expected
         * to be the for-loop's body... Same goes for while loops.
         * I'm going to double that semicolon (if any) so after the next line,
         * which strips semicolons here & there, we're still left with this one.
         * Note the special recursive construct in the three inner parts of the for:
         * (\{([^\{\}]*(?-2))*[^\{\}]*\})? - it is intended to match inline
         * functions bodies, e.g.: i<arr.map(function(e){return e}).length.
         * Also note that the construct is applied only once and multiplied
         * for each part of the for, otherwise it risks a catastrophic backtracking.
         * The limitation is that it will not allow closures in more than one
         * of the three parts for a specific for() case.
         * REGEX throwing catastrophic backtracking: $content = preg_replace('/(for\([^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*;[^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*;[^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*\));(\}|$)/s', '\\1;;\\8', $content);
         */
        $content = preg_replace('/(for\((?:[^;\{]*|[^;\{]*function[^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*);[^;\{]*;[^;\{]*\));(\}|$)/s', '\\1;;\\4', $content);
        $content = preg_replace('/(for\([^;\{]*;(?:[^;\{]*|[^;\{]*function[^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*);[^;\{]*\));(\}|$)/s', '\\1;;\\4', $content);
        $content = preg_replace('/(for\([^;\{]*;[^;\{]*;(?:[^;\{]*|[^;\{]*function[^;\{]*(\{([^\{\}]*(?-2))*[^\{\}]*\})?[^;\{]*)\));(\}|$)/s', '\\1;;\\4', $content);

        $content = preg_replace('/(for\([^;\{]+\s+in\s+[^;\{]+\));(\}|$)/s', '\\1;;\\2', $content);

        /*
         * Do the same for the if's that don't have a body but are followed by ;}
         */
        $content = preg_replace('/(\bif\s*\([^{;]*\));\}/s', '\\1;;}', $content);

        /*
         * Below will also keep `;` after a `do{}while();` along with `while();`
         * While these could be stripped after do-while, detecting this
         * distinction is cumbersome, so I'll play it safe and make sure `;`
         * after any kind of `while` is kept.
         */
        $content = preg_replace('/(while\([^;\{]+\));(\}|$)/s', '\\1;;\\2', $content);

        /*
         * We also can't strip empty else-statements. Even though they're
         * useless and probably shouldn't be in the code in the first place, we
         * shouldn't be stripping the `;` that follows it as it breaks the code.
         * We can just remove those useless else-statements completely.
         *
         * @see https://github.com/matthiasmullie/minify/issues/91
         */
        $content = preg_replace('/else;/s', '', $content);

        /*
         * We also don't really want to terminate statements followed by closing
         * curly braces (which we've ignored completely up until now) or end-of-
         * script: ASI will kick in here & we're all about minifying.
         * Semicolons at beginning of the file don't make any sense either.
         */
        $content = preg_replace('/;(\}|$)/s', '\\1', $content);
        $content = ltrim($content, ';');

        // get rid of remaining whitespace af beginning/end
        return trim($content);
    }

    /**
     * We'll strip whitespace around certain operators with regular expressions.
     * This will prepare the given array by escaping all characters.
     *
     * @param string[] $operators
     * @param string $delimiter
     *
     * @return string[]
     */
    protected function getOperatorsForRegex(array $operators, $delimiter = '/')
    {
        // escape operators for use in regex
        $delimiters = array_fill(0, count($operators), $delimiter);
        $escaped = array_map('preg_quote', $operators, $delimiters);

        $operators = array_combine($operators, $escaped);

        // ignore + & - for now, they'll get special treatment
        unset($operators['+'], $operators['-']);

        // dot can not just immediately follow a number; it can be confused for
        // decimal point, or calling a method on it, e.g. 42 .toString()
        $operators['.'] = '(?<![0-9]\s)\.';

        // don't confuse = with other assignment shortcuts (e.g. +=)
        $chars = preg_quote('+-*\=<>%&|', $delimiter);
        $operators['='] = '(?<![' . $chars . '])\=';

        return $operators;
    }

    /**
     * We'll strip whitespace around certain keywords with regular expressions.
     * This will prepare the given array by escaping all characters.
     *
     * @param string[] $keywords
     * @param string $delimiter
     *
     * @return string[]
     */
    protected function getKeywordsForRegex(array $keywords, $delimiter = '/')
    {
        // escape keywords for use in regex
        $delimiter = array_fill(0, count($keywords), $delimiter);
        $escaped = array_map('preg_quote', $keywords, $delimiter);

        // add word boundaries
        array_walk($keywords, function ($value) {
            return '\b' . $value . '\b';
        });

        $keywords = array_combine($keywords, $escaped);

        return $keywords;
    }

    /**
     * Replaces all occurrences of array['key'] by array.key.
     *
     * @param string $content
     *
     * @return string
     */
    protected function propertyNotation($content)
    {
        // PHP only supports $this inside anonymous functions since 5.4
        $minifier = $this;
        $keywords = $this->keywordsReserved;
        $callback = function ($match) use ($minifier, $keywords) {
            $property = trim($minifier->extracted[$match[1]], '\'"');

            /*
             * Check if the property is a reserved keyword. In this context (as
             * property of an object literal/array) it shouldn't matter, but IE8
             * freaks out with "Expected identifier".
             */
            if (in_array($property, $keywords)) {
                return $match[0];
            }

            /*
             * See if the property is in a variable-like format (e.g.
             * array['key-here'] can't be replaced by array.key-here since '-'
             * is not a valid character there.
             */
            if (!preg_match('/^' . $minifier::REGEX_VARIABLE . '$/u', $property)) {
                return $match[0];
            }

            return '.' . $property;
        };

        /*
         * Figure out if previous character is a variable name (of the array
         * we want to use property notation on) - this is to make sure
         * standalone ['value'] arrays aren't confused for keys-of-an-array.
         * We can (and only have to) check the last character, because PHP's
         * regex implementation doesn't allow unfixed-length look-behind
         * assertions.
         */
        preg_match('/(\[[^\]]+\])[^\]]*$/', static::REGEX_VARIABLE, $previousChar);
        $previousChar = $previousChar[1];

        /*
         * Make sure word preceding the ['value'] is not a keyword, e.g.
         * return['x']. Because -again- PHP's regex implementation doesn't allow
         * unfixed-length look-behind assertions, I'm just going to do a lot of
         * separate look-behind assertions, one for each keyword.
         */
        $keywords = $this->getKeywordsForRegex($keywords);
        $keywords = '(?<!' . implode(')(?<!', $keywords) . ')';

        return preg_replace_callback('/(?<=' . $previousChar . '|\])' . $keywords . '\[\s*(([\'"])[0-9]+\\2)\s*\]/u', $callback, $content);
    }
}
