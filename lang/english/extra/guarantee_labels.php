<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2026 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('TEXT_GUARANTEE_LABEL_TITLE', 'EU durability guarantee of the manufacturer');
  define('TEXT_GUARANTEE_LABEL_OPEN', 'Show the full label');
  define('TEXT_GUARANTEE_LABEL_CLOSE', 'Close');
  define('TEXT_GUARANTEE_LABEL_LINK', 'More information about the GARAN label');

  define('TEXT_GUARANTEE_LABEL_RELOAD', 'The full label could not be loaded. Please reload the page.');

  // Target of the QR code inside the official GARAN label, read from the code itself.
  define('TEXT_GUARANTEE_LABEL_URL', 'https://europa.eu/youreurope/commercial-guarantee-durability');

  // Language section of the Your Europe portal the notice points to, as listed in the
  // practical guidelines. A language package brings its own address here.
  define('TEXT_GUARANTEE_NOTICE_URL', 'https://europa.eu/youreurope/guarantees');

  define('TEXT_GUARANTEE_NOTICE_TITLE', 'Legal guarantee');
  define('TEXT_GUARANTEE_NOTICE_TEXT', 'Goods sold in the European Union come with a legal guarantee of conformity of at least two years.');
  define('TEXT_GUARANTEE_NOTICE_MIXED', 'The notice applies to the physical goods of this order.');
  define('TEXT_GUARANTEE_NOTICE_OPEN', 'Show the notice about the legal guarantee');
  define('TEXT_GUARANTEE_NOTICE_LINK', 'Read up on your rights in your country');
  define('TEXT_GUARANTEE_NOTICE_MAIL', 'The goods of this order sold in the European Union come with a legal guarantee of conformity of at least two years.');
  define('TEXT_GUARANTEE_NOTICE_ALT', 'Notice of the European Union about the legal guarantee of at least two years.');

  // one line per order position, the label itself is never redrawn as text
  define('TEXT_GUARANTEE_ORDER_LABEL', 'Durability guarantee: %1$s years, %2$s, model %3$s');
  define('TEXT_GUARANTEE_ORDER_TERMS', 'Guarantee conditions: %s (attached to this e-mail)');

  // Text alternative of the graphic, filled with duration, manufacturer and model
  define('TEXT_GUARANTEE_LABEL_ALT', 'EU label for the commercial guarantee of durability: %1$s years of durability guarantee by the manufacturer %2$s for the model %3$s.');
  define('TEXT_GUARANTEE_LABEL_ALT_COMPACT', 'EU label for the commercial guarantee of durability: %s years.');
