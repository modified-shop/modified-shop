<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  if (defined('MODULE_COOKIE_CONSENT_STATUS')
      && strtolower(MODULE_COOKIE_CONSENT_STATUS) == 'true'
      && defined('TEXT_COOKIE_CONSENT_LABEL_CPC_HEADING')
      )
  {
    echo '<p><a href="javascript:;" class="button" data-trigger-cookie-consent-panel="">'.TEXT_COOKIE_CONSENT_LABEL_CPC_HEADING.'</a></p>';
  }
