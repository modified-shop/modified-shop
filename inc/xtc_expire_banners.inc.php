<?php
/* -----------------------------------------------------------------------------------------
   $Id: xtc_expire_banners.inc.php 899 2005-04-29 02:40:57Z hhgag $   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(banner.php,v 1.10 2003/02/11); www.oscommerce.com 
   (c) 2003	 nextcommerce (xtc_expire_banners.inc.php,v 1.5 2003/08/1); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

require_once(DIR_FS_INC . 'xtc_set_banner_status.inc.php');
   
// Auto expire banners
  function xtc_expire_banners() {
    $banners_query = xtc_db_query("select b.banners_id, b.expires_date, b.expires_impressions, sum(bh.banners_shown) as banners_shown from " . TABLE_BANNERS . " b, " . TABLE_BANNERS_HISTORY . " bh where b.status = '1' and b.banners_id = bh.banners_id group by b.banners_id");
    if (xtc_db_num_rows($banners_query)) {
      while ($banners = xtc_db_fetch_array($banners_query)) {
        if (xtc_not_null($banners['expires_date'])) {
          if (date('Y-m-d H:i:s') >= $banners['expires_date']) {
            xtc_set_banner_status($banners['banners_id'], '0');
          }
        } elseif (xtc_not_null($banners['expires_impressions'])) {
          if ($banners['banners_shown'] >= $banners['expires_impressions']) {
            xtc_set_banner_status($banners['banners_id'], '0');
          }
        }
      }
    }
  }
 ?>
